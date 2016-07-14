<?php


namespace hooks\MVC;


use hooks\Storage\FileSystem;
use MatthiasMullie\Minify as Minify;

class MinifyHelper
{

    public static function compress($string){

        $scripts = self::getScript($string);
        $styles = self::getStyle($string);

        foreach ($scripts as $script){
            $minScript = self::minifyScript($script);
            $string = str_replace($script,$minScript,$string);
        }

        foreach ($styles as $style){
            $minStyle = self::minifyCSS($style);
            $string = str_replace($style,$minStyle,$string);
        }

        return $string;

    }

    public static function getScript($string) {

        preg_match_all("#<script.*?>([^<]+)</script>#", $string, $matches);

        if(count($matches) == 2){
            return $matches[1];
        }  else {
            return [];
        }

    }

    public static function getStyle($string) {
        preg_match_all("#<style.*?>([^<]+)</style>#", $string, $matches);
        if(count($matches) == 2){
            return $matches[1];
        }  else {
            return [];
        }
    }

    public static function minifyCSS($css){

        $minifier = new Minify\CSS();
        $minifier->add($css);
        return $minifier->minify();

    }


    public static function minifyScript($script){

        $minifier = new Minify\JS();
        $minifier->add($script);
        return $minifier->minify();

    }



}