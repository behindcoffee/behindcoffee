<?php

namespace Controller;

use \Helper\Exceptions\NotFound;

class Post extends \Resource {

    // Constants
    const MIN_TITLE_LENGTH = 5;         // minimum title length
    const MAX_TITLE_LENGTH = 500;       // maximum title length
    const MAX_BODY_LENGTH = 5000;       // maximum body length

    // Holds associative array of error messages.
    private $_error_messages = array();

    public function __construct()
    {
        $this->_error_messages = array(
            "title_required"    => "Title is required.",
            "title_short"       => sprintf("Title is too short (%d char min).", self::MIN_TITLE_LENGTH),
            "title_long"        => sprintf("Title is too long (%d char max).", self::MAX_TITLE_LENGTH),
            "body_long"         => sprintf("Body is too long (%d char max).", self::MAX_BODY_LENGTH)
        );
    }

    private function add_comment($author, $post, $body, $ip)
    {
        $comment_obj = array(
            "post_id" => $post["id"],
            "parent_id" => $post["id"],
            "body" => $body,
            "author_id" => $author["id"],
            "ip" => $ip
        );
        $model = new \Model\Comment();
        $new_comment = $model->add_comment($comment_obj);

        return $new_comment;
    }

    public function show_post($f3, $params) {

        $params = $f3->get('PARAMS');
        $_post_id = $params["id"];

        try {
            $post_model = new \Model\Post();
            $post_by_id = $post_model->get_post($_post_id);

            $comments_model = new \Model\Comment();
            $comments_by_post_id = $comments_model->list_by($_post_id, "post_id", $_post_id);

            // Comment IDs
            $comment_ids = array_keys($comments_by_post_id);

            // Storage for replies
            $replies = array();

            // Loop through all comment records and find the ones where
            // parent_id is not the same as post_id.
            foreach ($comment_ids as $id) {
                $post_id    = $comments_by_post_id[$id]["post_id"];
                $parent_id  = $comments_by_post_id[$id]["parent_id"];

                // This is a reply.
                if ($parent_id != $post_id) {
                    $reply = $comments_by_post_id[$id];
                    $comments_by_post_id[$parent_id]["replies"][$id] = $reply;
                    unset($comments_by_post_id[$id]);
                }
            }

            $f3->set("comments", $comments_by_post_id);
            $f3->set("post", $post_by_id[$_post_id]);

            $this->render("question.html");

        } catch (NotFound $e) {
            $this->render("errors/404.html");
        }

    }

    public function ask($f3)
    {
        // Require user to be logged in. If not, redirect
        // to login page and record this page for later redirect.
        $this->requireLogin();

        $post_params = $f3->get("POST");

        if ($post_params) {

            // Remove leading and trailing spaces
            array_walk_recursive($post_params, function(&$value, $key) {
                $value = trim($value);
            });

            $errors = array();

            // Title is required
            if (!array_key_exists('title', $post_params)) {
                $errors[] = $this->_error_messages["title_required"];

            // Title must of a specific length
            } elseif (strlen(trim($post_params["title"])) < self::MIN_TITLE_LENGTH) {
                $errors[] = $this->_error_messages["title_short"];
            } elseif (strlen(trim($post_params["title"])) > self::MAX_TITLE_LENGTH) {
                $errors[] = $this->_error_messages["title_long"];
            }

            // If body is specified, it must not exceed of a specific length
            if (array_key_exists('body', $post_params) && strlen(trim($post_params["body"])) > self::MAX_BODY_LENGTH) {
                $errors[] = $this->_error_messages["body_long"];
            }

            if (empty($errors)) {
                $post_model = new \Model\Post();
                $post_by_id = $post_model->add(
                    $post_params["title"],
                    $post_params["body"],
                    $f3->get("G.user")
                );

                $f3->reroute("/question/" . $post_by_id);
            } else {
                $f3->set("errors", $errors);
                $f3->set("post", $post_params);
            }

        }

        $this->render("ask.html");

    }

}
