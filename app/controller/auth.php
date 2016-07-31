<?php

namespace Controller;

use \Model\User;
use \Helper\Exceptions\NotFound;

class Auth extends \Resource {

    // Constants
    const MIN_NAME_LENGTH       = 2;      // minimum name length
    const MAX_NAME_LENGTH       = 100;    // maximum name length
    const MIN_USERNAME_LENGTH   = 5;      // minimum username length
    const MAX_USERNAME_LENGTH   = 20;     // maximum username length
    const MIN_PASSWORD_LENGTH   = 8;      // minimum password length
    const MAX_PASSWORD_LENGTH   = 200;    // maximum password length
    const MAX_ABOUT_LENGTH      = 500;    // maximum about length

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

    // Reserved usernames
    // List of usernames which cannot be registered by
    // users. This should prevent spamming.
    private $_reserved_useranmes = array(
        "admin",
        "administrator",
        "secret",
        "help",
        "helper",
        "question",
        "questions",
        "answer",
        "answers",
        "document",
        "documents",
        "public",
        "website",
        "webmaster",
        "security",
        "person",
        "audit",
        "auditor",
        "laptop",
        "jobs",
        "download",
        "downloads",
        "reply",
        "response",
        "fucking",
        "pussy"
    );

    // Holds associative array of error messages.
    private $_error_messages = array();

    public function __construct()
    {
        $this->_error_messages = array(
            "name_required"         => "Name is required.",
            "name_short"            => sprintf("Name is too short (%d char minimum).", self::MIN_NAME_LENGTH),
            "name_long"             => sprintf("Name is too long (%d char max).", self::MAX_NAME_LENGTH),
            "username_required"     => "Username is required.",
            "username_short"        => sprintf("Username is too short (%d char minimum).", self::MIN_USERNAME_LENGTH),
            "username_long"         => sprintf("Username is too long (%d char max).", self::MAX_USERNAME_LENGTH),
            "useranme_alpha"        => "Username must be alphanumeric.",
            "username_reserved"     => "This username cannot be registered.",
            "username_taken"        => "Username is already taken.",
            "username_begin_num"    => "Username cannot begin with a number",
            "username_begin_dash"   => "Username cannot begin with a dash or an underscore.",
            "username_registered"   => "Username is already taken.",
            "password_required"     => "Password is required.",
            "password_as_username"  => "Password cannot be the same as username.",
            "password_short"        => sprintf("Password is too short (%d char minimum).", self::MIN_PASSWORD_LENGTH),
            "password_long"         => sprintf("Password is too long (%d char max).", self::MAX_PASSWORD_LENGTH),
            "password_common"       => "Password you chose is too common.",
            "password_confirm_req"  => "Password confirmation is required.",
            "password_confirm_ne"   => "Password confirmation does not match password.",
            "email_required"        => "Email is required.",
            "email_format"          => "Email address does not seem to be valid.",
            "email_registered"      => "Email is already in use.",
            "username_password_req" => "Username and password are required.",
            "username_password_err" => "Username and password combination is not valid.",
            "username_not_reg"      => "Username was not found."
        );
    }

