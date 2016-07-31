<?php

namespace Controller;

class Recent extends \Resource {

    const MAX_RECORDS = 20;

    public function index($f3, $params)
    {
        $post_model = new \Model\Post();
        $post = $post_model->recent_posts(True);

        // $post = self::group_by_date($post, $f3->get("GET")["next"]);
        $post = self::sort($post, $f3->get("GET")["next"]);

        // $security = new \Helper\Security();
        // $start = time() - 1467331200;
        // var_dump(time());
        //
        // var_dump($start);
        // $start = strrev($start);
        // $start = str_pad($start, 7, 0, STR_PAD_RIGHT);
        // $start =  $start . "000001";
        // var_dump($start);
        //
        // var_dump($security->alpha_id($start));

        $f3->set("page_content.next", $f3->get("GET.next") + 1);
        $f3->set("page_content.items", $post);
        $this->render("questions.html");
    }

    private function sort($posts, $offset = 0)
    {
        $grouped = array();

        uasort($posts, array('self', "_recent_sorter"));

        $posts = array_slice($posts, $offset * (self::MAX_RECORDS - 1), self::MAX_RECORDS);

        return $posts;
    }

    private function group_by_date($posts, $offset = 0)
    {
        $grouped = array();

        uasort($posts, array('self', "_recent_sorter"));

        $posts = array_slice($posts, $offset * (self::MAX_RECORDS - 1), self::MAX_RECORDS);

        $date_created = array_column($posts, "date_created");
        $date_created = array_map('self::str_to_date_val', $date_created);
        $date_created = array_unique($date_created);
        uasort($date_created, array('self', "_date_sorter"));

        foreach ($date_created as $date_val) {
            $grouped[$date_val] = array();
        }

        // uasort($posts, array('self', "_recent_sorter"));

        foreach ($posts as $key => $value) {
            $post_date = $posts[$key]["date_created"];
            $date_val = self::str_to_date_val($post_date);
            $grouped[$date_val][] = $posts[$key];
        }

        return $grouped;
    }

    private function str_to_date_val($var)
    {
        $dt = date("Y-m-d", strtotime($var));
        return strtotime($dt);
    }

    private function _recent_sorter($a, $b) {
        return $a['date_created'] < $b['date_created'];
    }

    private function _date_sorter($a, $b)
    {
        return $a < $b;
    }

}
