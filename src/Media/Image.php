<?php

namespace hooks\Media;

use hooks\Storage\FileSystem;


class Image extends File
{
    public $path;                  //string
    protected
        $height,                //int
        $width,                 //int
        $preserveAspectRatio = true;  //bool

    public $tempDirectory = "assets/temp-img/";
    public $tempFile = null;

    protected $originalHeight, $originalWidth, $imageType, $resizeRatio;
    protected $thumb;


    public function __construct($path)
    {
        parent::__construct($path);
    }

    public function setPath($path){
        $this->path = parent::__construct($path);
    }

    public function src($width = null, $height = null, $aspectRatio = null){

        if($width != null){
            $this->width($width);
        }

        if($height != null){
            $this->height($height);
        }

        if($aspectRatio != null){
            $this->aspect($aspectRatio);
        }


        $tempFile = $this->prepareAndGetFileName();
        $url = BASE_URL . "image/" . $tempFile;

        if(FileSystem::exists($this->tempDirectory . $tempFile) && FileSystem::isImage($this->tempDirectory . $tempFile)){
            return $url;
        } else {
            return $this->default();
        }
    }

    public function fileName(){
        return basename($this->path);
    }

    public function prepareAndGetFileName(){

        $this->prepare();

        //if($this->tempFile == null){
            $this->tempFile = $this->tempFileName();

            if(!FileSystem::exists( $this->tempDirectory .  $this->tempFile)){
                $this->prepareFromImageType();
                $this->crop();
            }
        //}

        return  $this->tempFile;
    }


    public function default(){
        $default = "assets/images/default.jpg";
        if(FileSystem::exists($default) && FileSystem::isImage($default)){
            return getImageSrc($default, $this->width, $this->height);
        }
        return null;
    }

    public static function deliver($imageFile){

        $imageFile = FileSystem::getRealPath($imageFile);

        if (FileSystem::exists($imageFile)) {

            $imageInfo = getimagesize($imageFile);

            switch ($imageInfo[2]) {
                case IMAGETYPE_JPEG:
                    header("Content-Type: image/jpg",1);
                    break;
                case IMAGETYPE_GIF:
                    header("Content-Type: image/gif");
                    break;
                case IMAGETYPE_PNG:
                    header("Content-Type: image/png",1);
                    break;
                default:
                    break;
            }

            // Set the content-length header
            header('Content-Length: ' . filesize($imageFile));

            // Write the image bytes to the client
            readfile($imageFile);

        }
    }

    private function prepare(){

        if(FileSystem::exists($this->path) && FileSystem::isImage($this->path)){
            $image_properties = getimagesize($this->path);

            $this->originalWidth = $image_properties[0];
            $this->originalHeight = $image_properties[1];
            $this->resizeRatio = $this->originalWidth / $this->originalHeight;
            $this->imageType = $image_properties["mime"];

            if($this->height === null)
                $this->height = $this->originalHeight;
            if($this->width === null)
                $this->width = $this->originalWidth;
        }
    }

    private function prepareFromImageType(){

        if(
            !function_exists("imagecreatefromjpeg") ||
            !function_exists("imagecreatefromgif") ||
            !function_exists("imagecreatefrompng")
        ){
            errorLog("GD Image Library (imagecreatefrom...) is required. Please install.",3);
        }

        switch($this->imageType){
            case "image/jpeg":
                $this->thumb = imagecreatefromjpeg($this->path); //jpeg file
                break;
            case "image/gif":
                $this->thumb = imagecreatefromgif($this->path); //gif file
                break;
            case "image/png":
                $this->thumb = imagecreatefrompng($this->path); //png file
                break;
            default:
                die("Only jpeg, png and gif is supported");
                break;
        }

    }

    private function tempFileName(){
        switch($this->imageType){
            case  "image/jpeg":
                return  md5($this->path) . "." . $this->width . "x" . $this->height . "." . $this->preserveAspectRatio . ".jpg";

            case  "image/png":
                return  md5($this->path) . "." . $this->width . "x" . $this->height. "." . $this->preserveAspectRatio . ".png";

            case  "image/gif":
                return md5($this->path) . "." . $this->width . "x" . $this->height . "." . $this->preserveAspectRatio . ".gif";

            default:
                return null;
        }
    }

    private function crop(){

        if($this->preserveAspectRatio){
            $thumbnail = $this->cropPreservingAspectRation();
        } else {
            $thumbnail = $this->cropWithoutPreservingAspectRation();
        }

        $this->saveTempImage($thumbnail);

    }

    private function saveTempImage($thumbnail){

        if(!FileSystem::exists($this->tempDirectory)){
            FileSystem::makeDirectory($this->tempDirectory);
        }

        $fileName =  $this->tempDirectory . $this->tempFileName();

        switch($this->imageType){

            case  "image/jpeg":
                imagejpeg($thumbnail, $fileName);
                break;

            case  "image/png":
                imagepng($thumbnail, $fileName);
                break;

            case  "image/gif":
                imagegif($thumbnail, $fileName);
                break;

            default:
                die("Invalid Image Type");
                break;

        }
    }

    private function getCropSourcePoints(){

        $originalAR = $this->originalWidth / $this->originalHeight;
        $requestedAR = $this->width / $this->height;


        if($originalAR > $requestedAR){
            $cropHeight = $this->originalHeight;
            $cropWidth = round($cropHeight * $requestedAR);

        } else {
            $cropWidth = $this->originalWidth;
            $cropHeight = round($cropWidth / $requestedAR);
        }

        $sourceX = ($this->originalWidth - $cropWidth) / 2;
        $sourceY = ($this->originalHeight - $cropHeight) / 2;

        return (object) [
            "x" => $sourceX,
            "y"=>  $sourceY,
            "width" => $cropWidth,
            "height" => $cropHeight
        ];
    }

    private function cropPreservingAspectRation(){

        $crop = $this->getCropSourcePoints();


        $thumbnail = imagecreatetruecolor($this->width, $this->height);

        $whiteBackground = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
        imagefill($thumbnail,0,0,$whiteBackground); // fill the background with white


        imagecopyresampled($thumbnail, $this->thumb, 0, 0, $crop->x, $crop->y, $this->width, $this->height, $crop->width, $crop->height);

        return $thumbnail;
    }

    private function cropWithoutPreservingAspectRation(){

        $thumbnail = imagecreatetruecolor($this->width, $this->height);

        $whiteBackground = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
        imagefill($thumbnail,0,0,$whiteBackground); // fill the background with white


        imagecopyresampled($thumbnail, $this->thumb, 0, 0, 0, 0, $this->width, $this->height, $this->originalWidth, $this->originalHeight);

        return $thumbnail;

    }

    public function height($height){
        $this->height = $height;
        return $this;
    }

    public function width($width){
        $this->width = $width;
        return $this;
    }

    public function aspect($preserve){

        $this->preserveAspectRatio = (bool) $preserve;
        return $this;
    }


    public static function parseJPEGSource(string $img){

        $img = str_replace('data:image/jpeg;base64,', '', $img);
        $img = str_replace(' ', '+', $img);
        $img = base64_decode($img);

        return $img;

    }

    public static function parsePNGSource(string $img){

        $img = str_replace('data:image/png;base64,', '', $img);
        $img = str_replace(' ', '+', $img);
        $img = base64_decode($img);

        return $img;

    }

    public function __toString()
    {
        return $this->fileName();
    }


}