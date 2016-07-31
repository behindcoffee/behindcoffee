<?php

namespace Helper;

class Passport extends \Prefab {

    public function genSequentialId() {
        $f3 = \Base::instance();
        $cache = $f3->get("cache");

        // Get sequence number
        if ($cache) {
            if (!$cache->get("passport.sequence")) {
                $sequenceNumber = 1;
            } else {
                $sequenceNumber = $cache->get("passport.sequence") + 1;
            }
        } else {
            $sequenceNumber = 1;
        }

        // Cache sequence number
        $cache->set("passport.sequence", $sequenceNumber);

        // Return timestamp + sequenceNumber;
        $security = \Helper\Security::instance();
        $str = sprintf("%d-%d", time(), $sequenceNumber );
        $cr = $security->cr32($str);
        return dechex($cr);
    }

}
