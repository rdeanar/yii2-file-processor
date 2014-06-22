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
    public $deleteTypes = [];
    public $selectFileType = self::SELECT_ALL;

    public function init(){
        if( is_string($this->deleteTypes) ){
            $this->deleteTypes = empty($this->deleteTypes) ? [] : array_filter(explode(',', str_replace(' ', '', $this->deleteTypes)));
        }
    }

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'updateSequence',
            ActiveRecord::EVENT_BEFORE_DELETE => 'deleteSequence',
        ];
    }

    public function deleteSequence($event)
    {
        $type_id = $this->owner->id;
        $types = $this->deleteTypes;

        $files = Uploads::find()->where([
                'type' => $types,
                'type_id' => $type_id,
            ]
        )->all();

        foreach($files as $file){
            /** @var Uploads $file */
            $file->removeFile();
        }
    }

    public function updateSequence($event){
        $type_id = $this->owner->id;
        $hashes = Yii::$app->request->post('fp_hash');

        foreach($hashes as $hash){
            Uploads::updateAll(['type_id' => $type_id], 'hash=:hash', [':hash' => $hash]);
        }
    }

    public function imagesOnly(){
        $this->selectFileType = self::SELECT_IMAGES;
        return $this;
    }

    public function filesOnly(){
        $this->selectFileType = self::SELECT_FILES;
        return $this;
    }


    public function getFiles($type=null,$selectFileType=null)
    {
        if($type === null) $type = $this->defaultType;
        // TODO if defaultType is not set, use owner className

        if(!is_null($selectFileType)) $this->selectFileType = $selectFileType;

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
}