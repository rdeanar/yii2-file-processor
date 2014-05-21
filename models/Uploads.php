<?php

namespace deanar\fileProcessor\models;

use Yii;

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

    public static function getUploadsStack($type, $type_id){

        $uploads = array();

        $array = self::find()
            ->where(['type' => $type, 'type_id' => $type_id])
            ->orderBy('ord')
            ->all();

        foreach($array as $item){
            /**
             * @var $item Uploads
             */
            array_push($uploads,
                array(
                    'src' => 'http://loqa.dev/basic/web/uploads/' . $item->filename,
                    'type' => $item->mime,
                    'name' => $item->filename,
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

    public static function getMaxOrderValue($tyoe, $type_id){
        //TODO доделать метод
        /*
         *      $criteria = new CDbCriteria;
                $criteria->select='MAX(ord) as ord';

                if($model->type_id != ''){
                    $criteria->addCondition('type_id='.$model->type_id);
                    $criteria->addCondition('type="'.$model->type.'"');
                }else{
                    $criteria->addCondition('hash="'.$model->hash.'"');
                }

                //$criteria->params=array(':searchTxt'=>'%hair spray%');
                $picture_ord_max = Picture::model()->find($criteria);

                $model->ord = $picture_ord_max->ord+1;

         */
        return 0;
    }


}
