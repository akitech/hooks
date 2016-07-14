<?php

namespace hooks\MVC;


class Redirect
{
    public static function to(string $location = null){
        if(strpos($location,'http') === false){
            header('Location:'.BASE_URL.$location);
        } else {
            header('Location:'.$location);
        }
        exit();
    }

    public static function trigger(int $num){

        if(substr(route()->projectURI(), 0 , 5) == "admin")  {
            $errorDirectory = "admin/errors";
        } else {
            $errorDirectory = "errors";
        }



        switch($num){
            case 404:
                view()->printView( $errorDirectory . "/404");
                break;

            case 500:
                view()->printView( $errorDirectory . "/500");
                break;

            default:
                view()->printView( $errorDirectory . "/500");
                break;
        }

        die();

    }
}