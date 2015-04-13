<?php

// ==============================================================
// Variable definition file
// --------------------------------------------------------------

// -----------------------------------------
// App config

// Application configuration
$cfg['app'] = array(
	'name' => "Framewoork", 
	'title' => "Framewoork", 
	'costumer' => "26horas", 
	'copyright' => "© " . date("Y") . " 26horas"
);

// Server configuration
if ($cfg['localhost']) {
	
	// Localhost configuration
	ini_set("display_errors", "1");
	$cfg['app']['test-mode'] = true;
	$cfg['app']['force-errors'] = true;
	
} else {
	
	// Server configuration
	ini_set("display_errors", "0");
	$cfg['app']['test-mode'] = false;
	$cfg['app']['force-errors'] = false;
	
}

// Folders
$cfg['folders'] = array(
	'root' => "/framewoork/", 
	'site' => "", 
	'admin' => "admin/"
);

// Domain
$cfg['protocol'] = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$cfg['domain'] = $cfg['protocol'] . "://" . $_SERVER['HTTP_HOST'];

// Urls
$cfg['urls'] = array(
	'admin' => $cfg['folders']['root'] . $cfg['folders']['admin'], 
	'site' => $cfg['folders']['root'] . $cfg['folders']['site']
);

// App
$cfg['urls']['app'] = $cfg['urls'][APP];

// Localization
$cfg['lang'] = array(
	'default' => "es", 
	'allowed' => array("es", "en")
);

// Sessions
$cfg['session'] = array(
	'prefix' => "framewoork", 
	'life' => 1 // Cookie life (days)
);


// -----------------------------------------
// Options definitions

$cfg['options'] = array(
	
	'activity' => array(
			'1' => "Activo", 
			'0' => "Inactivo"
	), 
	
	'user-types' => array(
			'root' => "Root", 
			'admin' => "Administrador", 
			'user' => "Usuario", 
			'readonly' => "Sólo lectura"
	)
	
);


// -----------------------------------------
// Database definition

// Database definition
$cfg['db-tables'] = array(
	
	'categories' => array(
												'name' => "cfg_categories", 
												'title-single' => "Categoría", 
												'title-plural' => "Categorías", 
												'id' => "category_id", 
												'user_id' => "user_id", 
												'category_id' => "category_id", 
												'parent_id' => "parent_id", 
												'check-duplicated' => array("name"), 
												'levels' => 1, 
												'encrypted' => false, 
												'extra' => array(
																				'save' => function($new_data, $mode, $old_data) {
																										global $cfg, $var, $obj;
																										
																									}, 
																				'delete' => function($item) {
																											global $cfg, $var, $obj;
																											$id = core::fieldGetVal("categories", $item, "id");
																											// Remove categories from sites
																											$q = "UPDATE " . core::tableGet("sites", "name") . " SET 
																																			" . core::fieldGetName("sites", "category_id") . "='0' 
																																		WHERE 
																																			" . core::fieldGetName("sites", "category_id") . "='" . $id . "'";
																											$r = $obj['db']->query($q);
																											// Delete subcategories
																											$q = "UPDATE " . core::tableGet("categories", "name") . " SET 
																																			" . core::fieldGetName("categories", "deleted") . "='1' 
																																		WHERE 
																																			" . core::fieldGetName("categories", "parent_id") . "='" . $id . "'";
																											$r = $obj['db']->query($q);
																										}
																				)
												), 
	'images' => array(
												'name' => "cfg_images", 
												'title-single' => "Imagen", 
												'title-plural' => "Imágenes", 
												'id' => "image_id", 
												'user_id' => "user_id", 
												'check-duplicated' => array("filename"), 
												'encrypted' => false, 
												'extra' => array(
																				'save' => function($new_data, $mode, $old_data) {
																										global $cfg, $var, $obj;
																										$rename = admin::imageRename($new_data, $old_data);
																									}
																				)
												), 
	'images_galleries' => array(
												'name' => "cfg_images_galleries", 
												'title-single' => "Galería", 
												'title-plural' => "Galerías", 
												'id' => "gallery_id", 
												'user_id' => "user_id", 
												'check-duplicated' => array("name"), 
												'encrypted' => false, 
												'extra' => array(
																				'delete' => function($item) {
																										global $cfg, $var, $obj;
																										$rename = admin::imageGalleryDelete($item);
																									}
																				)
												), 
	'users' => array(
												'name' => "users", 
												'title-single' => "Usuario", 
												'title-plural' => "Usuarios", 
												'id' => "user_id", 
												'user_id' => "creator_id", 
												'check-duplicated' => array("username"), 
												'encrypted' => true, 
												'extra' => array(
																				'save' => function($new_data, $mode, $old_data) {
																										global $cfg, $var, $obj;
																										if ($mode == "edit" && $new_data[core::fieldGetName("users", "password")] != "") {
																											// Delete user sessions on password change
																											core::userSessionDelete($new_data);
																										}
																									}, 
																				'activate' => function($item, $value) {
																										global $cfg, $var, $obj;
																										if ($value == 0) {
																											// Delete user sessions on deactivation
																											core::userSessionDelete($item);
																										}
																									}, 
																				'delete' => function($item) {
																										global $cfg, $var, $obj;
																										// Delete user sessions on deletion
																										core::userSessionDelete($item);
																									}
																				)
												), 
	'users_sessions' => array(
												'name' => "users_sessions", 
												'id' => "user_id", 
												'encrypted' => false
												), 
	'users_sessions_auto' => array(
												'name' => "users_sessions_auto", 
												'id' => "user_id", 
												'encrypted' => false
												), 
	'log_admin' => array(
												'name' => "log_admin", 
												'id' => "log_id", 
												'encrypted' => true
												)
	
);


// -----------------------------------------
// Fields definitions

// Fields config
$cfg['fields-cfg'] = array(
	// Parameters of fields that can be public
	'public' => array(
		"name", "type", "label", "required", "validation", "validation-msg"
	), 
	// Ignored type fields when defining form fields in Javascript
	'ignored-js' => array(
		"internal", "uneditable", "block-open", "block-close", "html", "post-process"
	), 
	// Ignored type fields when storing forms into database
	'ignored-db' => array(
		"internal", "uneditable", "switch-list", "autocomplete-list", "block-open", "block-close", "html"
	)
);


// -----------------------------------------
// Vars

$var['now'] = date::datetimeDB();
$var['time'] = time();
$var['error'] = false;

// Javascript inserted at the end of page
$var['javascript-post'] = "";

// CSS inserted at the end of page
$var['css-post'] = "";

// Html inserted at the end of page
$var['html-post'] = "";

?>