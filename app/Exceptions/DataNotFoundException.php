<?php

namespace App\Exceptions;

class DataNotFoundException extends BaseException {
    public function __construct($message = 'Data Not Found', $code = 400) {
        parent::__construct($message, $code);
    }
}