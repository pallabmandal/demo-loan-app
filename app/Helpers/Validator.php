<?php
namespace App\Helpers\Application;

class Validator {

    public static function isValidEmail($email) {
        $pattern = '/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix';

        if (preg_match($pattern, $email) === 1) {
            return true;
        }

        return false;

    }

}