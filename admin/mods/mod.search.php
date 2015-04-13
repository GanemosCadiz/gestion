<?php

// Security check
if (!isset($var) && !isset($var['page'])) { die("Access not allowed."); }

// ==============================================================
// Search module
// --------------------------------------------------------------

// Pages definitions
switch ($var['page']['section']) {
	
	case "results":
		// --------------------------------------------------------------
		// Results
		// --------------------------------------------------------------
		
		$var['page']['aux'] = array(
			'categories' => core::getList("categories"), 
			'users' => core::getList("users"), 
		);
		
		// Keywords
		$var['keywords'] = (isset($_GET['q'])) ? inputClean::clean($_GET['q']) : "";
		
		// -------------------------------------------
		// Module configuration
		
		// Page navigation
		$var['page']['navigation'] = array(
																				'Inicio' => $cfg['urls']['app'], 
																				'Búsqueda' => ""
																				);
		
		// Page header
		$var['page']['header'] = array(
			'text' => "<p>Resultado de buscar '<strong>" . $var['keywords'] . "</strong>' en la aplicación.</p>", 
			'actions' => array(
												'title' => "Acciones", 
												'list' => array(
																				array(
																							'type' => "search"
																							), 
																				array(
																							'type' => "button", 
																							'title' => "Mostrar ayuda para esta sección", 
																							'text' => "Ayuda", 
																							'class' => "primary", 
																							'icon' => "question-sign", 
																							'onclick' => "main.help.show('search');"
																							)
																				)
												)
		);
		
		// Search query
		$search_query = admin::getSearchQuery($var['keywords']);
		
		if ($search_query == "") {
			// No search possible
			
			$html .= "<div class=\"search-error\">
									<p><strong>No se pudo realizar la búsqueda.</strong></p>
									<p>Para poder realizar una búsqueda debe introducir un texto que buscar de al menos 3 caracteres.</p>
								</div>";
			
		} else {
			// Search possible
			
			// List definition
			$var['page']['list'] = array(
				'id' => "search-list", 
				'title' => "Resultados de la búsqueda", 
				'query-forced' => $search_query, 
				'sorting-field' => "date", 
				'sorting-order' => "DESC", 
				'sorting-extra' => "", 
				'columns' => array(
														array(
																	'field' => "type", 
																	'title' => "Tipo", 
																	'sortable' => true
																	), 
														array(
																	'field' => "id", 
																	'title' => "ID", 
																	'sortable' => true
																	), 
														array(
																	'field' => "date", 
																	'title' => "Fecha", 
																	'sortable' => true
																	), 
														array(
																	'field' => "name", 
																	'title' => "Nombre", 
																	'sortable' => true
																	), 
														array(
																	'field' => "user_id", 
																	'title' => "Autor", 
																	'sortable' => true
																	), 
														array(
																	'field' => "active", 
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
					
					switch ($item['type']) {
						case "category": $type = "<span class=\"label label-primary\"><i class=\"glyphicon glyphicon-tag\"></i> Categoría</span>"; break;
						case "user": $type = "<span class=\"label label-warning\"><i class=\"glyphicon glyphicon-user\"></i> Usuario</span>"; break;
						default: $type = ""; break;
					}
					$name = "<td>" . $item['name'] . "</td>";
					$user = ($item['type'] == "user") ? "&nbsp;" : (($item['user_id'] == 0) ? "-" : "<p>" . $var['page']['aux']['users'][$item['user_id']] . "</p>");
					
					$html .= "<tr id=\"list_item_"  . $item['id'] . "\">
											<td class=\"type\">" . $type . "</td>
											<td>" . $item['id'] . "</td>
											<td class=\"date\">" . date::format($item['date'], "datetimeTextShort") . "</td>
											" . $name . "
											<td class=\"user\">" . $user . "</td>
											<td><span class=\"label label-" . (($item['active'] == 1) ? "success\">Activo" : "warning\">Inactivo") . "</span></td>
											<td class=\"actions\">";
					
					switch ($item['type']) {
						
						case "user":
							if ($obj['user']->data['type'] != "readonly") {
								$html .= "<a href=\"" . $cfg['urls']['app'] . "users/edit/?item=" . $item['id'] . "\" title=\"Editar este usuario\" class=\"btn btn-success btn-sm\"><span class=\"glyphicon glyphicon-edit\"></span></a>
													<a href=\"javascript:core.lists.items.activate('users','" . $item['id'] . "','" . (($item['active'] == 1) ? "0" : "1") . "');\" title=\"" . (($item['active'] == 1) ? "Desactivar" : "Activar") . " este usuario\" class=\"btn btn-" . (($item['active'] == 1) ? "warning" : "primary") . " btn-sm\"><span class=\"glyphicon glyphicon-" . (($item['active'] == 1) ? "pause" : "play") . "\"></span></a>
													<a href=\"javascript:core.lists.items.remove.ask('users','" . $item['id'] . "','" . string::toScript($item['name']) . "');\" title=\"Borrar este usuario\" class=\"btn btn-danger btn-sm\"><span class=\"glyphicon glyphicon-remove\"></span></a>";
							}
						break;
						
					}
					$html .= "	</td>
										</tr>";
					
					return $html;
					
				}
			);
			
			// Keywords for javascript
			$var['javascript-post'] .= "var search_query = \"" . string::toParam($var['keywords']) . "\";\n";
			
			// Render html
			$html = "";
			$html .= adminHtml::moduleHeader();
			$html .= adminHtml::moduleList();
			
		}
		
		// ======================================
		// Render page
		
		adminHtml::renderPage(array(
			'type' => "module", 
			'content' => $html, 
			'id' => $var['page']['module']
		));
		
	break;
	
	
	default:
		core::errorFatal("<p>Access not allowed.</p>");
	break;
	
}

?>