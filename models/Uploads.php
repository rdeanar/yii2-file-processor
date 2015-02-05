<?php

namespace deanar\fileProcessor\models;

use Yii;
use yii\base\ErrorException;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use deanar\fileProcessor\helpers\FileHelper;
use deanar\fileProcessor\helpers\VariationHelper;

use Imagine\Image;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;
use Imagine\Exception\Exception;

/**
 * This is the model class for table "fp_uploads".
 *
 * @property integer $id
 * @property string $timestamp
 * @property string $type
 * @property integer $type_id
 * @property string $hash
 * @property integer $ord
 * @property string $filename
 * @property string $original
 * @property string $mime
 * @property integer $size
 * @property integer $width
 * @property integer $height
 */
class Uploads extends \yii\db\ActiveRecord
{
    const IMAGE_DRIVER_GD       = 1;
    const IMAGE_DRIVER_IMAGICK  = 2;
    const IMAGE_DRIVER_GMAGICK  = 3;

    public $filename_separator = '_';
    public $upload_dir = '';    // override in init
    public $unlink_files;       // override in init

    public $image_driver = self::IMAGE_DRIVER_GD;

    public function init(){
        $this->upload_dir           = Yii::$app->getModule('fp')->upload_dir;
        $this->unlink_files         = Yii::$app->getModule('fp')->unlink_files;
        $this->image_driver         = Yii::$app->getModule('fp')->image_driver;
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fp_uploads}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['filename', 'original', 'size'], 'required'],
            [['timestamp'], 'safe'],
            [['type_id', 'ord', 'size', 'width', 'height'], 'integer'],
            [['type', 'hash', 'filename', 'original', 'mime'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'timestamp' => Yii::t('app', 'Время загрузки'),
            'type' => Yii::t('app', 'Тип'),
            'type_id' => Yii::t('app', 'ID Типа'),
            'hash' => Yii::t('app', 'HASH'),
            'ord' => Yii::t('app', 'Порядок отображения'),
            'filename' => Yii::t('app', 'Имя файла'),
            'original' => Yii::t('app', 'Оригинальное имя файла'),
            'mime' => Yii::t('app', 'Тип файла'),
            'size' => Yii::t('app', 'Размер файла'),
            'width' => Yii::t('app', 'Ширина'),
            'height' => Yii::t('app', 'Высота'),
        ];
    }

    /*
     * File upload and process methods
     *
     */

    public function isImage(){
        return !is_null($this->width);
    }

    public static function findByReference($type, $type_id){
        if (is_null($type_id)) return [];

        return self::find()
            ->where(['type' => $type, 'type_id' => $type_id])
            ->orderBy('ord')
            ->all();
    }

    /**
     * @param $id
     * @param $check
     * @return bool
     *
     * Static call function removeFile
     */
    public static function staticRemoveFile($id, $check){
        $file = self::findOne($id);
        if(is_null($file)) return false;
        if(
            $check['type']    == $file->type &&
            $check['type_id'] == $file->type_id
        ){
        return $file->removeFile();
        }
        return false;
    }

    /**
     * @return bool
     *
     * Remove file from file system and database
     */
    public function removeFile(){
        $config = VariationHelper::getConfigOfType($this->type);
        $error = false;

        if ($this->unlink_files) {
            foreach ($config as $variation_name => $variation_config) {

                if ($variation_name == '_original') {
                    if (!$variation_config) continue;
                    $variation_name = 'original';
                }

                if (substr($variation_name, 0, 1) !== '_' || $variation_name == '_thumb') {
                    // delete file
                    $file = $this->getUploadFilePath($variation_name);
                    if (file_exists($file)) {
                        if (!@unlink($file)) {
                            Yii::warning('Can not unlink file: ' . $file, 'file-processor');
                            $error = true;
                        } else {
                            Yii::trace('Unlinked file: ' . $file, 'file-processor');
                        }
                    }
                }
            }
        }

        // clear attribute value in model (if needed)
        Uploads::updateConnectedModelAttribute($config, $this->type_id, null);

        if(!$error){
            return $this->delete() ? true : false;
        }
        return false;
    }

    /**
     * Update attribute value of model (if needed).
     * Used for insertion <id> of uploaded file in case of single file upload
     * @param $config array
     * @param $model_pk integer
     * @param $value integer
     */
    public static function updateConnectedModelAttribute($config, $model_pk, $value){
        if (isset($config['_insert'])) {
            $keys = array_keys($config['_insert']);
            $className = array_shift($keys);
            $attribute = array_shift($config['_insert']);
            if( class_exists($className)) {
                $className::updateAll([$attribute => $value], [$className::primaryKey()[0] => $model_pk]);
            }
        }
    }

    public static function getMaxOrderValue($type, $type_id, $hash)
    {
        if (is_null($type) || is_null($type_id)){
            $where = ['hash' => $hash];
        }else{
            $where = ['type' => $type, 'type_id' => $type_id];
        }

        $find =  self::find()
            ->select('MAX(ord) as ord')
            ->where($where)
            ->one();

        return is_null($find) ? 0 : $find->ord;
    }




    public function process($file_temp_name, $config=null){
        $errors = [];

        if( is_null($config) ) $config = VariationHelper::getConfigOfType($this->type);

        $is_image = $this->isImage();

        if ( !$is_image || ( isset($config['_original']) && $config['_original'] === true ) ){
            $upload_dir = $this->getUploadDir($this->type);

            if(!is_dir($upload_dir) ) mkdir($upload_dir, 0777, true);

            $upload_full_path = $upload_dir . DIRECTORY_SEPARATOR . $this->filename;

            if (!move_uploaded_file($file_temp_name, $upload_full_path)) {
                array_push($errors, 'Can not move uploaded file.');
            }
        }else{
            $upload_full_path = $file_temp_name;
        }

        if(!$is_image) return $errors;


        try {
            switch($this->image_driver){
                case self::IMAGE_DRIVER_IMAGICK:
                    $imagine = new \Imagine\Imagick\Imagine();
                    break;
                case self::IMAGE_DRIVER_GMAGICK:
                    $imagine = new \Imagine\Gmagick\Imagine();
                    break;
                default: //case self::IMAGE_DRIVER_GD:
                    $imagine = new \Imagine\Gd\Imagine();
                    break;
            }

            $image = $imagine->open($upload_full_path);

            foreach ($config as $variation_name => $variation_config) {
                if (substr($variation_name, 0, 1) !== '_' || $variation_name == '_thumb') {
                    $errors = array_merge(
                        $errors,
                        $this->makeVariation($image, $variation_name, $variation_config)
                    );
                }
            }
        } catch (\Imagine\Exception\Exception $e) {
            // handle the exception
            array_push($errors, $e->getMessage());
        }

        return $errors;
    } // end of process



    /**
     * @param $image
     * @param $variationName
     * @param $variationConfig
     * @return array
     *
     * Resize images by variation config
     */
    public function makeVariation($image, $variationName, $variationConfig){
        $errors = [];
        if( !is_array($variationConfig)) return ['Variation config must be an array'];

        $config = VariationHelper::normalizeVariationConfig($variationConfig);

        // here because in normalizeVariationConfig we don't process variation name
        if($variationName == '_thumb'){
            $config['mode'] = 'outbound';
        }

        if($config['mode'] == 'inset'){
            $mode = ImageInterface::THUMBNAIL_INSET;
        }else{
            $mode = ImageInterface::THUMBNAIL_OUTBOUND;
        }

        $image = $image->thumbnail(new Box($config['width'], $config['height']), $mode);

        $options = array(
            'quality' => $config['quality'],
        );

        // PHP Fatal Error 'yii\base\ErrorException' with message 'Allowed memory size of 33554432 bytes exhausted (tried to allocate 2048 bytes)' in /Applications/MAMP/htdocs/loqa.dev/vendor/imagine/imagine/lib/Imagine/Gd/Image.php:606Stack trace:#0 [internal function]: yii\base\ErrorHandler->handleFatalError()#1 {main}

        try {
            if (!$image->save($this->getUploadFilePath($variationName), $options))
                array_push($errors, 'Can not save generated image.');
        }catch (ErrorException $e){
            array_push($errors, 'Allowed memory limit');
        }

        return $errors;
    }

    /**
     * @param $type
     * @return bool|string
     *
     * Get upload dir
     */
    public function getUploadDir($type){
        return  Yii::getAlias('@webroot/'.$this->upload_dir.'/' . $type);
    }


    /**
     * @param string $variation
     * @return string
     *
     * Get upload path to file
     */
    public function getUploadFilePath($variation='original'){
        return $this->getUploadDir($this->type) . DIRECTORY_SEPARATOR . $this->getFilenameByVariation($variation);
    }


    /**
     * @param string $variation
     * @param boolean $absolute
     * @return string
     *
     * Get Public file url
     */
    public function getPublicFileUrl($variation='original', $absolute=false){
        return Url::base($absolute) . '/' . $this->upload_dir . '/' . $this->type . '/' . $this->getFilenameByVariation($variation);
    }

    /**
     * @param string $variation
     * @return string
     *
     *  Get variation filename
    */
    public function getFilenameByVariation($variation='original'){
        if(empty($this->filename)) return '';
        //TODO make file name template
        if($variation == 'original'){
            return $this->filename;
        }else{
            return $variation . $this->filename_separator . $this->filename;
        }
    }


    /**
     * @param $filename
     * @return string
     *
     * Generate unique filename by uniqid() and original extension
     */
    public static function generateBaseFileName($filename){
        //TODO perhaps check extension and mime type compatibility
        return uniqid() . '.' . FileHelper::extractExtensionName($filename);
    }


    /**
     * Returns <img> tag as string by variation name
     *
     * @param string $variation
     * @param bool $absolute
     * @param array $options
     * @return string
     */
    public function imgTag($variation='original', $absolute=false,$options=array()){
        //TODO return 'empty' value if no image available
        if( empty($this->filename) ) return '';
        $src = $this->getPublicFileUrl($variation,$absolute);
        $attributes = ['src' => $src];
        return Html::tag('img', '', ArrayHelper::merge($options, $attributes));
    }


}
