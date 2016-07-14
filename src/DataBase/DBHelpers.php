<?php

namespace hooks\DataBase;


use hooks\Errors\Error;
use hooks\Storage\FileSystem;

class DBHelpers
{

    public static function getColumns($table){
        $file = "Models/.model-cache/" . $table . ".cols";

        if(FileSystem::exists($file)){
            $data = FileSystem::get($file);
            try{
                return json_decode($data);
            } catch (\Exception $e){
                new Error($e->getMessage(), 0);
            }
        }

        $sql = "SHOW COLUMNS FROM " . $table;
        $query = new SQLQuery($sql);
        $results = $query->getResults();

        try{
            FileSystem::put($file, json_encode($results));
        } catch (\Exception $e){
            new Error($e->getMessage(), 0);
        }
        return $results;
    }

    public static function getTables($db){
        $sql = "SHOW TABLES FROM ". $db;
        $query = new SQLQuery($sql);
        return $query->getResults();

    }

}