<?php

namespace hooks\Media;


use hooks\Storage\FileSystem;

class Directory
{
    public $path;

    public function __construct($path)
    {
        $realPath = FileSystem::getRealPath($path);

        if(!FileSystem::exists($realPath)){
            FileSystem::makeDirectory($realPath);
        }

        if(FileSystem::exists($realPath) && FileSystem::isDirectory($realPath)){
            $this->path = $realPath;
        } else {
            die("Invalid Directory " . $realPath);
        }
    }

    public function basePath(){
        return FileSystem::getBasePath($this->path);
    }

    public function realPath(){
        return FileSystem::getRealPath($this->path);
    }

    public function getFiles(){
        $return = [];
        $exclude = array('..', '.', '.DS_Store','.git','.gitignore','.idea');
        $array = array_diff(scandir($this->path), $exclude);
        foreach ($array as $item){
            $path = $this->path . DIRECTORY_SEPARATOR . $item;
            if(FileSystem::isDirectory($path)){
                $return[] = new Directory($path);
            } else if (FileSystem::isImage($path)){
                $return[] = new Image($path);
            } else if (FileSystem::isFile($path)){
                $return[] = new File($path);
            }
        }
        return $return;
    }

}