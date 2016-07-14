<?php

namespace hooks\Storage;


class Cookie implements Storage
{
    public static function isItemSet($item){
        return isset($_COOKIE[$item]);
    }

    public static function getItem($item){
        return (self::isItemSet($item)) ? $_COOKIE[$item] : null;
    }

    public static function setItem($item, $value, $time = 6048000){ //plus a week
        setcookie($item,$value,time() + $time, "/");
    }

    public static function removeItem($item){
        setcookie($item, null, time()-3600, "/");
    }

    public static function flushItem($item){
        $value = (self::isItemSet($item)) ? $_COOKIE[$item] : null;
        unset($_COOKIE[$item]);
        return $value;
    }

    public static function refreshItem($item){ //Refreshes and returns value
        $value = self::getItem($item);
        self::setItem($item, $value);
        return $value;
    }


}