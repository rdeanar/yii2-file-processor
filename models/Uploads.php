<?php

namespace deanar\fileProcessor\models;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

use Imagine\Gd\Imagine;
//use Imagine\Image;
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
    public $filename_separator = '_';
    public $upload_dir = '';    // override in init
    public $default_quality;    // override in init
    public $default_resize_mod; // override in init
    public $unlink_files;       // override in init


    public function init(){
        $this->upload_dir           = Yii::$app->getModule('fp')->upload_dir;
        $this->default_quality      = Yii::$app->getModule('fp')->default_quality;
        $this->default_resize_mod   = Yii::$app->getModule('fp')->default_resize_mod;
        $this->unlink_files         = Yii::$app->getModule('fp')->unlink_files;
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fp_uploads';
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

    public static function getUploads($type, $type_id){
        if (is_null($type_id)) return [];

        return self::find()
            ->where(['type' => $type, 'type_id' => $type_id])
            ->orderBy('ord')
            ->all();
    }

    public static function getUploadsStack($type, $type_id)
    {
        if (is_null($type_id)) return [];

        $uploads = array();

        $array = self::getUploads($type, $type_id);

        foreach ($array as $item) {
            /**
             * @var $item Uploads
             */
            array_push($uploads,
                array(
                    'src' => $item->getPublicFileUrl('_thumb'),
                    'type' => $item->mime,
                    'name' => $item->original,
                    'size' => $item->size,
                    'data' => array(
                        'id' => $item->id,
                        'type' => $item->type,
                        'type_id' => $item->type_id,
                    )
                ));
        }

        return $uploads;
    }

    /**
     * @param $id
     * @return bool
     *
     * Static call function removeFile
     */
    public static function staticRemoveFile($id, $check){
        $file = self::findOne($id);
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
        $config = Uploads::loadVariationsConfig($this->type);
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
                            echo 'Error unlinking file: ' . $file;
                            $error = true;
                        } else {
                            echo 'Unlink file: ' . $file . '' . PHP_EOL;
                        }
                    }
                }
            }
        }

        if(!$error){
            return $this->delete() ? true : false;
        }
        return false;
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


    public static function loadVariationsConfig($type)
    {
        // TODO set default variation instead of '_origial'
        $config = Yii::$app->getModule('fp')->variations_config;

        if (!array_key_exists($type, $config)) {
            $return = isset($config['_default']) ? $config['_default'] : array();
        } else {
            $return = $config[$type];
        }

        $all = isset($config['_all']) ? $config['_all'] : array();

        return ArrayHelper::merge($all,$return);
    }


    public function process($file_temp_name, $config=null){
        if( is_null($config) ) $config = Uploads::loadVariationsConfig($this->type);

        $image = $this->isImage();

        if ( !$image || ( isset($config['_original']) && $config['_original'] === true ) ){
            $upload_dir = $this->getUploadDir($this->type);

            if(!is_dir($upload_dir) ) mkdir($upload_dir, 0777, true); // TODO maybe add yii function for creating dirs

            $upload_full_path = $upload_dir . DIRECTORY_SEPARATOR . $this->filename;

            if (move_uploaded_file($file_temp_name, $upload_full_path)) {
                // cool
            }
        }else{
            $upload_full_path = $file_temp_name;
        }

        if(!$image) return true;


        try {
            $imagine = new Imagine();

            /*
            $imagine = new Imagine\Gd\Imagine();
            $imagine = new Imagine\Imagick\Imagine();
            $imagine = new Imagine\Gmagick\Imagine();
            */

            $image = $imagine->open($upload_full_path);

            foreach ($config as $variation_name => $variation_config) {
                if (substr($variation_name, 0, 1) !== '_' || $variation_name == '_thumb') {
                    $this->makeVariation($image, $variation_name, $variation_config);
                }
            }
        } catch (Imagine\Exception\Exception $e) {
            // handle the exception
        }

    } // end of process


    /**
     * @param array $variationConfig
     * @return array
     */
    public function normalizeVariationConfig($variationConfig){
        $config = array();
        $arrayIndexed = ArrayHelper::isIndexed($variationConfig);
        $argumentCount = count($variationConfig);
        $defaultMode = $this->default_resize_mod;

        if ($arrayIndexed) {
            $config['width'] = $variationConfig[0];
            $config['height'] = $variationConfig[1];
            if ($argumentCount > 2) {
                $config['mode'] = in_array($variationConfig[2], array('inset', 'outbound')) ? $variationConfig[2] : $defaultMode;
            }
            if ($argumentCount > 3) {
                $config['quality'] = is_numeric($variationConfig[3]) ? $variationConfig[3] : $this->default_quality;
            }

        } else {
            $config['width'] = $variationConfig['width'];
            $config['height'] = $variationConfig['height'];
            $config['mode'] = in_array($variationConfig['mode'], array('inset', 'outbound')) ? $variationConfig['mode'] : $defaultMode;
            if( isset($config['quality']) )
                $config['quality'] =  is_numeric($config['quality']) ? $config['quality']  : $this->default_quality;
            // fill color for resize mode fill in (inset variation)
            //$config['watermark'] = $variationConfig['watermark'];
            // watermark position
            // crop
            // rotate
            // etc
        }

        if (!isset($config['mode']))    $config['mode']    = $defaultMode;
        if (!isset($config['quality'])) $config['quality'] = $this->default_quality;

        return $config;
    }

    /**
     * @param $image
     * @param $variationName
     * @param $variationConfig
     * @return bool
     *
     * Resize images by variation config
     */
    public function makeVariation($image, $variationName, $variationConfig){
        if( !is_array($variationConfig)) return false;

        $config = $this->normalizeVariationConfig($variationConfig);

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

        $image->save( $this->getUploadFilePath( $variationName ) , $options );
    }

    /**
     * @param $type
     * @return bool|string
     *
     * Get upload dir
     */
    public function getUploadDir($type){
        return  Yii::getAlias('@app/web/'.$this->upload_dir.'/' . $type);
    }


    /**
     * @param string $variation
     * @return string
     *
     * Get upload path to file
     */
    public function getUploadFilePath($variation='original'){
        return $this->getUploadDir($this->type) . DIRECTORY_SEPARATOR . $this->getFilename($variation);
    }


    /**
     * @param string $variation
     * @param boolean $absolute
     * @return string
     *
     * Get Public file url
     */
    public function getPublicFileUrl($variation='original', $absolute=false){
        return Url::base($absolute) . '/' . $this->upload_dir . '/' . $this->type . '/' . $this->getFilename($variation);
    }

    /**
     * @param string $variation
     * @return string
     *
     *  Get variation filename
    */
    public function getFilename($variation='original'){
        if(empty($this->filename)) return '';
        //TODO make file name template
        if($variation == 'original'){
            return $this->filename;
        }else{
            return $variation . $this->filename_separator . $this->filename;
        }
    }


    public static function extractExtensionName($filename){
        return pathinfo($filename, PATHINFO_EXTENSION);
    }

    /**
     * @param $filename
     * @return string
     *
     * Generate unique filename by uniqid() and original extension
     */
    public static function generateBaseFileName($filename){
        //TODO perhaps check extension and mime type compatibility
        return uniqid().'.'.self::extractExtensionName($filename);
    }


    public function imgTag($variation='original', $absolute=false,$options=array()){
        //TODO return 'empty' value if no image available
        if( empty($this->filename) ) return '';
        $src = $this->getPublicFileUrl($variation,$absolute);
        $attributes = ['src' => $src];
        return Html::tag('img', '', ArrayHelper::merge($options, $attributes));
    }


    /**
     * Converts php.ini style size to bytes
     *
     * @param string $sizeStr $sizeStr
     * @return int
     */
    public static function sizeToBytes($sizeStr)
    {
        // used decimal, not binary
        $kilo = 1000;
        switch (substr($sizeStr, -1)) {
            case 'M':
            case 'm':
                return (int) $sizeStr * $kilo * $kilo;
            case 'K':
            case 'k':
                return (int) $sizeStr * $kilo;
            case 'G':
            case 'g':
                return (int) $sizeStr * $kilo * $kilo * $kilo;
            default:
                return (int) $sizeStr;
        }
    }
}
