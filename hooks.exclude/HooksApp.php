<?php

namespace hooks;

use hooks\Storage\FileSystem;
use hooks\Utils\Etc;
use hooks\Utils\GeoLocation;
use Models\App;


class HooksApp
{
    public $name = "hooks App";

    protected static $defaults = [
    ];


    public function __construct()
    {

    }

    public static function log($log, $level = 1){
        $ref = Etc::getRandomId();

        $now = new \DateTime('now');
        $file = "log/" . $now->format("Y-m-d") . ".txt";

        try{
            $logs = FileSystem::get($file);

            $logs .= "Ref# : " . $ref . " || \t";
            $logs .= "Level : " . $level . "\n";
            $logs .= $now->format("Y-m-d h:i:s A") . " || \t";
            $logs .= "IP: " . GeoLocation::getIP(). " || \t";
            $logs .= "Country: " . GeoLocation::getCountry(). "\n";
            $logs .= GeoLocation::userAgent(). "\n";
            $logs .= "Log: " . $log . "\n";
            $logs .= "--------------------------------------------------------------------------------------------------------------------------\n";

            FileSystem::put($file, $logs);

        } catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }

        return $ref;
    }


    public static function defaults($item){

        if(isset(static::$defaults[$item])){
            return static::$defaults[$item];
        }

        self::log("Invalid Default Requested for " . $item );
        throw new \Exception("Invalid Default Requested for " . $item );
    }

}