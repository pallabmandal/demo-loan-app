<?php

namespace App\Exceptions;

class DBOException extends BaseException {
    public function __construct($message = 'Data Not Found', $code = 400) {
        parent::__construct($message, $code);
    }
}