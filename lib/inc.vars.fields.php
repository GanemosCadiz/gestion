<?php

// ==============================================================
// Variable definition file (fields)
// --------------------------------------------------------------

/*
FIELDS
------------------------------------------------------------------

name -> (required) internal name of field.
type -> (required, can be function) type of field. Allowed values:
				- internal: For internal use. Not displayed in forms.
				- uneditable: Showed in form but not editable.
				- hidden: Hidden input field.
				- text, password, number, email, tel: Regular input fields.
				- textarea: Textarea.
				- select: Select field (use of options property required).
				- select-plus: Select field with a plus button to add elements (use of options and aux properties required).
				- switch: On/off switch.
				- switch-list: List of on/off switchs.
				- checkbox-list: List of checkboxes.
				- autocomplete-list: List of items added through autocomplete search.
				- button-group: Group of buttons that behave like radiobuttons (only one value possible).
				- add-list: List of names added by an 'add' button.
				- block-open: Opens a field block.
				- block-close: Closes a field block.
				- html: A free html code.
				- post-process: Field is post-processed only on the server side.
label -> (optional) Label in forms.
default -> (optional, can be function) Default value of field.
pre-process -> (must be function) Function called to pre-process field data.
process -> (optional, can be function) Function or string name of function called to post-process field data.
required -> (optional, can be function) If present and true, field is required.
length -> (optional) Max length of field in characters.
options -> (optional, can be function) Array of key => value for selects or list type of fields.
events -> (optional) Array of events assigned by javascript to field.
validation -> (optional) Type of validation (client and server side).
validation-msg -> (optional) Message that validation system with display if validation fails.
aux -> (optional) Aux parameters for some type of fields.
legend -> (optional, can be function) Additional info for form field.
encrypted -> (optional) If present field value is encrypted.
salted -> (optional) If present field value is encrypted with salt.
nodb -> (optional) If present field will not be stored into database.
html -> (optional, can be function) Html code for html types.
style -> (optional) Styling options for some type of fields.
*/


// --------------------------------------------------------------
// Users

