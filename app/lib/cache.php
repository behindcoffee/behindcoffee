<?php

namespace Lib;

class Cache extends \Prefab {

    private static function engine()
    {
        $base = \Base::instance();
        return $base->get("cache");
    }

    public static function get($key)
    {
        $engine = self::engine();

        if (!is_array($key)) {
            $key = array($key);
        }

        $items = array();
        foreach ($key as $k) {
            $cached = $engine->get($k);
            if ($cached) {
                $items[$k] = $cached;
            }
        }

        return $items;
    }

    public static function set($key, $item, $expire = 0)
    {
        $engine = self::engine();
        return $engine->set($key, $item, $expire);
    }

    public static function clear($key)
    {
        $engine = self::engine();
        return $engine->clear($key);
    }

    public static function reset()
    {
        $engine = self::engine();
        return $engine->reset();
    }

}
