<?php

namespace Model;

use \Helper\Security;
use \Helper\Exceptions\UnexpectedValue;

class User extends Record {

    // Constants
    const COOKIE_NAME           = "behindcoffee_token";
    const MIN_NAME_LENGTH       = 3;
    const MAX_NAME_LENGTH       = 100;
    const MAX_ABOUT_LENGTH      = 500;
    const MIN_PASSWORD_LENGTH   = 8;
    const MAX_PASSWORD_LENGTH   = 200;

    protected $table_name = 'user';

    // private $_required = array(
    //     "full_name", "username", "email", "password", "salt"
    // );

    // Most common passwords
    // List of most common passwords. These cannot be used
    // to register an account.
    private $_common_passwords = array(
        '1234567890',
        '123123123',
        '12345678',
        '11111111',
        'password',
        'abc12345',
        'iloveyou',
        'adobe123',
        'admin123',
        'monkey123',
        'password1',
        'princess',
        'sunshine',
        'adminadmin'
    );

    public function confirm_password($user, $password)
    {
        $security = \Helper\Security::instance();
        $hash = $security->hash($password, $user["salt"] ?: "");
        if ($user["password"] == $hash) {
            return true;
        }

        return false;
    }

    public function update_profile($user, $password, $data)
    {
        $status = false;
        $errors = array();

        // Remove empty elements
        $data = array_filter($data);

        // Holds values to update
        $update = array();

        // Verify current password.
        if (!self::confirm_password($user, $data["current_password"])) {
            $errors[] = "Your current password does not match our records. Please verify and try again.";
        } else {

            // Update Full Name
            if (array_key_exists("full_name", $data)) {

                $name = trim($data["full_name"]);

                if (strlen($name) < self::MIN_NAME_LENGTH ||
                    strlen($name) > self::MAX_NAME_LENGTH) {
                    $errors[] = sprintf("Name must be between %d and %d characters long.", self::MIN_NAME_LENGTH, self::MAX_NAME_LENGTH);
                } else {
                    $update["full_name"] = $name;
                }

            }

            // Update user about section
            if (array_key_exists("about", $data)) {

                $about = trim($data["about"]);

                if (strlen($about) > self::MAX_ABOUT_LENGTH) {
                    $errors[] = sprintf("About must be less than %d characters.", self::MAX_ABOUT_LENGTH);
                } else {
                    $update["about"] = $about;
                }

            }

            // Update email address
            if (array_key_exists("email", $data)) {

                $email = trim($data["email"]);

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Email address does not seem to be valid.";
                } else {
                    $update["email"] = $email;
                }

            }


            if (array_key_exists("new_password", $data)) {

                $new_password = $data["new_password"];
                $password_confirm = $data["new_password_confirm"];

                if (strlen($new_password) < self::MIN_PASSWORD_LENGTH ||
                    strlen($new_password) > self::MAX_PASSWORD_LENGTH) {
                    $errors[] = sprintf("New password must be between %d and %d characters long.", self::MIN_PASSWORD_LENGTH, self::MAX_PASSWORD_LENGTH);
                } else {
                    if ($password_confirm != $new_password) {
                        $errors[] = "New password confirmation failed.";
                    } elseif ($new_password == $user["username"]) {
                        $errors[] = "New password cannot be the same as username.";
                    } elseif (in_array(strtolower($post["password"]), $this->_common_passwords)) {
                        $errors[] = "New password is too common.";
                    } else {
                        $security = \Helper\Security::instance();
                		extract($security->hash($new_password));
                        $update["password"] = $hash;
                        $update["salt"] = $salt;
                    }
                }

            }

        }

        if (!empty($update)) {
            $this->update_user(array_merge($user, $update));
            $status = true;
        }

        return array(
            'status' => $status,
            'errors' => $errors
        );
    }

    public function change_password($user, $password)
    {
        $security = \Helper\Security::instance();
		extract($security->hash($password));
        $user["password"] = $hash;
        $user["salt"] = $salt;
        self::update_user($user);
    }

    public function open_session($user)
    {
        if (empty($user["session_token"])) {
            $token = Security::instance()->salt_sha2();
            $user["session_token"] = $token;
            self::update_user($user);
        }

        $this->set_session_token($user["session_token"]);

        return $user["session_token"];
    }

    public function load_session() {
        $f3 = \Base::instance();
        return $f3->get("COOKIE.".self::COOKIE_NAME);
    }

    public function start_session() {
        $token = $this->load_session();
        if ($token) {
            $user_by_session_token = self::get_by("session_token", $token);
            if ($user_by_session_token) {
                $user_id = key($user_by_session_token);
                $user = $user_by_session_token[$user_id];

                $f3 = \Base::instance();
                $f3->set("G.user", $user);
            } else {
                self::end_session();
            }
        }
    }

    private function set_session_token($token) {
        $f3 = \Base::instance();
        $f3->set("COOKIE.".self::COOKIE_NAME, $token);
    }

    public function end_session() {
        $f3 = \Base::instance();
        $token = $f3->get("COOKIE.".self::COOKIE_NAME);
        if ($token) {
            $f3->set("COOKIE.".self::COOKIE_NAME, "");
            $user = $f3->get("G.user");
            $user["session_token"] = null;
            self::update_user($user);
        }
    }

    public function update_user($user) {
        return self::update($user);
    }

    // Create a new user. This assumes that $user is a named
    // value array and all values were already verified.
    // Requires values are "username", "password", "full_name" and "email".
    public function create_user($user)
    {
        // Create a hash and salt from provided password.
        // Produces $hash and $salt values.
        $security = \Helper\Security::instance();
		extract($security->hash($user["password"]));
        unset($user["password"]);
        $user["password"] = $hash;
        $user["salt"] = $salt;

        $new_user = self::create($user);
        return $new_user;
    }

}
