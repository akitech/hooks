<?php

namespace hooks\MVC;


use hooks\Storage\FileSystem;

abstract class Controller
{

    /*
    public function put($id = null){

    }

    public function update($id = null){

    }

    public function delete($id = null){

    }

    */

    public function parseClass() {
        $class = get_class($this);

        $re = "/Controllers\\\\([A-Z0-9_]*)Controller/i";

        preg_match($re, $class, $matches);

        if(isset($matches[1])){
            $cls = $matches[1];
            $fullCls = "Models\\Context\\" . $cls;
            if(FileSystem::exists("Models/Context/" . $cls . ".php")
                && class_exists("Models\\Context\\" . $cls)){
                return $fullCls;
            }
        }
        return null;
    }

}