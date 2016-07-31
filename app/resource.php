<?php

class Resource extends Response {

    protected function requireLogin()
    {
        $f3 = \Base::instance();
        if ($user = $f3->get("G.user")) {
            return $user;
        } else {
            $f3->reroute("/login?required=true&to=" . urlencode($f3->get("PATH")));
            $f3->unload();

            return false;
        }
    }
}
