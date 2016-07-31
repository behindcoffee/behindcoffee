<?php

namespace Model;

class Post extends Record {

    protected $table_name = 'post';

    public function add($title, $body, $author)
    {

        // Formatter for body
        $parser = new \Helper\Cappuchino;
        $formatted_body = $parser->parse($body);

        // Create post
        $obj = array(
            'title' => $title,
            'body' => $formatted_body,
            'user_id' => $author["id"]
        );

        $post = self::create($obj);

        return $post;
    }

    public function recent_posts($include_author = false)
    {
        $posts = self::list_by("recent");

        if ($include_author) {
            $posts = self::wrap($posts);
        }
        return $posts;
    }

    public function get_post($id)
    {
        $post_by_id = self::get_by_id(array($id));
        $post_by_id = self::wrap($post_by_id);

        return $post_by_id;
    }

    private function wrap($posts)
    {
        $user_model = new User();
        $wrapped = array();

        $user_ids = array_column($posts, "user_id");
        $user_ids = array_unique($user_ids);
        $users = $user_model->get_by_id($user_ids);

        foreach ($posts as $key => $post) {
            if ($post["user_id"]) {
                $post["author"] = $users[$post["user_id"]];
                $wrapped[$key] = $post;
            }
        }

        return $wrapped;
    }
}
