<?php

namespace hooks\Social;


use hooks\Storage\Cookie;
use hooks\MVC\Redirect;
use Facebook\Facebook as FacebookSDK;
use Facebook\Exceptions\FacebookResponseException as FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException  as FacebookSDKException;

class FacebookAuth
{

    public static $tokenName = "fb_token";
    private static $fbInstance;

    public static function getFBInstance(){
        self::init();
        return self::$fbInstance;
    }

    private static function init(){
        if(self::$fbInstance === null){
            self::$fbInstance = new FacebookSDK([
                'app_id' => FACEBOOK_APP_ID,
                'app_secret' => FACEBOOK_APP_SECRET,
                'default_graph_version' => 'v2.2',
            ]);
        }
    }

    public static function authorize(){
        self::init();
        if(Cookie::isItemSet(self::$tokenName)){
            self::redirectToRedirectURL();
        } else {
            self::redirectToAuthorize();
        }

    }

    private static function redirectToAuthorize(){
        Redirect::to(self::getAuthURL());
    }

    public static function redirectToRedirectURL(){
        if(Cookie::isItemSet("redirectURL")){
            Redirect::to(Cookie::flushItem("redirectURL"));
        } else {
            Redirect::to(BASE_URL);
        }
    }

    private static function getAuthURL(){
        $redirect = BASE_URL."authorize/facebook/response/";
        $helper = self::$fbInstance->getRedirectLoginHelper();
        $permissions = ['email', 'user_likes', 'public_profile','user_posts','publish_actions'];
        return $loginUrl = $helper->getLoginUrl($redirect, $permissions);
    }

    public static function isAuthorized(){
        return Cookie::isItemSet(self::$tokenName);
    }

    //Part 2: When Authorise Response comes back from facebook
    public static function collectResponse(){
        self::init();
        $fb = self::$fbInstance;
        $helper = $fb->getRedirectLoginHelper();

        try {
            $accessToken = $helper->getAccessToken();
        } catch(FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        if (isset($accessToken)) {
            $oAuth2Client = $fb->getOAuth2Client();
            $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
            Cookie::setItem("fb_token",$longLivedAccessToken);
            self::redirectToRedirectURL();
        }

    }

}
