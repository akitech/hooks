<?php

namespace hooks\Utils;


class Curl
{

    private $url, $headers = false;

    public function __construct(string $url){
        $this->url = $url;
    }

    public function headers(bool  $bool){
        $this->headers = $bool;
    }

    public function execute(){

        if(!function_exists("curl_init")){
            errorLog("CURL is required. Please install.",3);
        }

        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL,$this->url);

        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_HEADER, $this->headers);

        $output = curl_exec($ch);

        curl_close($ch);
        return $output;

    }

}