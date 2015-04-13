<?php

// ==============================================================
// App engine
// --------------------------------------------------------------

// Define app
define("APP", "admin");
define("ROOT", str_replace(array("admin" , "\\"), array("", "/"), __DIR__) . "/");

// Load configuration
require_once(ROOT . "lib/inc.config.php");

// User authorization
require_once($cfg['paths']['shared'] . "scripts/php/class/class.user.php");
$obj['user'] = new user(array(
															'conn' => $obj['db'], 
															'crypt_key' => $cfg['crypt-key']
															));
$obj['user']->getUser($cfg['session']['prefix']);
if (!$obj['user']) {
	core::errorLogin("<p>Access not allowed.</p>");
}

// Checks page access and gets page info
$var['page'] = core::getPage();

// Check user access
$var['user-allow'] = core::userAllow($var['page']);

// Load page
require_once($var['page']['file']);

// Garbage maintenance
core::garbageCollector();

die();

?>