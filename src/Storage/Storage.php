<?php

namespace hooks\Storage;


interface Storage
{

    public static function isItemSet($item);

    public static function getItem($item);

    public static function setItem($item, $value);

    public static function removeItem($item);

    public static function flushItem($item);

}