<?php

// Security check
if (!isset($var) && !isset($var['page'])) { die("Access not allowed."); }

// ==============================================================
// Logout module
// --------------------------------------------------------------

// Logout action
core::logout();

// Render content

$content = "";

$content .= "
<section id=\"login\" class=\"panel panel-default\">

	<div class=\"panel-heading\">
    <h3 class=\"panel-title\">Desconectado</h3>
  </div>
  
	<form class=\"form-signin\" style=\"text-align: center;\">
		<p>Has salido de la aplicación.</p>
		<a href=\"" . $cfg['urls']['app'] . "\" title=\"Volver a la página de login\" class=\"btn btn-primary\">Login</a>
	</form>

</section>
";


// ======================================
// Render page

adminHtml::renderPage(array(
	'type' => "normal", 
	'content' => $content
));