<?php
/**
 * Created by PhpStorm.
 * Developer: Mikhail Razumovskiy
 * E-mail: rdeanar@gmail.com
 * User: deanar
 * Date: 24/12/14
 * Time: 15:25
 */

namespace deanar\fileProcessor\helpers;

use Yii;
use yii\helpers\ArrayHelper;

class VariationHelper {

    public static function default_resize_mod(){
        return Yii::$app->getModule('fp')->default_resize_mod;
    }

    public static function default_quality(){
        return Yii::$app->getModule('fp')->default_quality;
    }

    public static function getRawConfig(){
        return Yii::$app->getModule('fp')->variations_config;
    }

    public static function getConfigOfType($type)
    {
        // TODO set default variation instead of '_original'
        $config = self::getRawConfig();

        if (!array_key_exists($type, $config)) {
            $return = isset($config['_default']) ? $config['_default'] : array();
        } else {
            $return = $config[$type];
        }

        $all = isset($config['_all']) ? $config['_all'] : array();

        return ArrayHelper::merge($all,$return);
    }

    /**
     * Get Access Control settings for given type from variation config
     * @param $type
     * @return null
     */
    public static function getAclOfType($type){
        $config = self::getRawConfig();

        if (!array_key_exists($type, $config)) {
            return null;
        } else {
            $config_of_type = $config[$type];
        }

        if(array_key_exists('_acl',$config_of_type)){
            return $config_of_type['_acl'];
        }
    }

    /**
     * @param array $variationConfig
     * @return array
     */
    public static function normalizeVariationConfig($variationConfig){
        $config = array();
        $arrayIndexed = ArrayHelper::isIndexed($variationConfig);
        $argumentCount = count($variationConfig);
        $defaultResizeMode = self::default_resize_mod();

        if ($arrayIndexed) {
            $config['width'] = $variationConfig[0];
            $config['height'] = $variationConfig[1];
            if ($argumentCount > 2) {
                $config['mode'] = in_array($variationConfig[2], array('inset', 'outbound')) ? $variationConfig[2] : $defaultResizeMode;
            }
            if ($argumentCount > 3) {
                $config['quality'] = is_numeric($variationConfig[3]) ? $variationConfig[3] : self::default_quality();
            }

        } else {
            $config['width'] = $variationConfig['width'];
            $config['height'] = $variationConfig['height'];
            $config['mode'] = in_array($variationConfig['mode'], array('inset', 'outbound')) ? $variationConfig['mode'] : $defaultResizeMode;
            if( isset($config['quality']) )
                $config['quality'] =  is_numeric($config['quality']) ? $config['quality']  : self::default_quality();

            // fill color for resize mode fill in (inset variation)
            //$config['watermark'] = $variationConfig['watermark'];
            // watermark position
            // crop
            // rotate
            // etc
        }

        if (!isset($config['mode']))    $config['mode']    = $defaultResizeMode;
        if (!isset($config['quality'])) $config['quality'] = self::default_quality();

        return $config;
    }


}