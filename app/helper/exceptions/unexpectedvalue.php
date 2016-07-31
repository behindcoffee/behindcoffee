<?php

namespace Helper\Exceptions;

class UnexpectedValue extends \Exception {

    public function __construct($message = null, $code = 0) {
        if (!$message) {
            $message = "Unexpected Value";
        }
        parent::__construct($message, $code);
    }

}
