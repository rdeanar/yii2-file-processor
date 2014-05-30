<?php
/**
 * Created by PhpStorm.
 * User: deanar
 * Date: 30/05/14
 * Time: 01:31
 */


namespace deanar\fileProcessor\behaviours;

use yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;

class ConnectFileSequence extends Behavior
{
    public $in_attribute = 'name';
    public $out_attribute = 'slug';
    public $translit = true;

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'updateSequence',
            ActiveRecord::EVENT_AFTER_UPDATE => 'updateSequence',
        ];
    }


    public function updateSequence(){
        echo var_dump($_POST);
    }
}