<?php
/**
 * Created by PhpStorm.
 * User: deanar
 * Date: 30/05/14
 * Time: 01:31
 */


namespace deanar\fileProcessor\behaviours;

use deanar\fileProcessor\models\Uploads;
use yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;

class ConnectFileSequence extends Behavior
{
    const SELECT_ALL = 0;
    const SELECT_IMAGES = 1;
    const SELECT_FILES = 2;

    public $defaultType;
    public $selectFileType = self::SELECT_ALL;

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'updateSequence',
        ];
    }

    public function updateSequence($event){
        $type_id = $this->owner->id;
        $hash = Yii::$app->request->post('fp_hash');
        Uploads::updateAll(['type_id' => $type_id], 'hash=:hash', [':hash' => $hash]);
    }

    public function imagesOnly(){
        $this->selectFileType = self::SELECT_IMAGES;
        return $this;
    }

    public function filesOnly(){
        $this->selectFileType = self::SELECT_FILES;
        return $this;
    }


    public function getFiles($type=null)
    {
        if($type === null) $type = $this->defaultType;

        // TODO if defaultType is not set, use owner className

        switch ($this->selectFileType) {
            case self::SELECT_IMAGES:
                $condition = 'width IS NOT NULL';
                break;
            case self::SELECT_FILES:
                $condition = 'width IS NULL';
                break;
            default:
                $condition = '';
        }

        return $this->owner->hasMany( Uploads::className(), ['type_id' => 'id'] )
            ->andOnCondition('type =:type',[':type' => $type])
            ->where($condition)
            ->orderBy('ord')->all();
    }

    public function getFirstFile($type=null){
        return isset($this->getFiles($type)[0]) ? $this->getFiles($type)[0] : new Uploads();
    }


    //TODO add after_delete method for unlink files (or as alternative make console command for cleanup)

}