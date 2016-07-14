<?php

namespace hooks\Utils;



use hooks\Storage\Request;

class Etc
{
    public static function getRandomId(){
        return dechex(time()) ."-". rand(10000,99999). "-" . rand(10000,99999);
    }

    public static function isAssociativeArray($arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    public static function parseFields(array $fields, $required = true, $implode = true){

        $data = [];

        foreach ($fields as $field){

            if( $required && !Request::isItemSet($field) ){
                errorLog("Invalid request for parse field : ". $field, 1);
            }

            $item = Request::getItem($field);

            if(is_array($item)){
                $item = implode(",", $item);
            }

            $data[$field] = $item;



        }

        return $data;

    }

    public static function sanitize(string $string, string $regex = '/[^a-zA-Z0-9-._]/',string $replace = ''){
        return preg_replace($regex, $replace , $string);
    }

}