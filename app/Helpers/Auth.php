<?php
namespace App\Helpers;

class Auth {


    public static $auth = null;

    public static function setAuth($auth) {
        self::$auth = json_decode(json_encode($auth), true);
    }

    public static function getAuth() {
        
        if(empty(self::$auth)){
            throw new \App\Exceptions\AuthException("Invalid User", 401);            
        }

        return self::$auth;
    }

    public static function removeAuth() {
        
        self::$auth = null;
    }

}