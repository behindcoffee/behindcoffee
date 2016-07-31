<?php

namespace Helper;

class CSRF {

    public static function generateToken() {
        $f3 = \Base::instance();
        $token = md5(time() * mt_rand(1, 100));

        $f3->set("SESSION.csrf_token", $token);
        $f3->set("SESSION.csrf_used", false);

        return $token;
    }

    public static function validateToken($token) {
        $f3 = \Base::instance();

        if ($f3->get("SESSION.csrf_used"))
            return false;

        $f3->set("SESSION.csrf_used", true);

        return $f3->get("SESSION.csrf_token") == $token;
    }

}
