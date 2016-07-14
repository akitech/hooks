<?php

namespace hooks\Social;
use App\Blog\BlogApp;


class Feed
{

    public static function getAllFeeds(){
        $fb = new FacebookUtils();
        $fbFeed = $fb->getFeed();

        $ig = new Instagram();
        $igFeed = $ig
            ->user(2256663036)
            ->count(10)
            ->getPosts();

        $feed = array_merge($fbFeed,$igFeed);
        shuffle($feed);
        return $feed;
    }


}