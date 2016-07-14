<?php
namespace hooks\Storage;


class Get implements  Storage
{
    public static function isItemSet($item){
        return isset($_GET[$item]);
    }

    public static function getItem($item){
        return (self::isItemSet($item)) ? $_GET[$item] : null;
    }

    public static function setItem($item, $value){
        $_GET[$item] = $value;
    }

    public static function removeItem($item){
        unset($_GET[$item]);
    }

    public static function flushItem($item){
        $value = (self::isItemSet($item)) ? $_GET[$item] : null;
        unset($_GET[$item]);
        return $value;
    }

    public static function refreshItem($item){ //Refreshes and returns value
        $value = self::getItem($item);
        self::setItem($item, $value);
        return $value;
    }



}