<?php

// Security check
if (!isset($var) && !isset($var['module'])) { die("Access not allowed."); }

// ==============================================================
// Javascript module
// --------------------------------------------------------------

core::jsLoad();

?>