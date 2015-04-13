<?php

// ==============================================================
// App engine
// --------------------------------------------------------------

// Define app
define("APP", "site");
define("ROOT", str_replace("\\", "/", __DIR__) . "/");

// Load configuration
require_once(ROOT . "lib/inc.config.php");

// Checks page access and gets page info
$var['page'] = core::getPage();

// Load page
require_once($var['page']['file']);

// Garbage maintenance
core::garbageCollector();

die();

?>