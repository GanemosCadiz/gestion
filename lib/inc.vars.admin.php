<?php

// ==============================================================
// Variable definition file (admin)
// --------------------------------------------------------------

// Lists
$cfg['lists'] = array(
	'items-page' => 25
);

// Reserved keywords
$cfg['reserved-keywords'] = array("external", "ip");

// Stats
$cfg['stats'] = array(
	'summary-days' => 30, 
	'top-items' => 10, 
	'img-width' => 1000, 
	'img-height' => 400, 
	'palette' => array("#ff0000", "#F78F01", "#5E2D00", "#5AB56E", "#C0AB58", "#FFEED0"), 
	'year-start' => 2014, 
	'app-start' => "2014-03-01", 
	'max-margin' => 1.05
);


// -----------------------------------------
// Modules definition

$cfg['modules'] = array(
	
	'login' => array(
									'code' => "login", 
									'menu' => false, 
									'allowed-users' => array(), 
									'default' => true, 
									'texts' => array(
																		'title' => "Acceso", 
																		'name' => "Acceso a la aplicación"
																		)
									), 
	'home' => array(
									'code' => "home", 
									'menu' => true, 
									'allowed-users' => array("root", "admin", "user", "readonly"), 
									'texts' => array(
																		'title' => "Inicio", 
																		'button' => "Inicio", 
																		'name' => "Inicio"
																		)
									), 
	'stats' => array(
									'code' => "stats", 
									'menu' => false, 
									'allowed-users' => array("root", "admin", "user", "readonly"), 
									'restricted' => true, 
									'texts' => array(
																		'title' => "Estadísticas", 
																		'button' => "Estadísticas", 
																		'name' => "Estadísticas"
																		), 
									'sections' => array(
																			'summary' => array(
																											'code' => "summary", 
																											'menu' => true, 
																											'allowed-users' => array("root", "admin", "user", "readonly"), 
																											'default' => true, 
																											'texts' => array(
																																				'title' => "Resumen", 
																																				'button' => "Resumen", 
																																				'icon' => "stats"
																																				)
																											), 
																			'chart' => array(
																											'code' => "chart", 
																											'menu' => false, 
																											'allowed-users' => array("root", "admin", "user", "readonly"), 
																											'texts' => array(
																																				'title' => "Gráficas"
																																				)
																											)
																			)
									), 
	'users' => array(
									'code' => "users", 
									'menu' => true, 
									'allowed-users' => array("root"), 
									'texts' => array(
																		'title' => "Usuarios", 
																		'button' => "Usuarios", 
																		'name' => "Gestión de usuarios"
																		), 
									'sections' => array(
																			'list' => array(
																											'code' => "list", 
																											'menu' => true, 
																											'allowed-users' => array("root"), 
																											'default' => true, 
																											'texts' => array(
																																				'title' => "Listado de Usuarios", 
																																				'button' => "Listado de Usuarios", 
																																				'icon' => "list"
																																				)
																											), 
																			'edit' => array(
																											'code' => "edit", 
																											'menu' => false, 
																											'allowed-users' => array("root"), 
																											'texts' => array(
																																				'title' => "Editar Usuario"
																																				)
																											), 
																			'new' => array(
																											'code' => "new", 
																											'menu' => true, 
																											'allowed-users' => array("root"), 
																											'texts' => array(
																																				'title' => "Nuevo Usuario", 
																																				'button' => "Nuevo Usuario", 
																																				'icon' => "plus"
																																				)
																											)
																			)
									), 
	'search' => array(
									'code' => "search", 
									'menu' => false, 
									'allowed-users' => array("root", "admin", "user", "readonly"), 
									'texts' => array(
																		'title' => "Búsqueda", 
																		'button' => "Búsqueda", 
																		'name' => "Búsqueda"
																		), 
									'sections' => array(
																			'results' => array(
																											'code' => "results", 
																											'menu' => false, 
																											'allowed-users' => array("root", "admin", "user", "readonly"), 
																											'default' => true, 
																											'texts' => array(
																																				'title' => "Resultados de la búsqueda"
																																				)
																											)
																			)
									), 
	'help' => array(
									'code' => "help", 
									'menu' => false, 
									'allowed-users' => array("root", "admin", "user", "readonly"), 
									'texts' => array(
																		'title' => "Ayuda", 
																		'name' => "Ayuda"
																		)
									), 
	'log' => array(
									'code' => "log", 
									'menu' => true, 
									'allowed-users' => array("root"), 
									'texts' => array(
																		'title' => "Log", 
																		'button' => "Log", 
																		'name' => "Registro de Actividad"
																		)
									), 
	'action' => array(
									'code' => "action", 
									'menu' => false, 
									'allowed-users' => array("root", "admin", "user", "readonly", "download")
									), 
	'js' => array(
									'code' => "js", 
									'menu' => false, 
									'allowed-users' => array()
									), 
	'logout' => array(
									'code' => "logout", 
									'menu' => false, 
									'allowed-users' => array(), 
									'texts' => array(
																		'title' => "Salir"
																		)
									), 
	'404' => array(
									'code' => "404", 
									'menu' => false, 
									'allowed-users' => array(), 
									'texts' => array(
																		'title' => "Página no encontrada"
																		)
									)
	
);


// -----------------------------------------
// Defaults

$cfg['defaults'] = array(
	
	// Default user permissions
	'user-permissions' => array(
		'modules' => "all", // List to allowed modules. "all" grants access to all modules.
		'categories' => "all", // List of categories (and content with these categories) user is allowed to access to. "all" grants access to all categories. Empty array denys access to all categories.
		'noauthor' => true, // Allow acces to content created by other users.
		'write' => true // Allow user to write/modify content.
	)

);

?>