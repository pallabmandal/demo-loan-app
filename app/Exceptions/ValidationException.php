<?php

namespace App\Exceptions;

class ValidationException extends BaseException {
    public function __construct($message = 'Unable to validate data. Please check your input(s)') {
        parent::__construct($message, 400);
    }
}