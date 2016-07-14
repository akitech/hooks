<?php

namespace hooks\Storage;


class Environment implements Storage
{
    public static function isItemSet($item){
        return isset($_ENV[$item]);
    }

    public static function getItem($item){
        return (self::isItemSet($item)) ? $_ENV[$item] : null;
    }

    public static function setItem($item, $value){
        $_ENV[$item] = $value;
    }

    public static function removeItem($item){
        unset($_ENV[$item]);
    }

    public static function flushItem($item){
        $value = (self::isItemSet($item)) ? $_ENV[$item] : null;
        unset($_ENV[$item]);
        return $value;
    }


}