<?php

// Security check
if (!isset($var) && !isset($var['page'])) { die("Access not allowed."); }

// ==============================================================
// Help module
// --------------------------------------------------------------

$path = $cfg['urls']['app'];

// Pages definitions
switch ($_POST['item']) {
	
	default:
		$html = "<h1>Ayuda no disponible</h1>
							<p>La ayuda para esta sección no está disponible de momento.</p>
							<p>Disculpe las molestias.</p>";
	break;
	
}

die(misc::jsonEncode(array('result' => "ok", 'html' => $html)));

?>