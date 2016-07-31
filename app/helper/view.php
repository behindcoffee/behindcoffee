<?php

namespace Helper;

class View extends \Template {

    public function __construct()
    {

        // Register filters
        $this->filter('dateTime', '$this->formatDateTime');
        $this->filter('shortTime', '$this->formatShortTime');
        $this->filter('shortDate', '$this->formatShortDate');
        $this->filter('timeSince', '$this->formatTimeSince');
        $this->filter('shortTimeSince', '$this->formatShortTimeSince');

        parent::__construct();

    }

    public function formatDateTime($time)
    {
        $t = strtotime($time);
        return date("l, F d g:i a", $t);
    }

    public function formatShortTime($time)
    {
        $t = strtotime($time);
        return date("g:i a", $t);
    }

    public function formatShortDate($time)
    {
        $t = strtotime($time);
        return date("M d", $t);
    }

    public function formatTimeSince($time)
    {
        $t = (int)date($time);

        if ($t == strtotime('today')) {
            return 'Today';
        } elseif ($t == strtotime('yesterday')) {
            return 'Yesterday';
        } else {
            return date("l, F d", $t);
        }
    }

    public function formatShortTimeSince($time)
    {
        $t = strtotime($time);
        $t = date("Y-m-d", $t);
        $t = strtotime($t);

        if ($t == strtotime('today')) {
            return 'Today';
        } elseif ($t == strtotime('yesterday')) {
            return 'Yesterday';
        } else {
            return date("M d", $t);
        }
    }

}
