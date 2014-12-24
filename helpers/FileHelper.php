<?php
/**
 * Created by PhpStorm.
 * Developer: Mikhail Razumovskiy
 * E-mail: rdeanar@gmail.com
 * User: deanar
 * Date: 23/12/14
 * Time: 13:54
 */

namespace deanar\fileProcessor\helpers;


class FileHelper {


    public static function extractExtensionName($filename){
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
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