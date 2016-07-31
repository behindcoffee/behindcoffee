<?php

namespace Controller;

use \Helper\Exceptions\NotFound;

class User extends \Resource {

    public function profile($f3)
    {

        // Require user to be logged in. If not, redirect
        // to login page and record this page for later redirect.
        $this->requireLogin();

        try {
            $user_model = new \Model\User();
            $user = $user_model->get_by_id(array($f3->get("G.user.id")));
            $user = $user[$f3->get("G.user.id")];

            $post = $f3->get("POST");
            if ($post) {

                extract($user_model->update_profile($user, $post["current_password"], $post));
                if ($status) {
                    $user = $user_model->get_by_id(array($f3->get("G.user.id")));
                    $user = $user[$f3->get("G.user.id")];
                }

                if (!empty($errors)) {
                    $f3->set("errors", $errors);
                } else {
                    $f3->reroute("/profile?success=true");
                }
            }

            $f3->set("rules", $auth_rules);
            $f3->set("profile", $user);
            $this->render("user/profile.html");

        } catch (NotFound $e) {
            $this->render("errors/404.html");
        }

    }

}
