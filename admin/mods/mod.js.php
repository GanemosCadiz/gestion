<?php

// Security check
if (!isset($var) && !isset($var['page'])) { die("Access not allowed."); }

// ==============================================================
// Javascript module
// --------------------------------------------------------------

core::jsLoad();

?>