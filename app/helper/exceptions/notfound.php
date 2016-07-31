<?php

namespace Helper\Exceptions;

class NotFound extends \Exception {

    public function __construct($message = null, $code = 0) {
        if (!$message) {
            $message = "Not Found";
        }
        parent::__construct($message, $code);
    }

}
