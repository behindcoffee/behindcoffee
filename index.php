<?php

// Autoload vendors
require __DIR__.'/vendor/autoload.php';

// Retrieve instance of the framework
$f3 = \Base::instance();

// Get environmental variable
$env = getenv("APPLICATION_ENV");

// Set site URL
$f3->set("site.url", $f3->get("SCHEME") . "://" . $f3->get("HOST") . $f3->get("BASE") . "/");

// Initialize the app
$f3->config(sprintf("app/config/%s.ini", $env));

// Define routes
$f3->config('app/routes.ini');

// Set up memcache
$mc_host    = $f3->get("mc.host");
$mc_port    = $f3->get("mc.port");
$mc_str     = sprintf("memcache=%s:%d", $mc_host, $mc_port);

$cache      = \Cache::instance();

$cache->load($mc_str);
$f3->set("cache", $cache);

// Set up database connection
$db_host    = $f3->get("db.host");
$db_port    = $f3->get("db.port");
$db_name    = $f3->get("db.name");
$db_uname   = $f3->get("db.uname");
$db_pass    = $f3->get("db.password");
$db_str     = sprintf("mysql:host=%s;port=%d;dbname=%s", $db_host, $db_port, $db_name);

$f3->set("db", new \DB\SQL($db_str, $db_uname, $db_pass));

// Set up email
$f3->set('email.instance', new SendGrid($f3->get('email.key')));

// Load current account
$user = new Model\User();
$user->start_session();

// Execute application
$f3->run();
