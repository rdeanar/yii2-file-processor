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
    public $defaultType;

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


    public function getFiles($type=null)
    {
        if($type === null) $type = $this->defaultType;

        // TODO if defaultType is not set, use owner className

        return $this->owner->hasMany( Uploads::className(), ['type_id' => 'id'] )
            ->where('type = :type', [':type' => $type])
            ->orderBy('ord')->all();
    }

    public function getFirstFile($type=null){
        return isset($this->getFiles($type)[0]) ? $this->getFiles($type)[0] : new Uploads();
    }


    //TODO add after_delete method for unlink files (or as alternative make console command for cleanup)

}