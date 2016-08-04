<?php

namespace Controller;

use \Helper\Exceptions\NotFound;

class Comment extends \Resource {

    // Constants
    const MIN_COMMENT_LENGTH = 50;      // minimum comment length
    const MAX_COMMENT_LENGTH = 10000;   // maximum comment length
    const MIN_REPLY_LENGTH = 5;         // minimum reply length
    const MAX_REPLY_LENGTH = 500;       // maximum reply length

    // Holds associative array of error messages.
    private $_error_messages = array();

    public function __construct()
    {
        $this->_error_messages = array(
            "comment_required"    => "Text is required.",
            "comment_length"      => sprintf("Text must be between %d and %d characters.", self::MIN_COMMENT_LENGTH, self::MAX_COMMENT_LENGTH),
            "reply_required"    => "Text is required.",
            "reply_length"      => sprintf("Text must be between %d and %d characters.", self::MIN_REPLY_LENGTH, self::MAX_REPLY_LENGTH)
        );
    }

    private function add_comment($author, $post, $parent, $body, $ip, $format = true)
    {
        $model = new \Model\Comment();
        $new_comment = $model->add($post, $parent, $author, $body, $format);

        return $new_comment;
    }

    public function answer($f3, $params)
    {

        // Require user to be logged in. If not, redirect
        // to login page and record this page for later redirect.
        $this->requireLogin();

        try {
            $post_model = new \Model\Post();
            $post = $post_model->get_post($params["id"]);
            $post = $post[$params["id"]];

            $post_params = $f3->get("POST");
            if ($post_params) {

                $errors = array();

                // Remove leading and trailing spaces
                array_walk_recursive($post_params, function(&$value, $key) {
                    $value = trim($value);
                });

                if (array_key_exists("comment", $post_params)) {

                    // Text is required
                    if (!isset($post_params["body"])) {
                        $errors[] = $this->_error_messages["comment_required"];

                    // Text must be of a certain length
                    } else {
                        if (strlen($post_params["body"]) < self::MIN_COMMENT_LENGTH
                            || strlen($post_params["body"]) > self::MAX_COMMENT_LENGTH) {
                            $errors[] = $this->_error_messages["comment_length"];
                        }
                    }

                    if (empty($errors)) {
                        $reply_id = $this->add_comment($f3->get("G.user"), $post, $post, $post_params["body"], $f3->get("IP"));
                        $f3->reroute("/question/" . $params["id"] . "?replied=true#answer-" . $reply_id);
                    } else {
                        $f3->set("text", $post_params["body"]);
                        $f3->set("errors", $errors);
                    }
                }
            }

            $f3->set("post", $post);

            $this->render("answer.html");

        } catch (NotFound $e) {
            $this->render("errors/404.html");
        }
    }

    public function reply($f3, $params)
    {

        // Require user to be logged in. If not, redirect
        // to login page and record this page for later redirect.
        $this->requireLogin();

        // Get comment by id
        $comment_model = new \Model\Comment();
        $comment = $comment_model->get_by_id(array($params["id"]));
        $comment = $comment[$params["id"]];

        // Get post by id
        $post_model = new \Model\Post();
        $post = $post_model->get_by_id(array($comment["post_id"]));
        $post = $post[$comment["post_id"]];

        // TODO: Verify that replies are allowed.

        $post_params = $f3->get("POST");
        if ($post_params) {

            $errors = array();

            // Remove leading and trailing spaces
            array_walk_recursive($post_params, function(&$value, $key) {
                $value = trim($value);
            });

            if (array_key_exists("comment", $post_params)) {

                // Text is required
                if (!isset($post_params["body"])) {
                    $errors[] = $this->_error_messages["reply_required"];

                // Text must be of a certain length
                } else {
                    if (strlen($post_params["body"]) < self::MIN_REPLY_LENGTH
                        || strlen($post_params["body"]) > self::MAX_REPLY_LENGTH) {
                        $errors[] = $this->_error_messages["reply_length"];
                    }
                }

                if (empty($errors)) {
                    $reply_id = $this->add_comment($f3->get("G.user"), $post, $comment, $post_params["body"], $f3->get("IP"), false);
                    $f3->reroute("/question/" . $post["id"] . "?replied=true#response-" . $comment["id"]);
                } else {
                    $f3->set("text", $post_params["body"]);
                    $f3->set("errors", $errors);
                }
            }
        }

        $f3->set("post", $post);
        $f3->set("comment", $comment);
        $this->render("reply.html");
    }

}
