<?php
/**
 * Created by PhpStorm.
 * User: deanar
 * Date: 30/05/14
 * Time: 01:31
 */

namespace deanar\fileProcessor\behaviours;

use deanar\fileProcessor\helpers\VariationHelper;
use deanar\fileProcessor\helpers\AccessControl;
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
    public $registeredTypes = [];
    public $deleteTypes = []; // for back compatibility
    public $selectFileType = self::SELECT_ALL;

    public function init(){
        if(empty($this->registeredTypes)){ //TODO for back compatibility. Remove in next release.
            $this->registeredTypes = $this->deleteTypes;
        }

        if( is_string($this->registeredTypes) ){
            $this->registeredTypes = empty($this->registeredTypes) ? [] : array_filter(explode(',', str_replace(' ', '', $this->registeredTypes)));
        }
    }

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_UPDATE    => 'updateFileIdInOwnerModel',
            ActiveRecord::EVENT_AFTER_INSERT    => 'updateSequence',
            ActiveRecord::EVENT_BEFORE_DELETE   => 'deleteSequence',
        ];
    }

    public function deleteSequence($event)
    {
        $type_id = $this->owner->getPrimaryKey();
        $types = $this->registeredTypes;

        $files = Uploads::find()->where([
                'type' => $types, // Array of types or single type as string
                'type_id' => $type_id,
            ]
        )->all();

        foreach($files as $file){
            /** @var Uploads $file */
            $file->removeFile();
        }
    }

    public function updateSequence($event){
        if(Yii::$app->request->isConsoleRequest) return;

        $type_id = $this->owner->getPrimaryKey();
        $hashes = Yii::$app->request->post('fp_hash', false);

        if ($hashes != false && is_array($hashes)) {
            foreach($hashes as $hash){
                // fetch one record to determine `type` of upload
                $uploadExample = Uploads::find()->select(['type'])->where(['hash' => $hash])->one();
                if(count($uploadExample) > 0) {
                    $type = $uploadExample->getAttribute('type');
                    $acl = VariationHelper::getAclOfType($type);

                    if(AccessControl::checkAccess($acl, $type_id)) {
                        // all right, attach uploads
                        Uploads::updateAll(['type_id' => $type_id], 'hash=:hash', [':hash' => $hash]);
                    }else{
                        // no access, delete uploaded files
                        Uploads::deleteAll('hash=:hash', [':hash' => $hash]);
                    }
                }
            }
        }

        $this->updateFileIdInOwnerModel($event);
    }

    public function updateFileIdInOwnerModel($event){
        $type_id = $this->owner->getPrimaryKey();

        $configs = VariationHelper::getRawConfig();
        foreach($configs as $type => $config){
            if( ! in_array($type, $this->registeredTypes) ) continue;

            if (isset($config['_insert'])) {
                $attribute = array_shift($config['_insert']);

                $files = Uploads::findByReference($type, $type_id);

                if(!is_null($files)){
                    $file = array_shift($files);

                    //TODO replace with ActiveRecord::updateAttributes() to avoid loops
                    if($this->owner->getAttribute($attribute) !== $file->id) {
                        $this->owner->setAttribute($attribute, $file->id);
                        $this->owner->save(); // one more loop
                    }
                }
            }
        }

    }

    /*
     * Access methods
     */

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

        return $this->owner->hasMany( Uploads::className(), ['type_id' => $this->owner->primaryKey()[0]] )
            ->andOnCondition('type =:type',[':type' => $type])
            ->where($condition)
            ->orderBy('ord')->all();
    }

    public function getFirstFile($type=null){
        return isset($this->getFiles($type)[0]) ? $this->getFiles($type)[0] : new Uploads();
    }
}