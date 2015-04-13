<?php

// Security check
if (!isset($var) && !isset($var['page'])) { die("Access not allowed."); }

// ==============================================================
// Home module
// --------------------------------------------------------------

// Check if stats can be shown
$show_stats = core::userCheckModule("stats");

$content = "";

$content .= "<section id=\"home\" class=\"panel panel-default panel-main\">
							
							<div class=\"panel-heading\">
						    <h3 class=\"panel-title\">Inicio</h3>
						  </div>
						  
						  <div class=\"panel-body\">
						  	
						  	<div class=\"well\">
						  		<p>Resumen de actividad de la aplicación.</p>
								</div>";



if ($show_stats) {
	
	$content .= "<div class=\"home-data\">
								
								<div class=\"panel panel-default\">
									<div class=\"panel-heading\">
								    <h3 class=\"panel-title\">Resumen de estadísticas</h3>
								  </div>
								  <div class=\"panel-body\">
								  	<p>Actualizadas hasta ayer (" . date::format($var['now']." -1 days") . ").</p>
								  	<div class=\"stats-summary\">";
	
	$content .= "		</div>
						  	</div>
					  	</div>";
	
}


$content .= "<div class=\"panel panel-default" . (!$show_stats ? " standalone" : "") . "\">
							<div class=\"panel-heading\">
						    <h3 class=\"panel-title\">Resumen de elementos</h3>
						  </div>
						  <div class=\"panel-body\">
						  	<div class=\"stats-summary\">";

// ------------------------------------------
// Elements summary

$elements = array(
	array('table' => "users", 'title' => "usuarios", 'url' => "users/")
);

foreach ($elements as $n => $element) {
	
	// User query permissions
	$user_permissions = core::userPermissionQuery($element['table']);
	
	$q = "SELECT COUNT(*) AS num FROM " . core::tableGetName($element['table']) . " 
							WHERE 
								" . core::fieldGetName($element['table'], "deleted") . "='0' " . 
								($user_permissions != "" ? " AND " . $user_permissions . " " : "");
	$r = $obj['db']->query($q);
	$num = $obj['db']->getValues($r);
	$content .= "<div class=\"number\"><p>" . ucfirst($element['title']) . "</p><span><strong>" . number::format($num) . "</strong></span><a href=\"" . $cfg['urls']['app'] . $element['url'] . "\" class=\"btn btn-default btn-sm\" title=\"Ir al listado de " . $element['title'] . "\">Ir <i class=\"glyphicon glyphicon-chevron-right\"></i></a></div>";
	
}

$content .= "			</div>
						  	</div>
							</div>";

if ($show_stats) {
	$content .= "</div>";
}

// ------------------------------------------
// Charts

if ($show_stats) {
	
	$content .= "<div class=\"home-charts\">";
	
	$content .= adminHtml::moduleStatsCharts(array(
		array(
					'type' => "graph", 
					'code' => "stats-home-graph-users", 
					'title' => "Usuarios", 
					'description' => "Usuarios registrados en la plataforma en los últimos " . $cfg['stats']['summary-days'] . " días."
					)
	));
	
	$content .= "</div>";
	
}

$content .= "			</div>
							 	</div>
								
								<div class=\"clearfix\"></div>
								
							</section>";


// ======================================
// Render page

adminHtml::renderPage(array(
	'type' => "normal", 
	'content' => $content
));

?>