<?php

namespace hooks\Media;


use hooks\Storage\FileSystem;
use hooks\Utils\Etc;

class ImageUploader
{

    public static function upload(string $path, string $postName = "images", $thumbWidth = 300, $thumbHeight = 300){

        $path = FileSystem::getRealPath($path);

        $response = [];

        if(isset($_FILES[$postName])){

            foreach ($_FILES[$postName]["error"] as $key => $error) {

                if ($error == UPLOAD_ERR_OK) {

                    $tmp_name = $_FILES[$postName]["tmp_name"][$key];

                    $name = Etc::sanitize($_FILES[$postName]["name"][$key]);

                    if(move_uploaded_file( $tmp_name, "$path/$name")){

                        $image = new Image("$path/$name");

                        $response[] = [
                            "thumb" => $image->src($thumbWidth,$thumbHeight),
                            "file" => $name
                        ];

                    }
                }

            }

        }

        return $response;
    }



}