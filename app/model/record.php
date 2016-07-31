<?php

namespace Model;

use \Lib\Cache;
use \Lib\Item;
use \Helper\Exceptions\NotFound;

abstract class Record extends \Prefab {

    protected $table_name = "";

    public function get_by_id($ids, $skip_missing = false)
    {

        // Cache::reset();

        $items_from_cache = Cache::get($ids);
        $missing_ids = array();
        foreach ($ids as $id) {
            if (!array_key_exists($id, $items_from_cache)) {
                $missing_ids[] = $id;
            }
        }

        $items_from_db = array();
        if (!empty($missing_ids)) {
            $items_from_db = Item::get_by_id($this->table_name, $missing_ids);
            if (!empty($items_from_db)) {
                foreach($items_from_db as $item) {
                    Cache::set($item["id"], $item);
                }
            }
        }

        // $items_by_id = array_merge($items_from_cache, $items_from_db);
        $items_by_id = ($items_from_cache + $items_from_db);

        $missing = array();
        foreach ($ids as $id) {
            if (!array_key_exists($id, $items_by_id)) {
                $missing[] = $id;
            }
        }

        if (!empty($missing) && !$skip_missing) {
            throw new NotFound("Not Found: " . join($missing, ", "));
        }

        $sorted = $this->_sort($items_by_id);
        return $sorted;
    }

    public function get_by($field, $value, $options = null, $expire = 10)
    {
        $item = self::list_by($field."_".$value, $field, $value, $options, $expire);
        if (sizeof($item) > 1) {
            throw new \Helper\Exceptions\UnexpectedValue;
        }
        return $item;
    }

    private function _recent_sorter($a,$b) {
        return $a['date_created'] > $b['date_created'];
    }

    private function _sort($items)
    {
        uasort($items, array($this, "_recent_sorter"));
        return $items;
    }

    public function list_by($name, $field = null, $value = null, $options = null, $expire = 10)
    {
        $cache_key = $this->table_name . "_" . $name;
        $items_from_cache = Cache::get($cache_key);

        if (empty($items_from_cache)) {
            $item_ids = Item::list_by($this->table_name, $field, $value, $options);
            if (!empty($item_ids)) {
                Cache::set($cache_key, $item_ids, $expire);
            }
        } else {
            $item_ids = $items_from_cache[$cache_key];
        }

        return self::get_by_id($item_ids);
    }

    public function create($item)
    {
        $new_item = Item::create($this->table_name, $item);
        if ($new_item) {
            $item_by_id = self::get_by_id(array($new_item));
            return key($item_by_id);
        }

        return false;
    }

    public function update($new_values)
    {
        if (!array_key_exists("id", $new_values)) {
            throw new \Helper\Exceptions\ParameterError("Item `id` is required.");
        }

        $item_id = $new_values["id"];
        $item_from_cache = self::get_by_id(array($item_id));

        if (sizeof($item_from_cache) > 1) {
            throw new \Helper\Exceptions\UnexpectedValue("Expected only one item.");
        }

        $old_values = $item_from_cache[$item_id];
        unset($old_values["id"]);

        $merged_values = array_merge($old_values, $new_values);
        unset($merged_values["date_created"]);

        $updated = Item::update($this->table_name, $merged_values);
        if ($updated) {
            Cache::clear($updated);
            return self::get_by_id(array($updated));
        }

    }

}