    public function auth($f3, $params)
    {
        $post = $f3->get("POST");

        // Remove leading and trailing spaces
        array_walk_recursive($post, function(&$value, $key) {
            $value = trim($value);
        });

        // New user registration
        // Process form submission. Ensure all fields are present
        // and meet all the requirements for a new registration.
        if (array_key_exists("register", $post)) {

            $errors = array();
            $user_model = new User;

            // Name is required
            if (!array_key_exists("full_name", $post)) {
                $errors[] = $this->_error_messages["name_required"];

            // Name must be of a specific length
            } elseif (strlen($post["full_name"]) < self::MIN_NAME_LENGTH) {
                $errors[] = $this->_error_messages["name_short"];
            } elseif (strlen($post["full_name"]) > self::MAX_NAME_LENGTH) {
                $errors[] = $this->_error_messages["name_long"];
            }

            // Username is required
            if (!array_key_exists("username", $post)) {
                $errors[] = $this->_error_messages["username_required"];

            // Username must be of a specific length
            } elseif (strlen($post["username"]) < self::MIN_USERNAME_LENGTH) {
                $errors[] = $this->_error_messages["username_short"];
            } elseif (strlen($post["username"]) > self::MAX_USERNAME_LENGTH) {
                $errors[] = $this->_error_messages["username_long"];

            // Other username validations
            } else {

                // Username must be alphanumeric.
                // Prior to validation, remove dashes and underscores from the username value
                // because they are allowed but are not techincally considered as alpha
                // by ctype_alpha functions.
                $validChars = array('-', '_');
                if (!ctype_alnum(str_replace($validChars, '', $post["username"]))) {
                    $errors[] = $this->_error_messages["username_alpha"];

                // Username cannot begin with a dash or an underscore
                } elseif (substr($post["username"], 0, 1) == "-" || substr($post["username"], 0, 1) == "_") {
                    $errors[] = $this->_error_messages["username_begin_dash"];

                // Username cannot begin with a number
                } elseif (ctype_digit(substr($post["username"], 0, 1))) {
                    $errors[] = $this->_error_messages["username_begin_num"];

                // Username must not be in our reserved list
                } elseif (in_array(strtolower($post["username"]), $this->_reserved_useranmes)) {
                    $errors[] = $this->_error_messages["username_reserved"];

                // Other username validations
                } else {

                    // Verify if username is already taken
                    $user_by_username = $user_model->get_by("username", strtolower($post["username"]));
                    if ($user_by_username) {
                        $errors[] = $this->_error_messages["username_registered"];
                    }
                }
            }

            // Password is required
            if (!array_key_exists("password", $post)) {
                $errors[] = $this->_error_messages["password_required"];

            // Password must not be the same as username
            } elseif ($post["password"] == $post["username"]) {
                $errors[] = $this->_error_messages["password_as_username"];

            // Password must be of a specific length
            } elseif (strlen($post["password"]) < self::MIN_PASSWORD_LENGTH) {
                $errors[] = $this->_error_messages["password_short"];
            } elseif (strlen($post["password"]) > self::MAX_PASSWORD_LENGTH) {
                $errors[] = $this->_error_messages["password_long"];

            // Password must not be in our list of most common passwords
            } elseif (in_array(strtolower($post["password"]), $this->_common_passwords)) {
                $errors[] = $this->_error_messages["password_common"];
            }

            // Password confirmation is required
            if (!array_key_exists("password_confirmation", $post)) {
                $errors[] = $this->_error_messages["password_confirmation"];

            // Verify if password matches confirmation
            } elseif ($post["password_confirmation"] != $post["password"]) {
                $errors[] = $this->_error_messages["password_confirm_ne"];
            }

            // Email address is required
            if (!array_key_exists("email", $post)) {
                $errors[] = $this->_error_messages["email_required"];

            // Email address must be in the correct email format (name@email.com)
            } elseif (!filter_var($post["email"], FILTER_VALIDATE_EMAIL)) {
                $errors[] = $this->_error_messages["email_format"];

            // Other email validations
            } else {

                // Verify if email address was already used
                $user_by_email = $user_model->get_by("email", strtolower($post["email"]));
                if ($user_by_email) {
                    $errors[] = $this->_error_messages["email_registered"];
                }

            }

            if (!empty($errors)) {
                $f3->set("errors", $errors);
                $f3->set("post", $post);
            } else {

                // Create a new user
                $user_model = new User;
                $user_obj = array(
                    "full_name" => $post["full_name"],
                    "username"  => strtolower($post["username"]),   // alwasy lowercase
                    "email"     => strtolower($post["email"]),      // alwasy lowercase for consitancy
                    "password"  => $post["password"]
                );

                // Save user
                $new_user = $user_model->create_user($user_obj);

                if ($path = $f3->get("GET.to")) {
                    $f3->reroute($path);
                } else {
                    $f3->reroute("/login?registered=true");
                }

            }

        }

        // Login validation
        if (array_key_exists("login", $post)) {

            $errors = array();

            // Username and password is required
            if (!array_key_exists("username", $post) ||
                !array_key_exists("password", $post)) {
                $errors[] = $this->_error_messages["username_password_req"];

            // Username is too short
            } elseif (strlen($post["username"]) < self::MIN_USERNAME_LENGTH) {
                $errors[] = $this->_error_messages["username_short"];
            } elseif (strlen($post["password"]) < self::MIN_PASSWORD_LENGTH) {
                $errors[] = $this->_error_messages["password_short"];
            }

            if (!empty($errors)) {
                $f3->set("errors", $errors);
                $f3->set("post", $post);

            } else {
                $user_model = new User;
                $user = $user_model->get_by("username", $post["username"]);
                if (!empty($user)) {
                    $user = $user[key($user)];

                    $confirmed = $user_model->confirm_password($user, $post["password"]);
                    if ($confirmed) {
                        $user_model->open_session($user);

                        if ($path = $f3->get("GET.to")) {
                            $f3->reroute($path);
                        } else {
                            $f3->reroute("/");
                        }
                    } else {
                        $f3->set("errors", array($this->_error_messages["username_password_err"]));
                    }
                } else {
                    $f3->set("errors", array($this->_error_messages["username_not_reg"]));
                }
            }
        }

		$this->render("auth/login.html");
    }

    public function logout($f3)
    {
        $user_model = new User;
        $user_model->end_session();

        $f3->reroute("/");
    }

}