$cfg['fields']['users'] = array(

	// User fields
	
	'id' => array(
											'name' => "user_id", 
											'type' => "internal", 
											'label' => "Id de usuario"
											), 
	'type' => array(
											'name' => "type", 
											'type' => "select", 
											'label' => "Tipo de usuario", 
											'default' => "user", 
											'legend' => "<strong>Usuario normal:</strong> la única restricción es que no tiene acceso a la gestión de Usuarios.", 
											'options' => array_slice($cfg['options']['user-types'], 1), 
											'events' => array(
																				'load' => "if (core.cfg.app.section == 'edit') {
																											$(\"#f_type_container\").hide();
																										} else {
																											$(\"#f_type_container\").show();
																										}", 
																				'change' => "	var legend = $(this).parent().find(\"span.legend\");
																											switch ($(this).val()) {
																												case \"user\":
																												default:
																													legend.html(\"<strong>Usuario normal:</strong> no tiene acceso a la gestión de Usuarios y se le pueden añadir otros tipos de restricciones.\");
																													$(\"#f_block-modules-open_container\").slideDown();
																													$(\"#f_block-author-open_container\").slideDown();
																													$(\"#f_block-sites-open_container\").slideDown();
																													$(\"#f_block-categories-open_container\").slideDown();
																												break;
																												case \"readonly\":
																													legend.html(\"<strong>Usuario de consulta:</strong> como el Usuario normal pero solamente puede consultar los datos. No puede crear, modificar ni borrar nada.\");
																													$(\"#f_block-modules-open_container\").slideDown();
																													$(\"#f_block-author-open_container\").slideDown();
																													$(\"#f_block-sites-open_container\").slideDown();
																													$(\"#f_block-categories-open_container\").slideDown();
																												break;
																												case \"admin\":
																													legend.html(\"<strong>Administrador:</strong> tiene acceso a toda la aplicación, incluida la gestión de Usuarios.\");
																													$(\"#f_block-modules-open_container\").slideUp();
																													$(\"#f_block-author-open_container\").slideUp();
																													$(\"#f_block-sites-open_container\").slideUp();
																													$(\"#f_block-categories-open_container\").slideUp();
																												break;
																											}"
																				), 
											'required' => true
											), 
	'type2' => array(
											'name' => "type2", 
											'type' => "uneditable", 
											'label' => "Tipo de usuario", 
											'default' => function($item) {
																								global $cfg, $var, $obj;
																								if ($cfg['options']['user-types'] == "new") {
																									return "";
																								} else {
																									$type = core::fieldGetVal("users", $item, "type");
																									return $cfg['options']['user-types'][$type];
																								}
																							}, 
											'events' => array(
																				'load' => "if (core.cfg.app.section == 'edit') {
																											$(\"#f_type2_container\").show();
																										} else {
																											$(\"#f_type2_container\").hide();
																										}"
																				)
											), 
	'name' => array(
											'name' => "name", 
											'type' => "text", 
											'label' => "Nombre", 
											'legend' => "Nombre para identificar al usuario en la plataforma.", 
											'required' => true, 
											'encrypted' => true, 
											'salted' => true
											), 
	'username' => array(
											'name' => "username", 
											'type' => "text", 
											'label' => "Nombre de usuario", 
											'legend' => "Nombre que el usuario usará para acceder a la plataforma.", 
											'required' => true, 
											'validation' => "username", 
											'validation-msg' => "Debe tener al menos 8 caracteres.", 
											'encrypted' => true
											), 
	'password' => array(
											'name' => "password", 
											'type' => "password", 
											'label' => "Contraseña", 
											'legend' => function() {
																								global $cfg, $var, $obj;
																								if ($var['page']['section'] == "new") {
																									return "";
																								} else {
																									return "<strong>Rellenar solamente si se desea cambiar la contraseña</strong>.";
																								}
																						}, 
											'required' => function() {
																								global $cfg, $var, $obj;
																								if ($var['page']['section'] == "new") {
																									return true;
																								} else {
																									return false;
																								}
																							}, 
											'validation' => "password", 
											'validation-msg' => "Debe tener al menos 8 caracteres.", 
											'process' => function($v) {
																								global $cfg, $var, $obj;
																								$previous = (isset($var['item']) && core::fieldGetVal("users", $var['item'], "password") != "") ? core::fieldGetVal("users", $var['item'], "password") : "";
																								if ($v == "") {
																									return $previous;
																								} else {
																									return myCrypt::hashCreate($v);
																								}
																							}
											), 
	'password2' => array(
											'name' => "password2", 
											'type' => "password", 
											'nodb' => true, 
											'label' => "Confirmar contraseña", 
											'legend' => function() {
																							global $cfg, $var, $obj;
																								if ($var['page']['section'] == "new") {
																									return "";
																								} else {
																									return "<strong>Rellenar solamente si se desea cambiar la contraseña</strong>.";
																								}
																						}, 
											'required' => function() {
																								global $cfg, $var, $obj;
																								if ($var['page']['section'] == "new") {
																									return true;
																								} else {
																									return false;
																								}
																							}, 
											'validation' => "password-check", 
											'validation-msg' => "Las contraseñas no coinciden."
											), 
	'active' => array(	
											'name' => "active", 
											'type' => "switch", 
											'label' => "Activo", 
											'default' => 1, 
											'required' => true
											), 
	'permissions' => array(
											'name' => "permissions", 
											'type' => "post-process", 
											'process' => function($v) {
																								global $cfg, $var, $obj;
																								return misc::jsonEncode(admin::userPermissionsCreate($_POST['data']));
																							}, 
											'encrypted' => true, 
											'salted' => true
											), 
	'block-modules-open' => array(
											'name' => "block-modules-open", 
											'type' => "block-open", 
											'label' => "Permitir el acceso a módulos"
											), 
	'permissions-modules' => array(
											'name' => "permissions-modules", 
											'type' => "checkbox-list", 
											'label' => "Permitir acceso a", 
											'options' => function() {
																								global $cfg, $var, $obj;
																								return admin::getModulesRestricted();
																							}, 
											'default' => function($user) {
																								global $cfg, $var, $obj;
																								$default = array();
																								$user_permissions = json_decode(core::fieldGetVal("users", $user, "permissions"), true);
																								$user_modules = ($var['page']['section'] == "new") ? $cfg['defaults']['user-permissions']['modules'] : $user_permissions['modules'];
																								$restricted = admin::getModulesRestricted();
																								foreach ($restricted as $k => $v) {
																									if ($user_modules === "all"
																											|| in_array($k, $user_modules)) {
																										array_push($default, $k);
																									}
																								}
																								return implode(",", $default);
																							}, 
											'aux' => "60%", 
											'nodb' => true
											), 
	'block-modules-close' => array(
											'name' => "block-modules-close", 
											'type' => "block-close"
											), 
	'block-author-open' => array(
											'name' => "block-author-open", 
											'type' => "block-open", 
											'label' => "Acceso a contenido ajeno"
											), 
	'permissions-noauthor' => array(
											'name' => "permissions-noauthor", 
											'type' => "switch", 
											'label' => "Permitir acceso", 
											'legend' => "Si no se permite el acceso a contenido ajeno el usuario solamente tendrá acceso a los elementos que el usuario haya creado.", 
											'default' => function($user) {
																								global $cfg, $var, $obj;
																								$user_permissions = json_decode(core::fieldGetVal("users", $user, "permissions"), true);
																								$user_noauthor = ($var['page']['section'] == "new") ? $cfg['defaults']['user-permissions']['noauthor'] : $user_permissions['noauthor'];
																								if ($user_noauthor) {
																									return "1";
																								} else {
																									return "0";
																								}
																							}, 
											'nodb' => true
											), 
	'block-author-close' => array(
											'name' => "block-author-close", 
											'type' => "block-close"
											), 
	'block-categories-open' => array(
											'name' => "block-categories-open", 
											'type' => "block-open", 
											'label' => "Limite de acceso a categorías"
											), 
	'permissions-categories' => array(
											'name' => "permissions-categories", 
											'type' => "checkbox-list", 
											'label' => "Sólo acceso a", 
											'legend' => "Si marca una categoría el usuario solamente tendrá acceso a los elementos de la/s categoría/s marcada/s. Si no marca ninguna el usuario podrá acceder a todas categorías.", 
											'options' => function() {
																								global $cfg, $var, $obj;
																								return core::getList("categories");
																							}, 
											'default' => function($user) {
																								global $cfg, $var, $obj;
																								$user_permissions = json_decode(core::fieldGetVal("users", $user, "permissions"), true);
																								$user_categories = ($var['page']['section'] == "new") ? $cfg['defaults']['user-permissions']['categories'] : $user_permissions['categories'];
																								if ($user_categories === "all") {
																									return "";
																								} else {
																									return implode(",", $user_categories);
																								}
																							}, 
											'aux' => "48%", 
											'nodb' => true
											), 
	'block-categories-close' => array(
											'name' => "block-categories-close", 
											'type' => "block-close"
											), 
	'block-contact-open' => array(
											'name' => "block-contact-open", 
											'type' => "block-open", 
											'label' => "Datos de contacto"
											), 
	'email' => array(
											'name' => "email", 
											'type' => "email", 
											'label' => "Dirección de email", 
											'default' => function($user) {
																								global $cfg, $var, $obj;
																								$user_data = json_decode(core::fieldGetVal("users", $user, "data"), true);
																								return (isset($user_data['email'])) ? $user_data['email'] : "";
																							}, 
											'validation' => "email", 
											'nodb' => true
											), 
	'phone' => array(
											'name' => "phone", 
											'type' => "tel", 
											'label' => "Teléfono de contacto", 
											'default' => function($user) {
																								global $cfg, $var, $obj;
																								$user_data = json_decode(core::fieldGetVal("users", $user, "data"), true);
																								return (isset($user_data['phone'])) ? $user_data['phone'] : "";
																							}, 
											'validation' => "phone", 
											'nodb' => true
											), 
	'data' => array(
											'name' => "data", 
											'type' => "post-process", 
											'process' => function($v) {
																								global $cfg, $var, $obj;
																								$data = array(
																									'email' => (isset($_POST['data']['email'])) ? inputClean::clean($_POST['data']['email'], 150) : "", 
																									'phone' => (isset($_POST['data']['phone'])) ? inputClean::clean($_POST['data']['phone'], 20) : ""
																								); 
																								return misc::jsonEncode($data);
																							}, 
											'encrypted' => true, 
											'salted' => true
											), 
	'block-contact-close' => array(
											'name' => "block-contact-close", 
											'type' => "block-close"
											), 
	
	// Internal fileds
	
	'stats_items' => array(
											'name' => "stats_items", 
											'type' => "internal"
											), 
	'login_current' => array(
											'name' => "login_current", 
											'type' => "internal"
											), 
	'login_last' => array(
											'name' => "login_last", 
											'type' => "internal"
											), 
	'user_id' => array(
											'name' => "creator_id", 
											'type' => "internal"
											), 
	'date_creation' => array(
											'name' => "date_creation", 
											'type' => "internal"
											), 
	'date_modification' => array(
											'name' => "date_modification", 
											'type' => "internal"
											), 
	'salt' => array(
											'name' => "salt", 
											'type' => "internal"
											), 
	'deleted' => array(	
											'name' => "deleted", 
											'type' => "internal"
											)
);

// --------------------------------------------------------------
// Categories

$cfg['fields']['categories'] = array(
	
	// Category fields
	
	'id' => array(
											'name' => "category_id", 
											'type' => "internal", 
											'label' => "Id de categoría"
											), 
	'parent_id' => array(
											'name' => "parent_id", 
											'type' => "hidden", 
											'label' => "Id de categoría padre", 
											'default' => function($item) {
																								global $cfg, $var, $obj;
																								if ($var['page']['action'] == "new") {
																									if (isset($_GET['parent'])) {
																										$parent_id = inputClean::clean($_GET['parent'], 11);
																										$category = core::getItem("categories", $parent_id);
																										if (!$category) {
																											core::errorFatal("<p>La categoría no existe o ha sido borrada.</p><p><a href=\"javascript:core.history.back();\" title=\"Volver\"><i class=\"glyphicon glyphicon-arrow-left\"></i> Volver</a></p>");
																										}
																										return $parent_id;
																									} else {
																										return 0;
																									}
																								} else {
																									return core::fieldGetVal("categories", $item, "parent_id");
																								}
																							}
											), 
	'parent_name' => array(
											'name' => "parent_name", 
											'type' => "uneditable", 
											'label' => "Categoría padre", 
											'default' => function($item) {
																								global $cfg, $var, $obj;
																								if ($var['page']['action'] == "new") {
																									if (isset($_GET['parent'])) {
																										$parent_id = inputClean::clean($_GET['parent'], 11);
																									} else {
																										$var['javascript-post'] .= "$('#f_parent_name_container').hide();";
																										return "";
																									}
																								} else {
																									$parent_id = core::fieldGetVal("categories", $item, "parent_id");
																									if ($parent_id == 0) {
																										$var['javascript-post'] .= "$('#f_parent_name_container').hide();";
																										return "";
																									}
																								}
																								$category = core::getItem("categories", $parent_id);
																								if (!$category) {
																									core::errorFatal("<p>La categoría no existe o ha sido borrada.</p><p><a href=\"javascript:core.history.back();\" title=\"Volver\"><i class=\"glyphicon glyphicon-arrow-left\"></i> Volver</a></p>");
																								}
																								return core::fieldGetVal("categories", $category, "name");
																							}, 
											'nodb' => true
											), 
	'name' => array(
											'name' => "name", 
											'type' => "text", 
											'label' => "Nombre", 
											'required' => true
											), 
	
	// Internal fields
	
	'ord' => array(
											'name' => "ord", 
											'type' => "internal"
											), 
	'url' => array(
											'name' => "url", 
											'type' => "internal"
											), 
	'stats_items' => array(
											'name' => "stats_items", 
											'type' => "internal"
											), 
	'translations' => array(
											'name' => "translations", 
											'type' => "internal"
											), 
	'user_id' => array(
											'name' => "user_id", 
											'type' => "internal"
											), 
	'date_creation' => array(
											'name' => "date_creation", 
											'type' => "internal"
											), 
	'date_modification' => array(
											'name' => "date_modification", 
											'type' => "internal"
											), 
	'active' => array(	
											'name' => "active", 
											'type' => "internal"
											), 
	'deleted' => array(	
											'name' => "deleted", 
											'type' => "internal"
											)
);

// --------------------------------------------------------------
// Images

$cfg['fields']['images'] = array(
	
	// Image fields
	
	'image' => array(
											'name' => "image", 
											'type' => "uneditable", 
											'label' => "Imagen", 
											'default' => "<a href=\"javascript:;\" class=\"image-zoom\"></a>", 
											'nodb' => true
											), 
	'gallery_id' => array(
											'name' => "gallery_id", 
											'type' => "select", 
											'label' => "Galería", 
											'options' => function() {
																								global $cfg, $var, $obj;
																								return core::getList("images_galleries", array('zero-first' => false));
																							}
											), 
	'name' => array(
											'name' => "name", 
											'type' => "text", 
											'label' => "Nombre", 
											'legend' => "Nombre interno para diferenciar la imagen.", 
											'required' => true
											), 
	'filename' => array(
											'name' => "filename", 
											'type' => "text", 
											'label' => "Nombre de archivo", 
											'legend' => "Nombre del archivo de imagen (<strong>sin extensión</strong>). Será convertido a caracteres compatibles.", 
											'required' => true
											), 
	'alt' => array(
											'name' => "alt", 
											'type' => "text", 
											'label' => "Descripción", 
											'legend' => "Descripción de la imagen (alt).", 
											'required' => false
											), 
	// Internal fields
	
	'id' => array(
											'name' => "image_id", 
											'type' => "internal"
											), 
	'type' => array(
											'name' => "type", 
											'type' => "internal"
											), 
	'files' => array(
											'name' => "files", 
											'type' => "internal"
											), 
	'user_id' => array(
											'name' => "user_id", 
											'type' => "internal"
											), 
	'date_creation' => array(
											'name' => "date_creation", 
											'type' => "internal"
											), 
	'date_modification' => array(
											'name' => "date_modification", 
											'type' => "internal"
											), 
	'active' => array(	
											'name' => "active", 
											'type' => "internal"
											), 
	'deleted' => array(	
											'name' => "deleted", 
											'type' => "internal"
											)
);

$cfg['fields']['images_galleries'] = array(
	
	// Internal fields
	
	'id' => array(
											'name' => "gallery_id", 
											'type' => "internal"
											), 
	'name' => array(
											'name' => "name", 
											'type' => "text", 
											'label' => "Nombre de la galería", 
											'required' => true
											), 
	'user_id' => array(
											'name' => "user_id", 
											'type' => "internal"
											), 
	'date_creation' => array(
											'name' => "date_creation", 
											'type' => "internal"
											), 
	'date_modification' => array(
											'name' => "date_modification", 
											'type' => "internal"
											), 
	'active' => array(	
											'name' => "active", 
											'type' => "internal"
											), 
	'deleted' => array(	
											'name' => "deleted", 
											'type' => "internal"
											)
);

// --------------------------------------------------------------
// Log admin

$cfg['fields']['log_admin'] = array(
	
	// Internal fields
	
	'id' => array(
											'name' => "id", 
											'type' => "internal"
											), 
	'target' => array(
											'name' => "target", 
											'type' => "internal", 
											'encrypted' => true, 
											'salted' => true
											), 
	'action' => array(
											'name' => "action", 
											'type' => "internal", 
											'encrypted' => true, 
											'salted' => true
											), 
	'item' => array(
											'name' => "item", 
											'type' => "internal", 
											'encrypted' => true, 
											'salted' => true
											), 
	'ip' => array(
											'name' => "ip", 
											'type' => "internal", 
											'encrypted' => true, 
											'salted' => true
											), 
	'user_id' => array(
											'name' => "user_id", 
											'type' => "internal"
											), 
	'date' => array(	
											'name' => "date", 
											'type' => "internal"
											), 
	'salt' => array(
											'name' => "salt", 
											'type' => "internal"
											)
);

?>