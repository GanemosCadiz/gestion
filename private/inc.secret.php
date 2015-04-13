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
	$cfg['db']['database'] = "framewoork";
	$cfg['db']['user'] = "framewoork";
	$cfg['db']['password'] = "";
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
$cfg['crypt-key'] = "very_long_encryption_key";
$cfg['crypt-key-public'] = "very_long_encryption_key";


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