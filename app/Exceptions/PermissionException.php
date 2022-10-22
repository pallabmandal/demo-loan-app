<?php

namespace App\Exceptions;

class PermissionException extends BaseException {
    public function __construct($message = 'You do not have permission to perform this operation') {
        parent::__construct($message, 403);
    }
}