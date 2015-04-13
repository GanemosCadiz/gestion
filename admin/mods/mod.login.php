<?php

// Security check
if (!isset($var) && !isset($var['page'])) { die("Access not allowed."); }

// ==============================================================
// Login module
// --------------------------------------------------------------

// Login action
if (isset($_POST['username']) && isset($_POST['password'])) {
 	die(misc::jsonEncode(core::login($_POST['username'], $_POST['password'])));
}

// Render content

$content = "";

$content .= "
<section id=\"login\" class=\"panel panel-default\">

	<div class=\"panel-heading\">
    <h3 class=\"panel-title\">Acceso</h3>
  </div>
  
	<form class=\"form-signin\">
		<input type=\"text\" name=\"i_username\" id=\"i_username\" class=\"form-control\" placeholder=\"Nombre de usuario\" autofocus />
		<input type=\"password\" name=\"i_password\" id=\"i_password\" class=\"form-control\" placeholder=\"Contraseña\" />
		<div class=\"alert alert-warning\"></div>
		<button type=\"submit\" class=\"btn btn-lg btn-primary btn-block\">Acceder</button>
	</form>

</section>
";


// ======================================
// Render page

adminHtml::renderPage(array(
	'type' => "normal", 
	'content' => $content
));

?>