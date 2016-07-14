<?php
namespace hooks\Storage;


class Post implements  Storage
{
    public static function isItemSet($item){
        return isset($_POST[$item]);
    }

    public static function getItem($item){
        return (self::isItemSet($item)) ? $_POST[$item] : null;
    }

    public static function setItem($item, $value){
        $_POST[$item] = $value;
    }

    public static function removeItem($item){
        unset($_POST[$item]);
    }

    public static function flushItem($item){
        $value = (self::isItemSet($item)) ? $_POST[$item] : null;
        unset($_POST[$item]);
        return $value;
    }

    public static function refreshItem($item){ //Refreshes and returns value
        $value = self::getItem($item);
        self::setItem($item, $value);
        return $value;
    }



}