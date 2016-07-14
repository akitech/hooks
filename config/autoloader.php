<?php

if(!function_exists("autoloader")){

    function autoloader($class)
    {

        if(strpos($class, "hooks") === 0){
            $class = "vendor\\akitech\\hooks\\src\\" . substr($class, 6);
        }

        $file = BASE_DIR . DIRECTORY_SEPARATOR . $class . ".php";
        $file = str_replace("/" , DIRECTORY_SEPARATOR, $file);
        $file = str_replace("\\" , DIRECTORY_SEPARATOR, $file);


        if(file_exists($file))
        {

             require_once $file;
             return true;

        }
        //return false;

        else
        {
            if(ENV_DEVELOPMENT)
            {
                print_pre(debug_backtrace());
                die("Failed to include class " . $class . " as " . $file);
            }
        }


    }

}


spl_autoload_register('autoloader');

