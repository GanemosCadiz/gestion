<?php

// Security check
if (!isset($var) && !isset($var['page'])) { die("Access not allowed."); }

// ==============================================================
// Log module
// --------------------------------------------------------------

// Pages definitions
switch ($var['page']['section']) {
	
	case "":
	default:
		// --------------------------------------------------------------
		// List
		// --------------------------------------------------------------
		
		// -------------------------------------------
		// Module configuration
		
		// Load aux lists
		$var['page']['aux'] = array(
			'users' => core::getList("users", array('excluded-filter' => array(array(core::fieldGetName("users", "type"), "user"))))
		);
		
		// Page navigation
		$var['page']['navigation'] = array(
																				'Inicio' => $cfg['urls']['app'], 
																				'Registro de Actividad' => ""
																				);
		
		// Page header
		$var['page']['header'] = array(
			'text' => "<p>Registro de la actividad detectada en el panel de administración.</p>", 
			'filters' => array(
													'title' => "Filtrar listado", 
													'text' => "Mostrando solamente elementos filtrados por:", 
													'list' => array(
																					array(
																								'field' => "user_id", 
																								'title' => "Usuario", 
																								'options' => $var['page']['aux']['users']
																								)
																					)
												)
		);
		
		// List definition
		$var['page']['list'] = array(
			'id' => "log_admin", 
			'title' => "Listado de Acciones", 
			'width' => "850px", 
			'table' => core::tableGet("log_admin", "name"), 
			'where' => "", 
			'sorting-field' => core::fieldGetName("log_admin", "date"), 
			'sorting-order' => "DESC", 
			'sorting-extra' => "", 
			'columns' => array(
													array(
																'field' => core::fieldGetName("log_admin", "date"), 
																'title' => "Fecha", 
																'width' => "100px", 
																'sortable' => true
																), 
													array(
																'field' => core::fieldGetName("log_admin", "user_id"), 
																'title' => "Usuario", 
																'sortable' => true
																), 
													array(
																'field' => core::fieldGetName("log_admin", "action"), 
																'title' => "Acción", 
																'sortable' => false
																), 
													array(
																'field' => core::fieldGetName("log_admin", "target"), 
																'title' => "Destino", 
																'sortable' => false
																), 
													array(
																'field' => core::fieldGetName("log_admin", "item"), 
																'title' => "Elemento", 
																'sortable' => false
																), 
													array(
																'field' => core::fieldGetName("log_admin", "ip"), 
																'title' => "IP", 
																'sortable' => false
																)
												), 
			'row' => function($item) {
				
				global $cfg, $var;
				
				$html = "";
				
				$user_id = core::fieldGetVal("log_admin", $item, "user_id");
				$user = isset($var['page']['aux']['users'][$user_id]) ? $var['page']['aux']['users'][$user_id] : "-";
				
				$html .= "<tr>
										<td class=\"date\">" . date::format(core::fieldGetVal("log_admin", $item, "date"), "datetimeTextShort") . "</td>
										<td class=\"user\"><p>" . $user . "</p></td>
										<td class=\"small\">" . core::fieldGetVal("log_admin", $item, "action") . "</td>
										<td class=\"small\">" . core::fieldGetVal("log_admin", $item, "target") . "</td>
										<td class=\"small\">" . core::fieldGetVal("log_admin", $item, "item") . "</td>
										<td class=\"small\">" . core::fieldGetVal("log_admin", $item, "ip") . "</td>
									</tr>";
				
				return $html;
				
			}
		);
		
		// Render html
		$html = "";
		$html .= adminHtml::moduleHeader();
		$html .= adminHtml::moduleList();
		
		// ======================================
		// Render page
		
		adminHtml::renderPage(array(
			'type' => "module", 
			'content' => $html, 
			'id' => $var['page']['module']
		));
		
	break;
	
}
