<?php

namespace Helper;

class Form {

    public function validate($data) {
        $f3 = \Base::instance();
        $errors = array();

        // $f3->set("SESSION.csrf_token", "Enable for invalid token");

        $valid_token = array_key_exists("csrf_token", $data) &&
            \Helper\CSRF::validateToken($data["csrf_token"]);

        if (!$valid_token) {
            $errors["csrf_token"] = "This form has already changed.";
        }

        $f3->set("errors", $errors);
        return empty($errors);
    }

}
