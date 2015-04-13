<?php

// Security check
if (!isset($var) && !isset($var['module'])) { die("Access not allowed."); }

// ==============================================================
// Home module
// --------------------------------------------------------------

html::renderPage(array(
	'type' => "normal", 
	'content' => "Hello world!"
));

?>