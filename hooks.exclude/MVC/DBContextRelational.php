<?php

namespace hooks\MVC;


use hooks\Storage\FileSystem;

abstract class DBContextRelational
{

    public static $context, $contextPrimaryKey;

    public function __construct()
    {
        //Not self::$context because Contexts have $context self has null
        $path = "Models/.model-cache/" . self::getContext()  . ".rln";

        if(FileSystem::exists($path)){
            $data = FileSystem::get($path);
            $relations = (array) @json_decode($data);

            $this->linkRelations($relations);
        }
    }

    private function linkRelations($relations){

        foreach ($relations as $relation => $destination){

            $params = [$this->$relation];

            $refClass = new \ReflectionClass($destination);
            $this->$relation = $refClass->newInstanceArgs((array) $params);

        }
    }

    public static function getContext(){
        return static::$context;
    }

    public static function getContextKey(){
        return static::$contextPrimaryKey;
    }

}