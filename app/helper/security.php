<?php

namespace Helper;

class Security extends \Prefab {

    public function alpha_id($in, $reverse = true)
    {
        $output = "";
        $index = "bcdfghjklmnpqrstvwxyz0123456789";
        $base = strlen($index);

        for ($t = ($in != 0 ? floor(log($in, $base)) : 0); $t >= 0; $t--) {
            $bcp = bcpow($base, $t);
            $a = floor($in / $bcp) % $base;
            $output = $output . substr($index, $a, 1);
            $in = $in - ($a * $bcp);
        }

        return $output;
    }

    public function hash($string, $salt = null) {
        if ($salt === null) {
            $salt = $this->salt();
            return array(
                "salt" => $salt,
                "hash" => sha1($salt . sha1($string))
            );
        } else {
            return sha1($salt . sha1($string));
        }
    }

    public function gen_sequential_id() {
        return time();
    }

    public function cr32($string) {
        return crc32($string);
    }

    public function salt() {
        return md5($this->rand_bytes(64));
    }

    public function md5($string) {
        return md5($string);
    }

    public function salt_sha1() {
        return sha1($this->rand_bytes(64));
    }

    public function salt_sha2($size = 256) {
        $allSizes = array(256, 384, 512);
        if (!in_array($size, $allSizes)) {
            throw new Exception("Hash size must be one of: " . implode(", ", $allSizes));
        }
        return hash("sha$size", $this->rand_bytes(512), false);
    }

    private function rand_bytes($length = 36) {

        // Use OpenSSL cryptography extension if available
        if (function_exists("openssl_random_pseudo_bytes")) {
            $strong = false;
            $rnd = openssl_random_pseudo_bytes($length, $strong);
            if ($strong === true) {
                return $rnd;
            }
        }

        // Use SHA256 of mt_rand if OpenSSL is not available
        $rnd = "";
        for ($i = 0; $i < $length; $i++) {
            $sha = hash("sha256", mt_rand());
            $char = mt_rand(0, 30);
            $rnd .= chr(hexdec($sha[$char] . $sha[$char + 1]));
        }

        return (binary)$rnd;
    }

}
