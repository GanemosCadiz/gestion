<?php

// ==============================================================
// Secret configuration
// --------------------------------------------------------------

// Store here any sensible config data and passwords.

if (!isset($cfg)) { $cfg = array(); }

// ---------------------------------------
// Database connection

if (!isset($cfg['db'])) { $cfg['db'] = array(); }

if ($cfg['localhost']) {
	// Localhost connection
	$cfg['db']['host'] = "localhost";
	$cfg['db']['database'] = "ganemos-gestion";
	$cfg['db']['user'] = "root";
	$cfg['db']['password'] = "payacho";
} else {
	// Server connection
	$cfg['db']['host'] = "localhost";
	$cfg['db']['database'] = "framewoork";
	$cfg['db']['user'] = "framewoork";
	$cfg['db']['password'] = "";
}

// ---------------------------------------
// Encryption

// Please use long and secure strings
$cfg['crypt-key'] = "2CWJ@ajg3!nt3W8p@4PYVzcK#p*X4AXvWbpjHhNY?69DD9";
$cfg['crypt-key-public'] = "&nEF7bR%*FeExpCK*r5E%Xe*r89gdN3W8VM!48a2nM@93VuB";


// ---------------------------------------
// SMTP config

// SMTP configuration for mailing
$cfg['smtp'] = array(
	'server' => "smtp.server.com", 
	'port' => 25, 
	'auth' => true, 
	'username' => "username", 
	'password' => "password", 
	//'secure' => "tls", 
	'from' => "admin@server.com", 
	'timeout' => 5
);

?>