<?php


namespace hooks\Auth;


use hooks\Storage\Session;

class Authentication
{

    public static function user($parameter){
        return Session::getItem("xoauth.".$parameter);
    }

}