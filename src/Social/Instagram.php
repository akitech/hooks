<?php

namespace hooks\Social;


class Instagram {
    private $result = [];
    public $access_token = INSTAGRAM_TOKEN; // default access token, optional
    public $count = 10;
    public $userId = INSTAGRAM_UID;

    private function fetch($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    function prepare(){
        $url = "https://api.instagram.com/v1/users/".$this->userId."/media/recent?".
            "count=" .  $this->count .
            "&access_token=" . $this->access_token;

        $result = json_decode($this->fetch($url),
            true);
        $this->cleanUp($result);
    }

    function user($userId){
        $this->userId = $userId;
        return $this;
    }

    function count($count){
        $this->count = $count;
        return $this;
    }

    function cleanUp($result){
        foreach($result["data"] as $item){
            $post = new \stdClass();
            $post->link = $item["link"];
            $post->likes = $item["likes"]["count"];

            $post->time = new \DateTime();
            $post->time->setTimestamp($item["created_time"]);

            $post->title = $item["caption"]["text"];
            $post->image = $item["images"]["standard_resolution"]["url"];
            //$post->image->thumb = $item["images"]["thumbnail"]["url"];
            //$post->image->highRes = $item["images"]["standard_resolution"]["url"];
            $post->via = "Instagram";
            $this->result[] = $post;
        }
    }
    public function getPosts(){
        $this->prepare();
        return $this->result;
    }

    public function getAuthURL(){
        return "https://instagram.com/oauth/authorize/?client_id="
        .INSTAGRAM_APP_ID ."&redirect_uri=" . route()->completeURL() . "&response_type=token";
    }
}
