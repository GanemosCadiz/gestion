<?php

// Security check
if (!isset($var) && !isset($var['page'])) { die("Access not allowed."); }

// ==============================================================
// Users module
// --------------------------------------------------------------

// Pages definitions
switch ($var['page']['section']) {
	
	case "list":
		// --------------------------------------------------------------
		// List
		// --------------------------------------------------------------
		
		// -------------------------------------------
		// Action message
		
		$action_msg = "";
		
		if (isset($_SESSION['list-action'])) {
			
			if ($_SESSION['list-action']['action'] == "activate") {
				
				if ($_SESSION['list-action']['value'] == 0) {
					$action_msg = "<h4>Usuario desactivado</h4>
													<p>El usuario '<strong>" . core::fieldGetVal("users", $_SESSION['list-action']['item'], "name") . "</strong>' ha sido desactivado.</p>
													<p>Este usuario no podrá acceder a la plataforma mientras esté desactivado.</p>";
				} else {
					$action_msg = "<h4>Usuario activado</h4>
													<p>El usuario '<strong>" . core::fieldGetVal("users", $_SESSION['list-action']['item'], "name") . "</strong>' ha sido activado.</p>
													<p>Este usuario puede volver a acceder a la plataforma.</p>";
				}
			 	
			break;
				
			} else if ($_SESSION['list-action']['action'] == "delete") {
				
				$action_msg = "<h4>Usuario borrado</h4>
												<p>El usuario '" . core::fieldGetVal("users", $_SESSION['list-action']['item'], "name") . "' ha sido borrado.</p>";
				
			}
			
			unset($_SESSION['list-action']);
			
		}
		
		// -------------------------------------------
		// Delete message
		
		$var['javascript-post'] .= "
			core.lists.items.remove.title = \"Borrar Usuario\";
			core.lists.items.remove.content = \"<h4>¿Está seguro de querer borrar el usuario '**name**'?</h4>" . 
																					"<p>Esta acción no puede deshacerse.</p>\";";
		
		// -------------------------------------------
		// Module configuration
		
		// Page navigation
		$var['page']['navigation'] = array(
																				'Inicio' => $cfg['urls']['app'], 
																				'Gestión de Usuarios' => $cfg['urls']['app'] . "users/", 
																				'Listado de Usuarios' => ""
																				);
		
		// Page header
		$var['page']['header'] = array(
			'text' => "<p>Listado de los usuarios actualmente dados de alta en la aplicación.</p>", 
			'actions' => array(
												'title' => "Acciones", 
												'list' => array(
																				array(
																							'type' => "link", 
																							'title' => "Añadir un nuevo usuario", 
																							'text' => "Nuevo Usuario", 
																							'class' => "success", 
																							'icon' => "plus", 
																							'url' => $cfg['urls']['app'] . "users/new/"
																							), 
																				array(
																							'type' => "button", 
																							'title' => "Mostrar ayuda para esta sección", 
																							'text' => "Ayuda", 
																							'class' => "primary", 
																							'icon' => "question-sign", 
																							'onclick' => "main.help.show('users-list');"
																							)
																				)
												), 
			'filters' => array(
													'title' => "Filtrar listado", 
													'text' => "Mostrando solamente usuarios filtrados por:", 
													'list' => array(
																					array(
																								'field' => core::fieldGetName("users", "type"), 
																								'title' => "Tipo de Usuario", 
																								'options' => $obj['user']->data['type'] == "root" ? $cfg['options']['user-types'] : array_slice($cfg['options']['user-types'], 1)
																								), 
																					array(
																								'field' => core::fieldGetName("users", "active"), 
																								'title' => "Actividad", 
																								'options' => $cfg['options']['activity']
																								)
																					)
												)
		);
		
		// User query permissions
		$user_permissions = core::userPermissionQuery("users");
		
		// List definition
		$var['page']['list'] = array(
			'id' => "users-list", 
			'title' => "Listado de Usuarios", 
			'table' => core::tableGetName("users"), 
			'where' => ($obj['user']->data['type'] != "root" ? core::fieldGetName("users", "type") . "!='root' AND " : "") . " 
									" . core::fieldGetName("users", "deleted") . "='0'" . 
									($user_permissions != "" ? " AND " . $user_permissions . " " : ""), 
			'sorting-field' => core::fieldGetName("users", "id"), 
			'sorting-order' => "DESC", 
			'sorting-extra' => "", 
			'columns' => array(
													array(
																'field' => core::fieldGetName("users", "id"), 
																'title' => "ID", 
																'sortable' => true
																), 
													array(
																'field' => core::fieldGetName("users", "date_creation"), 
																'title' => "Alta", 
																'sortable' => true
																), 
													array(
																'field' => core::fieldGetName("users", "type"), 
																'title' => "Tipo", 
																'sortable' => true
																), 
													array(
																'field' => core::fieldGetName("users", "name"), 
																'title' => "Nombre", 
																'sortable' => true
																), 
													array(
																'field' => core::fieldGetName("users", "username"), 
																'title' => "Username", 
																'sortable' => true
																), 
													array(
																'field' => core::fieldGetName("users", "login_current"), 
																'title' => "Último Acceso", 
																'sortable' => true
																), 
													array(
																'field' => core::fieldGetName("users", "active"), 
																'title' => "Estado", 
																'sortable' => true
																), 
													array(
																'field' => "", 
																'title' => "Acciones", 
																'sortable' => false
																)
												), 
			'row' => function($item) {
				
				global $cfg, $var, $obj;
				
				$html = "";
				
				$id = core::fieldGetVal("users", $item, "id");
				$type = core::fieldGetVal("users", $item, "type");
				$name = core::fieldGetVal("users", $item, "name");
				$email = core::fieldGetVal("users", $item, "email");
				$phone = core::fieldGetVal("users", $item, "phone");
				$active = core::fieldGetVal("users", $item, "active");
				
				$html .= "<tr>
										<td>" . number::format($id) . "</td>
										<td class=\"date\">" . date::format(core::fieldGetVal("users", $item, "date_creation"), "datetimeTextShort") . "</td>
										<td>" . $cfg['options']['user-types'][$type] . "</td>
										<td class=\"name\">
											<strong>" . $name . "</strong>
											" . (($email != "") ? "<p class=\"email\"><a href=\"mailto:" . $email . "\" title=\"Enviar un email a este Usuario\">" . $email . "</a></p>" : "") . "
											" . (($phone != "") ? "<p class=\"phone\">" . $phone . "</p>" : "") . "
										</td>
										<td>" . core::fieldGetVal("users", $item, "username") . "</td>
										<td class=\"date\">" . (core::fieldGetVal("users", $item, "login_current") == "0" ? "-" : date::format(core::fieldGetVal("users", $item, "login_current"), "datetimeTextShort")) . "</td>
										<td><span class=\"label label-" . (($active == 1) ? "success\">Activo" : "warning\">Inactivo") . "</span></td>
										<td class=\"actions\">
											<a href=\"" . $cfg['urls']['app'] . "users/edit/?item=" . $id . "\" title=\"Editar este Usuario\" class=\"btn btn-success btn-sm\"><span class=\"glyphicon glyphicon-edit\"></span></a>
											" . ($type != "root" ? "<a href=\"javascript:core.lists.items.activate('users','" . $id . "','" . (($active == 1) ? "0" : "1") . "');\" title=\"" . (($active == 1) ? "Desactivar" : "Activar") . " este Usuario\" class=\"btn btn-" . (($active == 1) ? "warning" : "primary") . " btn-sm\"><span class=\"glyphicon glyphicon-" . (($active == 1) ? "pause" : "play") . "\"></span></a>" : "") . "
											" . ($type != "root" ? "<a href=\"javascript:core.lists.items.remove.ask('users','" . $id . "','" . string::toScript($name) . "');\" title=\"Borrar este Usuario\" class=\"btn btn-danger btn-sm\"><span class=\"glyphicon glyphicon-remove\"></span></a>" : "") . "
										</td>
									</tr>";
				
				return $html;
				
			}
		);
		
		// Render html
		$html = "";
		$html .= adminHtml::moduleHeader($action_msg);
		$html .= adminHtml::moduleList();
		
		// ======================================
		// Render page
		
		adminHtml::renderPage(array(
			'type' => "module", 
			'content' => $html, 
			'id' => $var['page']['module']
		));
		
	break;
	
	
	case "new":
	case "edit":
		// --------------------------------------------------------------
		// New/Edit
		// --------------------------------------------------------------
		
		// Don't allow readonly users
		if (!admin::userAllowWrite()) {
			admin::errorLogin("<p>No está autorizado a acceder a esta página.</p>");
		}
		
		// Get item
		$var['item'] = core::getItem("users");
		
		if (!$var['item']) {
			// Item not found
			core::errorFatal("<p>El usuario no existe o ha sido borrado.</p><p><a href=\"javascript:aux.history.back();\" title=\"Volver\"><i class=\"glyphicon glyphicon-arrow-left\"></i> Volver</a></p>");
		}
		
		// Check for root user
		if ($obj['user']->data['type'] != "root"  
				&& core::fieldGetVal("users", $var['item'], "type") == "root") {
			core::errorFatal("<p>El usuario no existe o ha sido borrado.</p><p><a href=\"javascript:aux.history.back();\" title=\"Volver\"><i class=\"glyphicon glyphicon-arrow-left\"></i> Volver</a></p>");
		}
		
		// -------------------------------------------
		// Messages
		
		if ($var['page']['section'] == "new") {
			// New
			
			$title = "Nuevo Usuario";
			$title_ok = "Nuevo usuario añadido";
			$text_ok = "<p>El nuevo usuario se añadió con éxito.</p>";
			$header_text = "<p>Puede crear un nuevo usuario. No olvide pulsar \"<strong>Guardar cambios</strong>\" para guardar los cambios.</p>";
			
		} else if ($var['page']['section'] == "edit") {
			// Edit
			
			// Check user access to content
			if (!core::userAllowContent("users", $var['item'])) {
				admin::errorLogin("<p>No está autorizado a acceder a esta página.</p>");
			}
			
			$title = "Editar Usuario";
			$title_ok = "Usuario editado";
			$text_ok = "<p>El usuario se editó con éxito.</p>";
			$header_text = "<p>Puede editar el usuario. No olvide pulsar \"<strong>Guardar cambios</strong>\" para guardar los cambios.</p>";
			
		}
		
		// -------------------------------------------
		// Module configuration
		
		// Navigation
		$var['page']['navigation'] = array(
																				'Inicio' => $cfg['urls']['app'], 
																				'Gestión de Usuarios' => $cfg['urls']['app'] . "users/", 
																				$title => ""
																				);
		
		if (isset($_GET['ok'])) {
			// ...............................
			// Ok message
			
			$var['page']['ok'] = array(
																	'title' => $title_ok, 
																	'content' => $text_ok . "
																								<p>Indique lo que desea realizar a continuación:</p>
																								<div class=\"buttons\">
																									<a href=\"" . $cfg['urls']['app'] . "users/new/\" title=\"Añadir un nuevo usuario\" class=\"btn btn-sm btn-success\"><i class=\"glyphicon glyphicon-plus\"></i> Añadir nuevo Usuario</a>
																									<a href=\"" . $cfg['urls']['app'] . "users/list/\" title=\"Volver al listado de usuarios\" class=\"btn btn-sm btn-info\"><i class=\"glyphicon glyphicon-list\"></i> Volver al listado de usuarios</a>
																									<a href=\"" . $cfg['urls']['app'] . "\" title=\"Volver al inicio\" class=\"btn btn-sm btn-primary\"><i class=\"glyphicon glyphicon-home\"></i> Volver al Inicio</a>
																								</div>"
																	);
			
			$html = "";
			$html .= adminHtml::moduleHeader();
			$html .= adminHtml::moduleOK();
			
			
		} else {
			// ...............................
			// Form
			
			$var['page']['header'] = array(
				'text' => $header_text, 
				'actions' => array(
													'title' => "Acciones", 
													'list' => array(
																					array(
																								'type' => "button", 
																								'title' => "Mostrar ayuda para esta sección", 
																								'text' => "Ayuda", 
																								'class' => "primary", 
																								'icon' => "question-sign", 
																								'onclick' => "main.help.show('users-" . $var['page']['section'] . "');"
																								)
																					)
													)
			);
			
			$var['page']['form'] = array(
				'table' => "users", 
				'title' => $title, 
				'width' => "700px"
			);
			
			$html = "";
			$html .= adminHtml::moduleHeader();
			$html .= adminHtml::moduleForm($var['page']['section']);
			
		}
		
		// ======================================
		// Render page
		
		adminHtml::renderPage(array(
			'type' => "module", 
			'content' => $html, 
			'id' => $var['page']['section']
		));
		
	break;
	
	
	default:
		core::errorFatal("<p>Access not allowed.</p>");
	break;
	
}

?>