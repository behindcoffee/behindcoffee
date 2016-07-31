<?php

namespace Lib;

use \DB\SQL\Mapper;
use \Helper\Passport;
use \Helper\Exceptions\ParameterError;

class Item extends \Prefab {

    public static function get_by_id($table, $ids)
    {
        // var_dump("calling get_by_id on $table");
        $base = \Base::instance();
        $db = $base->get("db");

        $table = new Mapper($db, $table);

        $id_str = join("','", $ids);
        $items_by_id = $table->find(array("id IN ('".$id_str."')"));

        $items_ids = array();
        foreach ($items_by_id as $item) {
            $items_ids[$item->id] = $item->cast();
        }

        return $items_ids;
    }

    public static function list_by($table, $field = null, $value = null, $options = null)
    {
        // var_dump("calling list_by on $table");
        $base = \Base::instance();
        $db = $base->get("db");

        $table = new Mapper($db, $table);
        $filter = null;
        if ($field && $value) {
            $filter = array("$field = ?", $value);
        }

        $items_by_id = $table->find($filter, array(
            "order" => "date_created DESC",
            "limit" => 1000
        ));

        $items_ids = array();
        foreach ($items_by_id as $item) {
            $items_ids[] = $item->id;
        }

        return $items_ids;
    }

    public static function create($table, $data)
    {
        // var_dump("calling create on $table");
        $base = \Base::instance();
        $db = $base->get("db");

        $mapper = new Mapper($db, $table);

        $passport = Passport::instance();
        $mapper->id = $passport->genSequentialId();

        foreach ($data as $field => $value) {
            $mapper->set($field, $value);
        }

        // Log current IP address
        $mapper->set("created_ip", $base->IP);

        $item = $mapper->insert();
        return $item->id;
    }

    public static function update($table, $data)
    {
        // var_dump("calling update on $table");
        $base = \Base::instance();
        $db = $base->get("db");

        $mapper = new Mapper($db, $table);
        $mapper->load(array("id = ?", $data["id"]));
        unset($data["id"]);

        //
        foreach ($data as $field => $value) {
            $mapper->set($field, $value);
        }

        // Set updated date and time
        $mapper->set("date_updated", time());

        // Log current IP address
        $mapper->set("updated_ip", $base->IP);

        $updated = $mapper->update();
        return $updated->id;
    }

}
