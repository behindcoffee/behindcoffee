<?php

namespace Helper\Exceptions;

class ParameterError extends \Exception {

    public function __construct($message = null, $code = 0) {
        if (!$message) {
            $message = "Parameter Error";
        }
        parent::__construct($message, $code);
    }

}
