<?php
namespace hooks\Storage;


class Request implements  Storage
{
    public static function isItemSet($item){
        return isset($_REQUEST[$item]);
    }

    public static function getItem($item){
        return (self::isItemSet($item)) ? $_REQUEST[$item] : null;
    }

    public static function setItem($item, $value){
        $_REQUEST[$item] = $value;
    }

    public static function removeItem($item){
        unset($_REQUEST[$item]);
    }

    public static function flushItem($item){
        $value = (self::isItemSet($item)) ? $_REQUEST[$item] : null;
        unset($_REQUEST[$item]);
        return $value;
    }

    public static function refreshItem($item){ //Refreshes and returns value
        $value = self::getItem($item);
        self::setItem($item, $value);
        return $value;
    }



}