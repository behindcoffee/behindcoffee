<?php

namespace Model;

class Comment extends Record {

    protected $table_name = "comment";

    public function add($post, $parent, $author, $body, $format = true)
    {

        // Create post
        $obj = array(
            'post_id'   => $post["id"],
            'parent_id' => $parent["id"],
            'author_id' => $author["id"]
        );

        // Formatter for body
        if ($format) {
            $parser = new \Helper\Cappuchino;
            $formatted_body = $parser->parse($body);

            $obj['body'] = $formatted_body;
        } else {
            $obj['body'] = $body;
        }

        $post = self::create($obj);

        return $post;
    }

    public function list_by($name, $field = null, $value = null, $options = null, $expire = 10)
    {
        $comments = parent::list_by($name, $field, $value, $options, $expire);

        if (!empty($comments)) {
            $author_ids = array_column($comments, 'author_id');
            $author_ids = array_unique($author_ids);

            $model = new User;
            $authors = $model->get_by_id($author_ids);

            foreach(array_keys($comments) as $key) {
                $comments[$key]["author"] = $authors[$comments[$key]["author_id"]];
            }
        }

        return $comments;
    }

}
