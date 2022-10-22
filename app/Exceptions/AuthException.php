<?php

namespace App\Exceptions;

class AuthException extends BaseException {
    public function __construct($message = 'Unable to Authenticate User', $code = 400) {
        parent::__construct($message, $code);
    }
}