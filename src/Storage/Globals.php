<?php
namespace hooks\Storage;


class Globals implements  Storage
{
    public static function isItemSet($item){
        return isset($GLOBALS[$item]);
    }

    public static function getItem($item){
        return (self::isItemSet($item)) ? $GLOBALS[$item] : null;
    }

    public static function setItem($item, $value){
        $GLOBALS[$item] = $value;
    }

    public static function removeItem($item){
        unset($GLOBALS[$item]);
    }

    public static function flushItem($item){
        $value = (self::isItemSet($item)) ? $GLOBALS[$item] : null;
        unset($GLOBALS[$item]);
        return $value;
    }

}