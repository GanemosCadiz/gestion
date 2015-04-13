<?php

// Security check
if (!isset($var) && !isset($var['page'])) { die("Access not allowed."); }

// ==============================================================
// Stats module
// --------------------------------------------------------------

// Pages definitions
switch ($var['page']['section']) {
	
	case "summary":
		// --------------------------------------------------------------
		// Summary
		// --------------------------------------------------------------
		
		// -------------------------------------------
		// Module configuration
		
		// Page navigation
		$var['page']['navigation'] = array(
																				'Inicio' => $cfg['urls']['app'], 
																				'Estadísticas' => $cfg['urls']['app'] . "stats/", 
																				'Resumen' => ""
																				);
		
		// Page header
		$var['page']['header'] = array(
			'text' => "<p>Resumen de las estadísticas de los últimos <strong>" . $cfg['stats']['summary-days'] . "</strong> días actualizadas hasta ayer (" . date::format($var['now']." -1 days", "date") . ").</p>", 
			'actions' => array(
												'title' => "Acciones", 
												'list' => array(
																				array(
																							'type' => "button", 
																							'title' => "Mostrar ayuda para esta sección", 
																							'text' => "Ayuda", 
																							'class' => "primary", 
																							'icon' => "question-sign", 
																							'onclick' => "main.help.show('stats-summary');"
																							)
																				)
												)
		);
		
		// Render html
		$html = "";
		$html .= adminHtml::moduleHeader();
		$html .= adminHtml::moduleStatsCharts(array(
							array(
										'type' => "graph", 
										'code' => "stats-summary-graph-total", 
										'title' => "Actividad total", 
										'description' => "Resumen de la actividad registrada en todos las landings en los últimos " . $cfg['stats']['summary-days'] . " días."
										), 
							array(
										'type' => "graph", 
										'code' => "stats-summary-graph-visits", 
										'title' => "Landings más visitadas", 
										'description' => "Los " . $cfg['stats']['top-items'] . " landings más visitadas en los últimos " . $cfg['stats']['summary-days'] . " días."
										), 
							array(
										'type' => "graph", 
										'code' => "stats-summary-graph-leads", 
										'title' => "Landings con más registros", 
										'description' => "Los " . $cfg['stats']['top-items'] . " landings con más registros en los últimos " . $cfg['stats']['summary-days'] . " días."
										)
						));
		
		adminHtml::renderModule($html);
		
	break;
	
	
	case "sites":
		// --------------------------------------------------------------
		// Sites list
		// --------------------------------------------------------------
		
		// -------------------------------------------
		// Aux lists
		
		$var['page']['aux'] = array(
			'categories' => core::getList("categories"), 
			'templates_form' => core::getList("templates", array('exception-filter' => array("type='form'"))), 
			'templates_page' => core::getList("templates", array('exception-filter' => array("type='page'"))), 
			'users' => core::getList("users")
		);
		
		if (!isset($_GET['item'])) {
			// ...............................
			// List
			
			// -------------------------------------------
			// Module configuration
			
			// Page navigation
			$var['page']['navigation'] = array(
																					'Inicio' => $cfg['urls']['app'], 
																					'Estadísticas' => $cfg['urls']['app'] . "stats/", 
																					'Listado de Landings' => ""
																					);
			
			// Page header
			$var['page']['header'] = array(
				'text' => "<p>Listado de las landings actualmente dadas de alta en la aplicación.</p>", 
				'actions' => array(
													'title' => "Acciones", 
													'list' => array(
																					array(
																								'type' => "button", 
																								'title' => "Mostrar ayuda para esta sección", 
																								'text' => "Ayuda", 
																								'class' => "primary", 
																								'icon' => "question-sign", 
																								'onclick' => "main.help.show('stats-sites-list');"
																								)
																					)
													), 
				'filters' => array(
														'title' => "Filtrar listado", 
														'text' => "Mostrando solamente landings filtradas por:", 
														'list' => array(
																						array(
																									'field' => core::fieldGetName("sites", "type"), 
																									'title' => "Tipo", 
																									'options' => $cfg['options']['site-types']
																								), 
																						array(
																									'field' => core::fieldGetName("sites", "category_id"), 
																									'title' => "Categoría", 
																									'options' => $var['page']['aux']['categories']
																									), 
																						array(
																									'field' => core::fieldGetName("sites", "template_form_id"), 
																									'title' => "Plantilla de formulario", 
																									'options' => $var['page']['aux']['templates_form']
																									), 
																						array(
																									'field' => core::fieldGetName("sites", "template_page_id"), 
																									'title' => "Plantilla de página", 
																									'options' => $var['page']['aux']['templates_page']
																									), 
																						array(
																									'field' => core::fieldGetName("sites", "theme"), 
																									'title' => "Estilo", 
																									'options' => $cfg['options']['site-themes']
																									), 
																						array(
																									'field' => core::fieldGetName("sites", "user_id"), 
																									'title' => "Autor", 
																									'options' => $var['page']['aux']['users']
																									), 
																						array(
																									'field' => core::fieldGetName("sites", "active"), 
																									'title' => "Actividad", 
																									'options' => $cfg['options']['activity']
																									)
																						)
													)
			);
			
			// User query permissions
			$user_permissions = core::userPermissionQuery("sites");
			
			// List definition
			$var['page']['list'] = array(
				'id' => "sites-list", 
				'title' => "Listado de Landings", 
				'table' => core::tableGetName("sites"), 
				'where' => core::fieldGetName("sites", "deleted") . "='0'" . 
										($user_permissions != "" ? " AND " . $user_permissions . " " : ""), 
				'sorting-field' => core::fieldGetName("sites", "id"), 
				'sorting-order' => "DESC", 
				'sorting-extra' => "", 
				'columns' => array(
														array(
																	'field' => core::fieldGetName("sites", "id"), 
																	'title' => "ID", 
																	'sortable' => true
																	), 
														array(
																	'field' => core::fieldGetName("sites", "date_creation"), 
																	'title' => "Alta", 
																	'sortable' => true
																	), 
														array(
																	'field' => core::fieldGetName("sites", "category_id"), 
																	'title' => "Categoría", 
																	'sortable' => true
																	), 
														array(
																	'field' => core::fieldGetName("sites", "name"), 
																	'title' => "Nombre", 
																	'sortable' => true
																	), 
														array(
																	'field' => core::fieldGetName("sites", "stats_visits"), 
																	'title' => "Stats", 
																	'sortable' => true
																	), 
														array(
																	'field' => core::fieldGetName("sites", "user_id"), 
																	'title' => "Autor", 
																	'sortable' => true
																	), 
														array(
																	'field' => core::fieldGetName("sites", "active"), 
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
					
					$id = core::fieldGetVal("sites", $item, "id");
					$category_id = core::fieldGetVal("sites", $item, "category_id");
					$subcategory_id = core::fieldGetVal("sites", $item, "subcategory_id");
					$user_id = core::fieldGetVal("sites", $item, "user_id");
					$name = core::fieldGetVal("sites", $item, "name");
					$description = core::fieldGetVal("sites", $item, "description");
					if ($description != "") {
						$name .= " <i class=\"form-description glyphicon glyphicon-info-sign\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"" . string2input($description) . "\"></i>";
					}
					$active = core::fieldGetVal("sites", $item, "active");
					
					$category = ($category_id == 0) ? "<em>Sin categoría</em>" : (($subcategory_id == 0) ? "<p>" . $var['page']['aux']['categories'][$category_id] . "</p>" : "<p>" . $var['page']['aux']['categories'][$category_id] . "</p><span>" . $var['page']['aux']['subcategories'][$subcategory_id] . "</span>");
					$user = ($user_id == 0) ? "-" : $var['page']['aux']['users'][$user_id];
					
					$html .= "<tr id=\"list_item_"  .$id . "\">
											<td>" . $id . "</td>
											<td class=\"date\">" . date::format(core::fieldGetVal("sites", $item, "date_creation"), "datetimeTextShort") . "</td>
											<td class=\"category\">" . $category . "</td>
											<td class=\"name\">" . $name . "</td>
											<td class=\"number\">
												<p>" . number::format(core::fieldGetVal("sites", $item, "stats_visits")) . " <span>visi.</span></p>
												<p>" . number::format(core::fieldGetVal("sites", $item, "stats_leads")) . " <span>reg.</span></p>
											</td>
											<td class=\"user\"><p>" . $user . "</p></td>
											<td><span class=\"label label-" . (($active == 1) ? "success\">Activo" : "warning\">Inactivo") . "</span></td>
											<td class=\"actions\" style=\"width: 70px;\">
												<a href=\"" . site::getUrl($item) . "\" title=\"Ver landing\" class=\"btn btn-primary btn-sm\" target=\"_blank\"><span class=\"glyphicon glyphicon glyphicon-link\"></span></a>";
					if (core::userCheckModule("stats")) {
						$html .= "	<a href=\"" . $cfg['urls']['app'] . "stats/sites/?item=" . $id . "\" title=\"Ver estadísticas de esta landing\" class=\"btn btn-default btn-sm\"><span class=\"glyphicon glyphicon-stats\"></span> <i class=\"glyphicon glyphicon-chevron-right\"></i></a>";
					}
					$html .= "	</td>
										</tr>";
					
					return $html;
					
				}
			);
			
			// Render html
			$html = "";
			$html .= adminHtml::moduleHeader();
			$html .= adminHtml::moduleList();
			
			adminHtml::renderModule($html);
			
		} else {
			// ...............................
			// Site stats
			
			// Get item
			$var['item'] = core::getItem("sites");
			
			if (!$var['item']) {
				// Item not found
				core::errorFatal("<p>La landing no existe o ha sido borrada.</p><p><a href=\"javascript:aux.history.back();\" title=\"Volver\"><i class=\"glyphicon glyphicon-arrow-left\"></i> Volver</a></p>");
			}
			
			// -------------------------------------------
			// Module configuration
			
			// Page navigation
			$var['page']['navigation'] = array(
																					'Inicio' => $cfg['urls']['app'], 
																					'Estadísticas' => $cfg['urls']['app'] . "stats/", 
																					'Listado de Landings' => $cfg['urls']['app'] . "stats/sites/", 
																					'Estadísticas de Landing' => ""
																					);
			
			$var['page']['header'] = array(
				'text' => "<h4><small>Landing:</small> " . core::fieldGetVal("sites", $var['item'], "name") . "</h4>
										<div class=\"stats-info\">
											<ul>
												<li><p>Categoría</p><span>" . ((core::fieldGetVal("sites", $var['item'], "category_id") == 0) ? "<em>Sin categoría</em>" : $var['page']['aux']['categories'][core::fieldGetVal("sites", $var['item'], "category_id")]) . "</span></li>
												<li><p>Subcategoría</p><span>" . ((core::fieldGetVal("sites", $var['item'], "subcategory_id") == 0) ? "<em>Sin subcategoría</em>" : $var['page']['aux']['subcategories'][core::fieldGetVal("sites", $var['item'], "subcategory_id")]) . "</span></li>
												<li><p>Tipo</p><span>" . $cfg['sitecfg']['types'][core::fieldGetVal("sites", $var['item'], "type")]['title'] . "</span></li>
												<li><p>Alta</p><span>" . date::format(core::fieldGetVal("sites", $var['item'], "date_creation"), "datetimeTextShort") . "</span></li>
												<li><p>Visitas totales</p><span><strong>" . number::format(core::fieldGetVal("sites", $var['item'], "stats_visits")) . "</strong> <small>(hasta ayer " . date::format($var['now']." -1 days", "date") . ")</small></span></li>
												<li><p>Registros totales</p><span><strong>" . number::format(core::fieldGetVal("sites", $var['item'], "stats_leads")) . "</strong> <small>(hasta ayer " . date::format($var['now']." -1 days", "date") . ")</small></span></li>
											</ul>
										</div>
										<div class=\"clearfix\"></div>", 
				'actions' => array(
													'title' => "Acciones", 
													'list' => array(
																					array(
																								'type' => "button", 
																								'title' => "Volver", 
																								'text' => "Volver", 
																								'class' => "info", 
																								'icon' => "chevron-left", 
																								'onclick' => "core.history.back();"
																								), 
																					array(
																								'type' => "link", 
																								'title' => "Ver landing", 
																								'text' => "Ver Landing", 
																								'class' => "default", 
																								'icon' => "link", 
																								'url' => site::getUrl($var['item']), 
																								'target' => "_blank"
																								), 
																					array(
																								'type' => "button", 
																								'title' => "Mostrar ayuda para esta sección", 
																								'text' => "Ayuda", 
																								'class' => "primary", 
																								'icon' => "question-sign", 
																								'onclick' => "main.help.show('stats-site');"
																								)
																					)
													)
			);
			
			// Render html
			$html = "";
			$html .= adminHtml::moduleHeader();
			$html .= adminHtml::moduleStatsCharts(array(
							array(
										'type' => "table", 
										'code' => "stats-site-table-summary", 
										'title' => "Total de actividad", 
										'description' => "Total de la actividad registrada en la landing en las fechas indicadas."
										), 
							array(
										'type' => "graph", 
										'code' => "stats-site-graph-summary", 
										'title' => "Resumen de actividad", 
										'description' => "Resumen de la actividad registrada en la landing en las fechas indicadas."
										)
						));
			
			adminHtml::renderModule($html);
			
		}
		
	break;
	
	
	case "chart":
		// --------------------------------------------------------------
		// Charts
		// --------------------------------------------------------------
		
		if (!isset($_POST['item'])) {
			misc::error404();
		}
		
		$chart = inputClean::clean($_POST['item'], 64);
		$options = isset($_POST['options']) ? $_POST['options'] : array();
		
		$create = stats::load($chart, $options);
		
	break;
	
	
	default:
		core::errorFatal("<p>Access not allowed.</p>");
	break;
	
}

?>