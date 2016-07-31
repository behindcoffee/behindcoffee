<?php

namespace Helper\Exceptions;

class NotImplemented extends \Exception {

    public function __construct($message = null, $code = 0) {
        if (!$message) {
            $message = "Not Implemented";
        }
        parent::__construct($message, $code);
    }

}
