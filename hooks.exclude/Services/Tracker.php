<?php


namespace hooks\Services;

class Tracker
{

    public function track()
    {
        $time = microtime(true) - SERVER_INIT;
        $url = $this->getCompleteURL();
        $ip = $this->getClientIP();
        $ua = $_SERVER['HTTP_USER_AGENT'];
        $data = compact("url","ip","ua","time");

        try{
            if($time < MAX_CPU_TIME){
                db()->insertTo(TRACKER_TABLE,$data);
            } else {
                $this->blacklist();
            }
        } catch (\Exception $e){

        }
    }

    public function getClientIP(){
        if ( isset ($_SERVER['HTTP_CLIENT_IP'] ) )
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if ( isset ($_SERVER['HTTP_X_FORWARDED_FOR'] ) )
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if ( isset ($_SERVER['HTTP_X_FORWARDED'] ) )
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if ( isset ($_SERVER['HTTP_FORWARDED_FOR'] ) )
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if ( isset ($_SERVER['HTTP_FORWARDED'] ) )
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if ( isset ($_SERVER['REMOTE_ADDR'] ) )
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';

        return $ipaddress;

    }

    public function getCompleteURL(){
        $base_url = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https' : 'http' ) . '://' .  $_SERVER['HTTP_HOST'];
        return $base_url . $_SERVER["REQUEST_URI"];
    }

    public function blacklist($reason = "Request Time Overload"){

        $url = $this->getCompleteURL();
        $ip = $this->getClientIP();
        $ua = $_SERVER['HTTP_USER_AGENT'];
        $time = microtime(true)- SERVER_INIT;
        $data = compact("url","ip","ua","time", "reason");

        try{
            db()->insertTo(BLACKLIST_TABLE,$data);
        } catch (\Exception $e){

        }
    }

    public function isBlacklisted(){
        return db()->exists(BLACKLIST_TABLE,["ip" => $this->getClientIP(), "cleared" => 0]);
    }

    public function kill(){
        die(
            "<h1>Ooops!</h1>
            We are sorry but you are blacklisted in our server.
            Please send a written application to our server admin (".SERVER_ADMIN.")
            explaining why it happened.
            We will investigate and look if you can be re-enlisted."
        );
    }

}