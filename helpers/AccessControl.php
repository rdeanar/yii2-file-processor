<?php
/**
 * Created by PhpStorm.
 * @author Mikhail Razumovskiy
 * E-mail: rdeanar@gmail.com
 * Date: 14/01/15
 * Time: 20:11
 */

namespace deanar\fileProcessor\helpers;

use Yii;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

class AccessControl {

    public static function checkAccess($acl, $reference = null){
        // 1) All users
        if(is_null($acl) OR $acl == '*') return true;

        // 2) Authenticated users
        if($acl == '@') return !Yii::$app->user->isGuest;

        $user_id  = Yii::$app->user->identity->getId();
        $username = Yii::$app->user->identity->username;

        if(is_array($acl) && ArrayHelper::isAssociative($acl)){
            if(self::checkAccess('@')) {

                // 3) List of users
                if (array_key_exists('users', $acl) && is_array($acl['users'])) {
                    return in_array($username, $acl['users']);
                } else {

                    // 4) Current user id equals to specified attribute of model
                    $keys       = array_keys($acl);
                    $className  = array_shift($keys);
                    $attribute  = array_shift($acl);

                    if (class_exists($className)) {
                        if (is_null($reference)) return false;

                        if($reference instanceof $className){
                            return $reference->getAttribute($attribute) == $user_id;
                        }else {
                            try{
                                $whereCondition = [$className::primaryKey()[0] => $reference, $attribute => $user_id];
                                return $className::find()->where($whereCondition)->count() >  0;
                            }catch (Exception $e){
                                Yii::warning('Invalid configuration: ' . $e->getMessage(), 'file-processor');
                                return false;
                            }
                        }
                    } else {
                        // throw new Exception; // maybe
                        return false;
                    }

                }
            }
        }

        // 5) Defined function
        if(is_callable($acl) ){
            return call_user_func($acl, $reference, $user_id);
        }

        return false;
    }
}