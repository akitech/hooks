<?php


namespace hooks\Media;


use hooks\Storage\FileSystem;

class File
{
    public $path;

    public function __construct($path)
    {
        $realPath = FileSystem::getRealPath($path);
        if(FileSystem::exists($realPath) && FileSystem::isFile($realPath)){
            $this->path = $realPath;
        }
    }

    public function basePath(){
        return FileSystem::getBasePath($this->path);
    }

    public function realPath(){
        return FileSystem::getRealPath($this->path);
    }

    public function put($data){
        return FileSystem::put($this->path, $data);
    }

    public function get(){
        return FileSystem::get($this->path);
    }

    public function content(){
        return FileSystem::get($this->path);
    }


}