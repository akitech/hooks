<?php
namespace hooks\Social;


use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException as FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException as FacebookSDKException;
use hooks\Storage\Cookie;

class FacebookUtils
{

    private
            $fbInstance,
            $fbAccessToken,
            $data = [],
            $currentMedia; //feed, photos, videos

    public function __construct(){
        $this->fbInstance = FacebookAuth::getFBInstance();
        $this->checkFacebookAuth();
    }

    private function checkFacebookAuth(){
        if(!FacebookAuth::isAuthorized()){
            FacebookAuth::authorize();
        } else {
            $this->fbAccessToken = Cookie::getItem(FacebookAuth::$tokenName);
        }
    }

    public function status($status){
        $this->data['message'] = $status;
        $this->currentMedia = "feed";
        return $this;
    }

    public function link($link){
        $this->data['link'] = $link;
        $this->currentMedia = "feed";
        return $this;
    }

    public function photo($path){
        $this->data['source'] = $this->fbInstance->fileToUpload($path);
        $this->currentMedia = "photos";
        return $this;
    }

    public function video($path){
        $this->data['source'] = $this->fbInstance->videoToUpload($path);
        $this->currentMedia = "videos";
        return $this;
    }

    public function videoTitle($title){
        $this->data['title'] = $title;
        $this->currentMedia = "videos";
        return $this;
    }

    public function videoDesc($description){
        $this->data['description'] = $description;
        $this->currentMedia = "videos";
        return $this;
    }

    public function post(){
        try {
            // Returns a `Facebook\FacebookResponse` object
            $response = $this->fbInstance->post('/me/' . $this->currentMedia,
                $this->data, $this->fbAccessToken);

            $graphNode = $response->getGraphNode();
            return isset($graphNode['id']);
        } catch(FacebookResponseException $e) {
            die('Graph returned an error: ' . $e->getMessage());
        } catch(FacebookSDKException $e) {
            die('Facebook SDK returned an error: ' . $e->getMessage());
        }

    }

    public function getFeed(){
        // Allocate a new cURL handle
        $ch = curl_init("https://graph.facebook.com/me/feed?access_token=" . Cookie::getItem("fb_token"));
        if (! $ch) {
            die( "Cannot allocate a new PHP-CURL handle" );
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);

        $data = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($data);
        $posts = [];

        foreach ($data->data as $feed){
            $post = new \stdClass();
            $feed = (array) $feed;

            if(isset($feed["message"])){
                $post->title = $feed["message"];
            }

            if(isset($feed["story"])){
                $post->title =  $feed["story"];
            }
            $post->time = new \DateTime($feed["created_time"]);
            $post->via = "Facebook";
            $posts[] = $post;
        }

        return $posts;

    }


}