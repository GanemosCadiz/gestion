<?php

// ==============================================================
// Main configuration file
// --------------------------------------------------------------

// Protection (from WordPress)
if (isset($HTTP_POST_VARS['GLOBALS']) || isset($HTTP_POST_FILES['GLOBALS']) || isset($HTTP_GET_VARS['GLOBALS']) || isset($HTTP_COOKIE_VARS['GLOBALS'])) {
	die(":(");
}
if (isset($HTTP_SESSION_VARS) && !is_array($HTTP_SESSION_VARS)) {
	die(":(");
}

session_start();

// Init
$cfg = array();
$var = array();
$obj = array();

// Server detection
$cfg['localhost'] = in_array($_SERVER['HTTP_HOST'], array("localhost", "127.0.0.1", "192.168.1.2", "84.127.245.142"));

// Defining paths
$cfg['paths'] = array(
	'shared' => $cfg['localhost'] ? ROOT . "_shared/" : 	// Shared path at localhost.
																	ROOT . "_shared/", 	// Shared path at server.
	'secret' => $cfg['localhost'] ? ROOT . "private/" : // Secret path at localhost.
																	ROOT . "private/", 	// Secret path at server.
	'root' => ROOT
);
$cfg['paths']['site'] = $cfg['paths']['root'];
$cfg['paths']['admin'] = $cfg['paths']['root'] . "admin/";

// Include libraries
require_once($cfg['paths']['shared'] . "scripts/php/class/class.inputClean.php");
require_once($cfg['paths']['shared'] . "scripts/php/class/class.crypt.php");
require_once($cfg['paths']['shared'] . "scripts/php/class/class.app.core.php");
require_once($cfg['paths']['shared'] . "scripts/php/class/class.functions.date.php");
require_once($cfg['paths']['shared'] . "scripts/php/class/class.functions.misc.php");
require_once($cfg['paths']['shared'] . "scripts/php/class/class.functions.number.php");
require_once($cfg['paths']['shared'] . "scripts/php/class/class.functions.string.php");
require_once($cfg['paths']['shared'] . "scripts/php/class/class.functions.validation.php");
require_once($cfg['paths']['site'] . "lib/class.app.php");
if (APP == "admin") {
	require_once($cfg['paths']['shared'] . "scripts/php/class/class.pagination.php");
	require_once($cfg['paths']['shared'] . "scripts/php/class/class.upload.php");
	require_once($cfg['paths']['shared'] . "scripts/php/class/class.upload.image.php");
	require_once($cfg['paths']['shared'] . "scripts/php/class/class.admin.core.php");
	require_once($cfg['paths']['shared'] . "scripts/php/class/class.admin.core.html.php");
	require_once($cfg['paths']['site'] . "lib/class.admin.php");
	require_once($cfg['paths']['site'] . "lib/class.admin.html.php");
	require_once($cfg['paths']['site'] . "lib/class.admin.stats.php");
} else if (APP == "site") {
	
}

// Include vars
require_once($cfg['paths']['site'] . "lib/inc.vars.php");
require_once($cfg['paths']['site'] . "lib/inc.vars.fields.php");
if (APP == "admin") {
	require_once($cfg['paths']['site'] . "lib/inc.vars.admin.php");
} else if (APP == "site") {
	require_once($cfg['paths']['site'] . "lib/inc.vars.site.php");
}

// Include secret config
require_once($cfg['paths']['secret'] . "inc.secret.php");

// Localization
require_once($cfg['paths']['shared'] . "scripts/php/class/class.localization.php");
$obj['lang'] = new localization(array(
	'default' => $cfg['lang']['default'], 
	'allowed' => $cfg['lang']['allowed']
));

// Database connection
require_once($cfg['paths']['shared'] . "scripts/php/class/class.dbLayer.php");
$obj['db'] = new dbLayer(array(
																'debug' => $cfg['app']['test-mode'], 
																'force_errors' => $cfg['app']['force-errors']
																));
$obj['db']->connect($cfg['db']['host'], $cfg['db']['database'], $cfg['db']['user'], $cfg['db']['password']);
if (!$obj['db']->connected) {
	$obj['db']->error("Error connecting to database.");
}

?>