<?php

class adminCoreHtml {
	
	public static function renderPage($options=array()) {
		
		global $cfg, $var, $obj;
		
		$type = isset($options['type']) ? $options['type'] : "normal";
		$content = isset($options['content']) ? $options['content'] : "";
		$id = isset($options['id']) ? $options['id'] : "module";
		
		$html = "";
		
		$html .= adminHtml::head();
		$html .= adminHtml::header();
		
		switch ($type) {
			
			case "normal":
			default:
				$html .= $content;
			break;
			
			case "module":
				$html .= "<section id=\"" . $id . "\" class=\"panel panel-default panel-main\">
										<div class=\"panel-heading\">
					    				<h3 class=\"panel-title\">";
				
				if (isset($var['page']['navigation'])) {
					$t = array();
					foreach ($var['page']['navigation'] as $title => $url) {
						if ($url != "") {
							array_push($t, "<a href=\"" . $url . "\" title=\"" . $title . "\">" . $title . "</a>");
						} else {
							array_push($t, $title);
						}
					}
					$html .= implode(" <i class=\"glyphicon glyphicon-chevron-right\"></i> ", $t);
				} else {
					$html .= $var['page']['module-data']['texts']['title'];
				}
				
				$html .= "		</h3>
			  						</div>
			  						<div class=\"panel-body panel-body-main\">";
				
				$html .= $content;
				
				$html .= "	</div>";
				
				if ($obj['user']->auth) {
					$html .= "<div class=\"totop\"><button type=\"button\" class=\"btn btn-default\" title=\"Volver a arriba\"><i class=\"glyphicon glyphicon-arrow-up\"></i> Volver a arriba</button></div>";
				}
				
				$html .= "</section>";
			break;
			
		}
		
		$html .= adminHtml::footer();
		
		echo $html;
		
	}
	
	public static function head($options=array()) {
		
		global $cfg, $var, $obj;
		
		$html = "";
		
		$html .= "<!DOCTYPE html>
							<!--[if lt IE 7]>      <html class=\"no-js lt-ie9 lt-ie8 lt-ie7\"> <![endif]-->
							<!--[if IE 7]>         <html class=\"no-js lt-ie9 lt-ie8\"> <![endif]-->
							<!--[if IE 8]>         <html class=\"no-js lt-ie9\"> <![endif]-->
							<!--[if gt IE 8]><!--> <html class=\"no-js\"> <!--<![endif]-->
							<head>
								
								<meta charset=\"utf-8\" />
							  <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge,chrome=1\" />
								
							  <title>" . $cfg['app']['title'] . "</title>
							  
							  <meta name=\"description\" content=\"\" />
							  
							  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\" />
								
								<link rel=\"shortcut icon\" type=\"image/png\" href=\"" . $cfg['urls']['app'] . "img/favicon.png\" />
							  
							  <link rel=\"stylesheet\" href=\"" . $cfg['urls']['app'] . "css/vendor/jquery-ui/jquery-ui.min.css\" />
							  <link rel=\"stylesheet\" href=\"" . $cfg['urls']['app'] . "css/vendor/bootstrap.min.css\" />
							  <link rel=\"stylesheet\" href=\"" . $cfg['urls']['app'] . "css/vendor/bootstrap-theme.min.css\" />
								<link rel=\"stylesheet\" href=\"" . $cfg['urls']['app'] . "css/vendor/jquery.cleditor.css\" />
								<link rel=\"stylesheet\" href=\"" . $cfg['urls']['app'] . "css/vendor/jquery.fileupload.css\" />
								<link rel=\"stylesheet\" href=\"" . $cfg['urls']['app'] . "css/vendor/magnific-popup.css\" />
								<link rel=\"stylesheet\" href=\"" . $cfg['urls']['app'] . "css/vendor/jquery.jqplot.css\" />
								<link rel=\"stylesheet\" href=\"" . $cfg['urls']['app'] . "css/styles-base.css\" />";
		
		if (isset($obj['user']) && $obj['user']->auth) {
			$html .= "<link rel=\"stylesheet\" href=\"" . $cfg['urls']['app'] . "css/styles-user.css\" />
								<!--[if lt IE 9]>
								<link rel=\"stylesheet\" href=\"" . $cfg['urls']['app'] . "css/styles-user-ie.css\" />
								<![endif]-->
								<link rel=\"stylesheet\" href=\"" . $cfg['urls']['app'] . "css/styles-custom.css\" />\n";
		}
		
		$html .= "  <script src=\"" . $cfg['urls']['app'] . "js/vendor/modernizr-2.6.2-respond-1.1.0.min.js\"></script>
							  
							</head>";
		
		return $html;
		
	}
	
	public static function header($options=array()) {
		
		global $cfg, $var, $obj;
		
		$html = "";
		
		$html .= "<!--[if lt IE 7 ]>   <body class=\"ie6\">          <![endif]-->
							<!--[if IE 7 ]>      <body class=\"ie7\">          <![endif]-->
							<!--[if IE 8 ]>      <body class=\"ie8\">          <![endif]-->
							<!--[if IE 9 ]>      <body class=\"ie9\">          <![endif]-->
							<!--[if (gt IE 9) ]> <body class=\"modern\">       <![endif]-->
							<!--[!(IE)]><!-->    <body class=\"notIE modern\"> <!--<![endif]-->
							
							<div class=\"container\">
								
								<header>
									<h1>" . $cfg['app']['costumer'] . "</h1>
									<h2>" . $cfg['app']['name'] . "</h2>";
		
		if (isset($obj['user']) && $obj['user']->auth) {
			$html .= "	<p><span>Conectado como:</span>" . $obj['user']->data['name'] . "</p>";
		}
		
		$html .= "		<div class=\"clearfix\"></div>
								</header>";
		
		// --------------------------
		// Menu
							
		// Extra buttons
		$extra_buttons = array();
							
		$html .= adminHtml::menu($extra_buttons);
		
		return $html;
		
	}
	
	public static function footer($options=array()) {
		
		global $cfg, $var, $obj;
		
		$html = "";
		
		$html .= "	<footer>" . $cfg['app']['copyright'] . "</footer>
							</div>
							
							<script src=\"" . $cfg['urls']['app'] . "js/vendor/jquery-1.11.1.min.js\"></script>
							<script src=\"" . $cfg['urls']['app'] . "js/vendor/bootstrap-3.3.1.min.js\"></script>
							<script src=\"" . $cfg['urls']['app'] . "js/vendor/jquery-ui-1.11.2.min.js\"></script>
							<script src=\"" . $cfg['urls']['app'] . "js/vendor/jquery.cleditor-1.4.5.min.js\"></script>
							<script src=\"" . $cfg['urls']['app'] . "js/vendor/jquery.cleditor.xhtml.min.js\"></script>
							<script src=\"" . $cfg['urls']['app'] . "js/vendor/jquery.smooth-scroll.min.js\"></script>
							<script src=\"" . $cfg['urls']['app'] . "js/vendor/jquery.numeric.js\"></script>
							<script src=\"" . $cfg['urls']['app'] . "js/vendor/jquery.magnific-popup.min.js\"></script>
							<script src=\"" . $cfg['urls']['app'] . "js/vendor/plugins.js\"></script>
							<script src=\"" . $cfg['urls']['app'] . "js/?script=app.aux\"></script>";
		
		// --------------------------------------------------------------
		// Personalized scripts
		
		if (!$obj['user']->auth || $var['error']) {
			// Login scripts
			
			$html .= "<script src=\"" . $cfg['urls']['app'] . "js/?script=admin.login\"></script>\n";
			
		}
		
		if (isset($var['page']) && !$var['error']) {
			
			if (isset($obj['user']) && $obj['user']->auth) {
				// Private scripts (only for authorized users)
				
				// Upload script
				$html .= "<script src=\"" . $cfg['urls']['app'] . "js/vendor/jquery.iframe-transport.js\"></script>";
				$html .= "<script src=\"" . $cfg['urls']['app'] . "js/vendor/jquery.fileupload.js\"></script>";
				
				// Main scripts
				$html .= "<script src=\"" . $cfg['urls']['app'] . "js/?script=admin.core\"></script>";
				$html .= "<script src=\"" . $cfg['urls']['app'] . "js/?script=admin.main\"></script>";
				$html .= "<script src=\"" . $cfg['urls']['app'] . "js/admin.main.js\"></script>";
				
				// Stats scripts
				
				$html .= "<!--[if lt IE 9]><script language=\"javascript\" type=\"text/javascript\" src=\"" . $cfg['urls']['app'] . "js/excanvas.min.js\"></script><![endif]-->
									<script src=\"" . $cfg['urls']['app'] . "js/vendor/jquery.jqplot.min.js\"></script>
									<script src=\"" . $cfg['urls']['app'] . "js/vendor/jqplot-plugins/jqplot.categoryAxisRenderer.min.js\"></script>
									<script src=\"" . $cfg['urls']['app'] . "js/vendor/jqplot-plugins/jqplot.pointLabels.min.js\"></script>
									<script src=\"" . $cfg['urls']['app'] . "js/vendor/jqplot-plugins/jqplot.canvasTextRenderer.min.js\"></script>
									<script src=\"" . $cfg['urls']['app'] . "js/vendor/jqplot-plugins/jqplot.barRenderer.min.js\"></script>
									<script src=\"" . $cfg['urls']['app'] . "js/vendor/jqplot-plugins/jqplot.dateAxisRenderer.min.js\"></script>
									<script src=\"" . $cfg['urls']['app'] . "js/vendor/jqplot-plugins/jqplot.highlighter.min.js\"></script>\n";
				
			}
			
		}
		
		// --------------------------------------------------------------
		// Config vars
		
		$html .= "<script>
								core.cfg.paths.root = \"" . $cfg['urls']['app'] . "\";
								core.cfg.app.module = \"" . (isset($var['page']) ? $var['page']['module'] : "") . "\";
								core.cfg.app.section = \"" . (isset($var['page']) ? $var['page']['section'] : "") . "\";
								core.cfg.app.action = \"" . (isset($var['page']) ? $var['page']['action'] : "") . "\";";
		
		// --------------------------------------------------------------
		// Initialization
		
		if (isset($var['page']) && !$var['error']) {
			
			switch ($var['page']['module']) {
				
				case "login":
					$html .= "$(document).ready(function() {
										  login.init();
										});\n";
				break;
				
				default:
					$html .= "$(document).ready(function() {
										  main.init(\"" . $var['page']['module'] . "\", \"" . $var['page']['section'] . "\");
										});\n";
				break;
				
			}
			
		}
		
		$html .= "</script>";
		
		// --------------------------------------------------------------
		// Post-html
		
		$html .= $var['html-post'];
		
		// --------------------------------------------------------------
		// Post-css
		
		$html .= ($var['css-post'] != "") ? "<style type=\"text/css\">" . $var['css-post'] . "</style>" : "";
		
		// --------------------------------------------------------------
		// Post-scripts
		
		$html .= ($var['javascript-post'] != "") ? "<script>" . str_replace("\t", "", $var['javascript-post']) . "</script>" : "";
		
		$html .= "</body></html>";
		
		return $html;
		
	}
	
	public static function menu($extra_buttons=array()) {
		
		global $cfg, $var, $obj;
		
		$html = "";
		
		if (isset($obj['user']) && $obj['user']->auth) {
			
			if (!$var['error']) {
				$title = $var['page']['module-data']['texts']['title'];
			} else {
				$title = "Error";
			}
			
			$module_active = isset($var['page']) ? $var['page']['module'] : "";
			
			// Menu
			$html = "<nav id=\"nav-menu\" class=\"navbar navbar-default\" role=\"navigation\">
							  
							  <div class=\"navbar-header\">
							    <button type=\"button\" class=\"navbar-toggle\" data-toggle=\"collapse\" data-target=\".navbar-ex1-collapse\">
							      <span class=\"sr-only\">Toggle navigation</span>
							      <span class=\"icon-bar\"></span>
							      <span class=\"icon-bar\"></span>
							      <span class=\"icon-bar\"></span>
							    </button>
							    <span class=\"navbar-brand\">" . $title . "</span>
							  </div>
							  
							  <div class=\"collapse navbar-collapse navbar-ex1-collapse\">
							    <ul class=\"nav navbar-nav nav-pills nav-menu-main\">";
			
			foreach ($cfg['modules'] as $module => $module_data) {
				if ($module_data['menu'] 
						&& (empty($module_data['allowed-users']) 
								|| in_array($obj['user']->data['type'], $module_data['allowed-users']))
						&& core::userCheckModule($module)) {
					$html .= "<li" . (($module_active == $module) ? " class=\"active\"" : "") . "><a href=\"" . $cfg['urls']['app'] . $module_data['code'] . "/\" title=\"" . $module_data['texts']['button'] . "\">" . $module_data['texts']['button'] . "</a></li>\n";
				}
			}
			
			$html .= "  </ul>";
			
			$html .= "  <ul class=\"nav navbar-nav navbar-right nav-menu-aux\">";
			
			if ($extra_buttons != "") {
				$html .= "	<li>" . implode("</li><li>", $extra_buttons) . "</li>";
			}
			
			$html .= "		<li><a href=\"" . $cfg['urls']['app'] . "logout/\" class=\"btn logout\" title=\"Salir de la aplicación\">Salir <span class=\"glyphicon glyphicon-remove-circle\"></span></a></li>
							    </ul>
							  </div><!-- /.navbar-collapse -->
							</nav>";
			
			// Submenu
			$html .= "<nav id=\"nav-submenu\" class=\"navbar navbar-default\" role=\"navigation\">
									<div class=\"navbar-header\">
								    <button type=\"button\" class=\"navbar-toggle\" data-toggle=\"collapse\" data-target=\".navbar-ex1-collapse\">
								      <span class=\"sr-only\">Toggle navigation</span>
								      <span class=\"icon-bar\"></span>
								      <span class=\"icon-bar\"></span>
								      <span class=\"icon-bar\"></span>
								    </button>
								  </div>
								  
								  <div class=\"collapse navbar-collapse navbar-ex1-collapse\">";
			
			if (isset($var['page']['module-data']['sections']) && !empty($var['page']['module-data']['sections'])) {
				
				$html .= "  <ul class=\"nav navbar-nav nav-pills nav-menu-submenu\">";
	   		
	   		foreach ($var['page']['module-data']['sections'] as $section => $section_data) {
	   			if ($section_data['menu']) {
	   				$href = (isset($section_data['href'])) ? $section_data['href'] : $cfg['urls']['app'] . $module_active . "/" . $section_data['code'];
	   				$html .= "	<li" . (($var['page']['section'] == $section) ? " class=\"active\"" : "") . "><a href=\"" . $href . "/\" title=\"" . $section_data['texts']['button'] . "\"><i class=\"glyphicon glyphicon-" . $section_data['texts']['icon'] . "\"></i> " . $section_data['texts']['button'] . "</a></li>\n";
	 				}
				}
				
				$html .= "	</ul>";
				
			}
			
			// Search module
			if ($obj['user']->data['type'] != "download") {
				$html .= "	<form action=\"" . $cfg['urls']['app'] . "search/results/\" method=\"get\" class=\"navbar-form navbar-right\" role=\"search\">
						      		<div class=\"input-group\">
						        		<input type=\"text\" name=\"q\" class=\"form-control\" placeholder=\"Buscar...\" value=\"" . (isset($var['keywords']) ? string::toParam($var['keywords']) : "") . "\">
						        		<span class=\"input-group-btn\">
						      				<button type=\"submit\" class=\"btn btn-default\"><i class=\"glyphicon glyphicon-search\"></i></button>
				      					</span>
			      					</div>
						    		</form>";
	 		}
			
			$html .= "	</div><!-- /.navbar-collapse -->
								</nav>";
			
		}
		
		return $html;
		
	}
	
	public static function renderModule($content, $id="edit") {
		
		global $cfg, $var, $obj;
		
		// Include Html
		include_once("lib/html.head.php");
		include_once("lib/html.header.php");
		
		echo "<section id=\"" . $id . "\" class=\"panel panel-default panel-main\">
						<div class=\"panel-heading\">
	    				<h3 class=\"panel-title\">";
		
		if (isset($var['page']['navigation'])) {
			$t = array();
			foreach ($var['page']['navigation'] as $title => $url) {
				if ($url != "") {
					array_push($t, "<a href=\"" . $url . "\" title=\"" . $title . "\">" . $title . "</a>");
				} else {
					array_push($t, $title);
				}
			}
			echo implode(" <i class=\"glyphicon glyphicon-chevron-right\"></i> ", $t);
		} else {
			echo $var['page']['module-data']['texts']['title'];
		}
		
		echo "		</h3>
	  				</div>
	  				<div class=\"panel-body panel-body-main\">";
		
		echo $content;
		
		echo "	</div>";
		
		if ($obj['user']->auth) {
			echo "<div class=\"totop\"><button type=\"button\" class=\"btn btn-default\" title=\"Volver a arriba\"><i class=\"glyphicon glyphicon-arrow-up\"></i> Volver a arriba</button></div>";
		}
		
		echo "</section>";
		
		include_once("lib/html.footer.php");
		
	}
	
	public static function moduleHeader($action_msg="") {
		
		global $cfg, $var, $obj;
		
		$html = "";
		
		if (isset($var['page']['header'])) {
			
			$dates_show = ($var['page']['module'] == "downloads" && $var['page']['section'] == "custom") 
										|| ($var['page']['module'] == "stats" && $var['page']['section'] == "sites" && isset($_GET['item']));
			
			if (isset($var['page']['header']['filters']) 
					|| $dates_show) {
				$html .= "<div class=\"list-header\">";
			}
			
			// Action message
			if ($action_msg != "") {
				$html .= "<div class=\"core-msg alert alert-success fade in\">
										<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Cerrar\"><span aria-hidden=\"true\">&times;</span></button>
										" . $action_msg . "
									</div>";
			}
			
			// ---------------------------------
			// Info
			if (isset($var['page']['header']['text'])) {
				
				$html .= "<div class=\"info well" . ($dates_show ? " info-narrow" : "") . "\">";
				
				$html .= $var['page']['header']['text'];
				
				// Actions
				if (isset($var['page']['header']['actions'])) {
					
					$html .= "<div class=\"well-actions\">
											<p>" . $var['page']['header']['actions']['title'] . "</p>
											<ul>";
					
					foreach ($var['page']['header']['actions']['list'] as $n => $action) {
						
						switch ($action['type']) {
							
							case "link":
								$html .= "<li><a href=\"" . $action['url'] . "\" class=\"btn btn-" . $action['class'] . " btn-sm\" title=\"" . $action['title'] . "\"" . ((isset($action['action'])) ? " data-action=\"" . $action['action'] . "\"" : "") . ((isset($action['target'])) ? " target=\"" . $action['target'] . "\"" : "") . "><span class=\"glyphicon glyphicon-" . $action['icon'] . "\"></span> " . $action['text'] . "</a></li>\n";
							break;
							
							case "button":
								$html .= "<li><button type=\"button\" class=\"btn btn-" . $action['class'] . " btn-sm\" title=\"" . $action['title'] . "\"" . ((isset($action['onclick'])) ? " onclick=\"" . $action['onclick'] . "\"" : "") . ((isset($action['action'])) ? " data-action=\"" . $action['action'] . "\"" : "") . "><span class=\"glyphicon glyphicon-" . $action['icon'] . "\"></span> " . $action['text'] . "</button></li>\n";
							break;
							
							case "search":
								$html .= "<li>
														<div class=\"search-action\">
															<form action=\"" . $cfg['urls']['app'] . "search/\" method=\"get\" role=\"search\">
											      		<div class=\"input-group\">
											        		<input type=\"text\" name=\"q\" class=\"form-control input-sm\" placeholder=\"Buscar...\" value=\"" . (isset($var['keywords']) ? string::toParam($var['keywords']) : "") . "\">
											        		<span class=\"input-group-btn\">
											      				<button type=\"submit\" class=\"btn btn-primary btn-sm\"><i class=\"glyphicon glyphicon-search\"></i></button>
									      					</span>
								      					</div>
											    		</form>
														</div>
													</li>";
							break;
							
						}
						
					}
					
					$html .= "	</ul>
										</div>";
					
				}
				
				$html .= "</div>";
				
			}
			
			// ---------------------------------
			// Filters
			if (isset($var['page']['header']['filters'])) {
				
				// Get filters
				$var['page']['list']['filters'] = core::getListFilters();
				
				$html .= "<div class=\"filters panel panel-default\">
										<div class=\"panel-heading\"><h3 class=\"panel-title\">" . $var['page']['header']['filters']['title'] . "</h3></div>
										<div class=\"panel-body\">";
				
				if (isset($var['page']['list']['filters']) && $var['page']['list']['filters']['id'] == $var['page']['list']['id'] && !empty($var['page']['list']['filters']['filters'])) {
					$html .= "	<p>" . $var['page']['header']['filters']['text'] . "</p>
											<ul class=\"filters-list\">";
					foreach ($var['page']['list']['filters']['filters'] as $field => $filter) {
						$html .= "<li><a href=\"javascript:core.lists.filters.remove('" . $field . "');\" title=\"Eliminar este filtro\"><strong>" . $filter['title'] . ":</strong> " . $filter['value-title'] . "<i class=\"glyphicon glyphicon-remove-circle\"></i></a></li>";
					}
					$html .= "	</ul>";
				}
				
				$html .= "		<button type=\"button\" class=\"btn btn-info center-block filters-add\"><span class=\"glyphicon glyphicon-plus-sign\"></span> Añadir filtro</button>
										</div>";
				
				$html .= "	<div class=\"filters-menu\">";
				
				foreach ($var['page']['header']['filters']['list'] as $n => $filter) {
					$html .= "	<div class=\"core-accordion-item\">
												<h4 class=\"core-accordion-switch\" data-target=\"#accordion-filters-" . $filter['field'] . "\" data-parent=\"#accordion-filters\"><i class=\"glyphicon glyphicon-chevron-right\"></i> " . $filter['title'] . "...</h4>
												<ul id=\"accordion-filters-" . $filter['field'] . "\" class=\"core-accordion-collapse\">";
					foreach ($filter['options'] as $k => $v) {
						$html .= "		<li><a href=\"javascript:core.lists.filters.add('" . $filter['field'] . "','" . $k . "');\" title=\"" . $v . "\"><i class=\"glyphicon glyphicon-plus-sign\"></i> " . $v . "</a></li>";
					}
					$html .= "		</ul>
											</div>";
				}
				$html .= "	</div>
									</div>";
				
			}
			
			// ---------------------------------
			// Dates
			if ($dates_show) {
				
				if ($var['page']['module'] == "stats" && $var['page']['section'] == "sites" && isset($_GET['item'])) {
					// Stats
					
					$title = "Fechas que mostrar";
					$date_type = "date";
					$date_min = date::format(core::fieldGetVal("sites", $var['item'], "date_creation"));
					$date_max = date::format($var['now']);
					$date_start = date::format("first day of this month");
					$date_end = date::format("today");
					$button = "Actualizar gráficas";
					
				} else if ($var['page']['module'] == "stats" && $var['page']['section'] == "summary") {
					// Stats summary
					
					$title = "Fechas que mostrar";
					$date_type = "range";
					$date_min = date::format("3 months ago");
					$date_max = date::format("yesterday");
					$date_start = date::format("30 days ago");
					$date_end = date::format("yesterday");
					$button = "Actualizar gráficas";
					
				} else if ($var['page']['module'] == "downloads" && $var['page']['section'] == "custom") {
					// Custom downloads
					
					$title = "Fechas que incluir";
					$date_type = "date";
					$date_min = date::format($cfg['stats']['app-start']);
					$date_max = date::format($var['now']);
					$date_start = date::format("first day of this month");
					$date_end = date::format("today");
					$button = "Actualizar fechas";
					
				}
				
				$html .= "<div class=\"dates-panel\">
										<h5>" . $title . "</h5>
										
										<div class=\"date\">
											<div class=\"years btn-group\">";
				
				for ($y=$cfg['stats']['year-start']; $y<=date("Y"); $y++) {
					$html .= "  	<button type=\"button\" class=\"btn btn-default btn-lg\" data-item=\"" . $y . "\">" . $y . "</button>";
				}
				
				$html .= "		</div>
											<div class=\"months btn-group\">";
				for ($m=1; $m<=6; $m++) {
					$html .= "  	<button type=\"button\" class=\"btn btn-default\" data-item=\"" . $m . "\">" . substr($cfg['texts']['months'][$m-1], 0, 3) . "</button>";
				}
				$html .= "		</div>
											<div class=\"months btn-group\">";
				for ($m=7; $m<=12; $m++) {
					$html .= "  	<button type=\"button\" class=\"btn btn-default\" data-item=\"" . $m . "\">" . substr($cfg['texts']['months'][$m-1], 0, 3) . "</button>";
				}
				$html .= "		</div>
										</div>
										
										<div class=\"range\">
											<div class=\"selector\">
												<p>Desde el día:</p>
												<div class=\"row\">
													<div class=\"col-lg-12\">
												    <div class=\"input-group\">
												    	<input type=\"text\" name=\"selector_start\" class=\"form-control input-lg\">
												      <span class=\"input-group-btn\">
												        <button class=\"btn btn-default btn-lg\" type=\"button\"><i class=\"glyphicon glyphicon-calendar\"></i></button>
												      </span>
												    </div>
												  </div>
											  </div>
											</div>
											<div class=\"selector\">
												<p>Hasta el día:</p>
												<div class=\"row\">
													<div class=\"col-lg-12\">
												    <div class=\"input-group\">
												    	<input type=\"text\" name=\"selector_end\" class=\"form-control input-lg\">
												      <span class=\"input-group-btn\">
												        <button class=\"btn btn-default btn-lg\" type=\"button\"><i class=\"glyphicon glyphicon-calendar\"></i></button>
												      </span>
												    </div>
												  </div>
											  </div>
											</div>
											<div class=\"clearfix\"></div>
										</div>
										
										<div class=\"action\">
											<button type=\"button\" class=\"switch btn btn-default\"><i class=\"glyphicon glyphicon-calendar\"></i> Elegir días</button>
											<button type=\"button\" class=\"refresh btn btn-info\"><i class=\"glyphicon glyphicon-refresh\"></i> " . $button . "</button>
											<div class=\"clearfix\"></div>
										</div>
										
									</div>";
									
				$var['javascript-post'] .= "
										core.stats.dates.type = '" . $date_type . "', 
										core.stats.dates.year = " . date("Y") . ", 
										core.stats.dates.month = " . date("n") . ", 
										core.stats.dates.start = '" . $date_start . "', 
										core.stats.dates.end = '" . $date_end . "', 
										core.stats.dates.min = '" . $date_min . "', 
										core.stats.dates.max = '" . $date_max . "';";
				
			}
			
			if (isset($var['page']['header']['filters']) 
					|| $dates_show) {
				$html .= "</div><div class=\"clearfix\"></div>";
			}
			
		}
		
		return $html;
		
	}
	
	public static function moduleList() {
		
		global $cfg, $var, $obj;
		
		$html = "";
		
		if (isset($var['page']['list'])) {
			
			// Get sorting criteria
			$var['page']['list']['sorting'] = core::getListCriteria();
			// Get page number
			$var['page']['list']['page-num'] = core::getListPageNum();
			
			// Header
			$html .= "<div id=\"" . $var['page']['list']['id'] . "\" class=\"tablelist panel panel-info table-responsive\"" . (isset($var['page']['list']['width']) ? " style=\"width: " . $var['page']['list']['width'] . ";\"" : "") . ">
									<div class=\"panel-heading\"><h3 class=\"panel-title\">" . $var['page']['list']['title'] . "</h3></div>
									<table class=\"table table-striped table-bordered\">
										<thead>
											<tr>";
			
			// Columns
			foreach ($var['page']['list']['columns'] as $n => $column) {
				$width = isset($column['width']) ? " style=\"width: " . $column['width'] . ";\"" : "";
				if (!$column['sortable']) {
					$html .= "<th" . $width . "><p>" . $column['title'] . "<p></th>";
				} else {
					if ($column['field'] == $var['page']['list']['sorting']['field']) {
						$class = "sortable sortable-main";
						if (string::uppercase($var['page']['list']['sorting']['order']) == "ASC") {
							$icon = "<i class=\"glyphicon glyphicon-chevron-up\"></i>";
							$order = "desc";
						} else {
							$icon = "<i class=\"glyphicon glyphicon-chevron-down\"></i>";
							$order = "asc";
						}
					} else {
						$class = "sortable";
						$icon = "";
						$order = "asc";
					}
					$html .= "<th class=\"" . $class . "\" onclick=\"core.lists.order('" . $column['field'] . "|" . $order . "');\"" . $width . "><p>" . $column['title'] . $icon . "</p></th>";
				}
			}
			
			$html .= "			</tr>
										</thead>
										<tbody>";
			
			// Create list of items
			$var['page']['list']['items'] = new pagination(array(
				'items_page' => $cfg['lists']['items-page'], 
				'db_connection' => $obj['db']->connection
			));
			// Get filters for where clause
			$wheres = (isset($var['page']['list']['where'])) ? array($var['page']['list']['where']) : array();
			if (isset($var['page']['list']['filters']) && $var['page']['list']['filters']['id'] == $var['page']['list']['id'] && !empty($var['page']['list']['filters']['filters'])) {
				foreach ($var['page']['list']['filters']['filters'] as $field => $data) {
					array_push($wheres, $field . "='" . $data['value'] . "'");
				}
			}
			// Query
			$var['page']['list']['items']->query_forced = (isset($var['page']['list']['query-forced'])) ? $var['page']['list']['query-forced'] : "";
			$var['page']['list']['items']->query = array(
				'table' => isset($var['page']['list']['table']) ? $var['page']['list']['table'] : "", 
				'fields' => isset($var['page']['list']['fields']) ? $var['page']['list']['fields'] : array(), 
				'where' => implode(" AND ", $wheres), 
				'sorting-field' => $var['page']['list']['sorting']['field'], 
				'sorting-order' => $var['page']['list']['sorting']['order'], 
				'sorting-extra' => isset($var['page']['list']['sorting-extra']) ? $var['page']['list']['sorting-extra'] : "", 
				'limit' => isset($var['page']['list']['limit']) ? $var['page']['list']['limit'] : ""
			);
			$var['page']['list']['items']->page = $var['page']['list']['page-num'];
			$var['page']['list']['items']->go();
			
			if ($var['page']['list']['items']->error_msg != "") {
				
				$html .= "<tr><td colspan=\"" . count($var['page']['list']['columns']) . "\" class=\"empty\">Se produjo un error: " . $var['page']['list']['items']->error_msg . ".</td></tr>";
				
			} else if ($var['page']['list']['items']->items_total == 0) {
				
				$html .= "<tr><td colspan=\"" . count($var['page']['list']['columns']) . "\" class=\"empty\">No hay elementos que se ajusten a los criterios especificados.</td></tr>";
				
			} else {
				
				foreach ($var['page']['list']['items']->items as $n => $item) {
					
					// Output every row
					$html .= $var['page']['list']['row']($item);
					
				}
				
			}
			
			$html .= "		</tbody>
									</table>
								</div>";
			
			// Styling for sorting column
			$html .= "<style type=\"text/css\">
									.tablelist tr td:nth-child(" . ($var['page']['list']['sorting-fieldnum'] + 1) . "), 
									.table-striped > tbody > tr:nth-child(" . ($var['page']['list']['sorting-fieldnum'] + 1) . "n+1) > td:nth-child(" . ($var['page']['list']['sorting-fieldnum'] + 1) . ") {
										background: rgba(66, 139, 202, 0.1);
									}
								</style>";
			
			
			$html .= adminHtml::pagination($var['page']['list']['items']);
			
		}
		
		return $html;
		
	}
	
	public static function moduleForm($mode) {
		
		global $cfg, $var, $obj;
		
		$html = "";
		
		$table = $var['page']['form']['table'];
		
		if (isset($var['page']) && isset($var['page']['form']) && isset($var['item'])) {
			
			$url_next = (isset($var['page']['form']['next'])) ? $var['page']['form']['next'] : "";
			
			$html = "<div class=\"panel panel-primary form form-" . $table . "\"" . (isset($var['page']['form']['width']) ? " style=\"width: " . $var['page']['form']['width'] . ";\"" : "") . ">
									<div class=\"panel-heading\"><h3 class=\"panel-title\">" . $var['page']['form']['title'] . "</h3></div>
									<div class=\"panel-body\">
										
										<div class=\"back\">
											<a href=\"javascript:aux.history.back(-1);\" title=\"Volver\" class=\"btn btn-warning\"><i class=\"glyphicon glyphicon-arrow-left\"></i> Volver</a>
										</div>
										
										<div class=\"msg\">
											Los campos marcados con <em>*</em> son obligatorios.
										</div>";
			
			$html .= adminHtml::form($table, $mode, $var['item']);
			
			// Form action
			$html .= "	<div class=\"form-action\">
										<div class=\"form-action-forms\">";
			
			if (!isset($var['page']['form']['next'])) {
				// Single button
				
				$html .= "		<div class=\"finish\">
												<button type=\"button\" data-form=\"" . $table . "\" data-destination=\"\" class=\"btn btn-success btn-lg " . ($mode != "config" ? "form-save" : "form-config") . "\" title=\"Guardar cambios\"><i class=\"glyphicon glyphicon-check\"></i> <span>Guardar cambios</span></button>
											</div>";
				
			} else {
				// Button for multiple steps
				
				if ($var['page']['section'] == "new") {
					// New site
					if ($url_next != "") {
						$html .= "<div class=\"finish\">
												<button type=\"button\" data-form=\"" . $table . "\" data-destination=\"" . $url_next . "\" class=\"btn btn-success btn-lg form-save\" title=\"Guardar cambios y continuar al siguiente paso\"><span>Guardar y Continuar</span> <i class=\"glyphicon glyphicon-arrow-right\"></i></button>
											</div>";
					} else {
						$html .= "<div class=\"finish\">
												<button type=\"button\" data-form=\"" . $table . "\" data-destination=\"\" class=\"btn btn-success btn-lg form-save\" title=\"Guardar cambios y finalizar la edición\"><i class=\"glyphicon glyphicon-check\"></i> <span>Guardar y Finalizar</span></button>
											</div>";
					}
				} else {
					// Edit site
					$html .= "	<div class=\"finish\">
												<button type=\"button\" data-form=\"" . $table . "\" data-destination=\"\" class=\"btn btn-success btn-lg form-save\" title=\"Guardar cambios y finalizar la edición\"><i class=\"glyphicon glyphicon-check\"></i> <span>Guardar y Finalizar</span></button>
											</div>";
					if ($url_next != "") {
						$html .= "<div class=\"next\">
												<button type=\"button\" data-form=\"" . $table . "\" data-destination=\"" . $url_next . "\" class=\"btn btn-info form-save\" title=\"Guardar cambios y continuar al siguiente paso\"><span>Guardar y Continuar</span> <i class=\"glyphicon glyphicon-arrow-right\"></i></button>
											</div>";
					}
				}
				
			}
			
			$html .= "		</div>
									</div>
									
								</div>
							</div>";
			
		}
		
		return $html;
		
	}
	
	public static function moduleOK() {
		
		global $cfg, $var, $obj;
		
		$html = "";
		
		if (isset($var['page']) && isset($var['page']['ok'])) {
			
			$html .= "<div class=\"panel panel-success panel-ok\">
								  <div class=\"panel-heading\">
								    <h3 class=\"panel-title\">" . $var['page']['ok']['title'] . "</h3>
								  </div>
								  <div class=\"panel-body\">
								    " . $var['page']['ok']['content'] . "
								  </div>
								</div>";
			
		}
		
		return $html;
		
	}
	
	public static function moduleStatsCharts($charts) {
		
		global $cfg, $var, $obj;
		
		$html = "";
		
		$var['javascript-post'] .= "
			core.stats.load_stack = [], 
			core.stats.item = " . ((isset($var['item'])) ? core::fieldGetVal("sites", $var['item'], "id") : "0") . ";
		";
		
		foreach ($charts as $n => $chart) {
			
			$html .= adminHtml::moduleStatsChart($chart);
			
		}
		
		return $html;
		
	}
	
	public static function moduleDownloads($list) {
		
		global $cfg, $var, $obj;
		
		$html = "";
		
		foreach ($list as $code => $item) {
			
			$var['javascript-post'] .= "core.downloads.list.push('" . $code . "');";
			
			$html .= "<div class=\"panel panel-primary " . $code . "\">
									<div class=\"panel-heading\"><h3 class=\"panel-title\">" . $item['title'] . "</h3></div>
									<div class=\"panel-body\">
										
										<div class=\"complete\">
											<div class=\"panel panel-default\">
												<div class=\"panel-heading\"><strong>Descargar listado completo</strong></div>
												<div class=\"panel-body\">
													<button type=\"button\" data-type=\"" . $code . "\" title=\"Descargar el listado completo de inscripciones\" class=\"btn btn-success btn-lg\"><span class=\"glyphicon glyphicon-circle-arrow-down\"></span> Descargar todo</button>
													<em>La descarga del listado completo es un proceso complejo que carga al servidor. No abuse de ella.</em>
												</div>
											</div>
										</div>";
			
			if (isset($item['filters']) && !empty($item['filters'])) {
				
				$html .= "	<div class=\"filters\">
											
											<div class=\"panel panel-default menu\">
											  <div class=\"panel-heading\"><strong>Filtros de descarga</strong></div>
											  <div class=\"panel-body\">
													<div class=\"panel-group filter-item\" id=\"accordion_" . $code . "\">";
				
				foreach ($item['filters'] as $n => $filter) {
					
					switch ($filter['type']) {
						
						// ----------------------------------------
						case "list":
							$html .= "		<div class=\"panel panel-default accordion\">
															<div class=\"panel-heading\" data-target=\"#filter-" . $code . "-" . $filter['field'] . "\" data-parent=\"#accordion_" . $code . "\">
																<h3 class=\"core-accordion-switch\"><i class=\"glyphicon glyphicon-chevron-right\"></i> <em>" . $filter['title'] . "</em></h3>
															</div>
															<div id=\"filter-" . $code . "-" . $filter['field'] . "\" class=\"core-accordion-collapse\">
																<div class=\"panel-body\">
																	<ul>";
							$options = is_array($filter['options']) ? $filter['options'] : $var['page']['aux'][$filter['options']];
							foreach ($options as $k => $v) {
								$html .= "					<li><input 	type=\"checkbox\" 
																								name=\"filter_" . $code . "_" . $filter['field'] . "\" 
																								id=\"filter_" . $code . "_" . $filter['field'] . "_" . $k . "\" 
																								value=\"" . $k . "\" 
																								data-filter=\"" . $filter['field'] . "\" 
																								data-ref=\"" . $code . "\" /><label 	for=\"filter_" . $code . "_" . $filter['field'] . "_" . $k . "\"> " . $v . "</label></li>";
							}
							$html .= "					</ul>
																</div>
															</div>
														</div>";
						break;
						
						// ----------------------------------------
						case "date":
							$html .= "		<div class=\"panel panel-default accordion\">
															<div class=\"panel-heading\" data-target=\"#filter-" . $code . "-" . $filter['field'] . "\" data-parent=\"#accordion_" . $code . "\">
																<h3 class=\"core-accordion-switch\"><i class=\"glyphicon glyphicon-chevron-right\"></i> <em>" . $filter['title'] . "</em></h3>
															</div>
															<div id=\"filter-" . $code . "-" . $filter['field'] . "\" class=\"core-accordion-collapse\">
																<div class=\"panel-body\">
																	<ul>
																		<li><label for=\"filter_" . $code . "_" . $filter['field'] . "_from\" class=\"label-text\">Desde el día</label><input type=\"text\" name=\"filter_" . $code . "_" . $filter['field'] . "_from\" id=\"filter_" . $code . "_" . $filter['field'] . "_from\" class=\"form-control form-date\" data-filter=\"" . $filter['field'] . "\" data-prefix=\"from\" data-ref=\"" . $code . "\" /></li>
																		<li><label for=\"filter_" . $code . "_" . $filter['field'] . "_to\" class=\"label-text\">Hasta el día</label><input type=\"text\" name=\"filter_" . $code . "_" . $filter['field'] . "_to\" id=\"filter_" . $code . "_" . $filter['field'] . "_to\" class=\"form-control form-date\" data-filter=\"" . $filter['field'] . "\" data-prefix=\"to\" data-ref=\"" . $code . "\" /></li>
																	</ul>
																</div>
															</div>
														</div>";
						break;
						
					}
					
				}
				
				$html .= "				</div>
					    
											  </div>
											</div>
											
										</div>
										
										<div class=\"panel panel-default list\">
										  <div class=\"panel-heading\"><strong>Descarga con filtros</strong></div>
										  <div class=\"panel-body\">
										  	
										  	<div class=\"load\"></div>
										  	
										  	<div class=\"action\">
													<button type=\"button\" title=\"Descargar el listado filtrado\" class=\"btn btn-success\" data-type=\"" . $code . "\"><span class=\"glyphicon glyphicon-arrow-down\"></span> Descargar</button>
												</div>
										  	
									  	</div>
							  		</div>";
 			}
 			
 			$html .= "	</div>
								</div>";
			
		}
		
		$html .= "<form name=\"download\" action=\"" . $cfg['urls']['app'] . "action/\" method=\"post\" target=\"_blank\">
								<input type=\"hidden\" name=\"action\" value=\"download\" />
								<input type=\"hidden\" name=\"type\" value=\"\" />
								<input type=\"hidden\" name=\"filters\" value=\"\" />
							</form>";
		
		return $html;
		
	}
	
	public static function moduleStatsChart($chart) {
		
		global $cfg, $var, $obj;
		
		$html = "";
		
		$html .= "<div class=\"chart\">
								<div class=\"info\">
									<h4>" . $chart['title'] . "</h4>
									<p>" . $chart['description'] . "</p>
								</div>";
		
		if ($chart['type'] == "graph") {
			
			$html .= "<div class=\"menu\">
									<p>Leyenda</p>
									<ul></ul>
									<button type=\"button\" class=\"btn btn-sm btn-info stats-save\" data-item=\"" . $chart['code'] . "\" title=\"Guardar gráfica como imagen\"><i class=\"glyphicon glyphicon-save\"></i></button>
									<div class=\"clearfix\"></div>
								</div>";
			
		}
		
		$html .= "	<div class=\"wrapper\">
									<div class=\"content\">
										<div class=\"loading\">Cargando...</div>
										<div id=\"" . $chart['code'] . "\" class=\"data\"></div>
									</div>
								</div>
							</div>
							";
		
		$var['javascript-post'] .= "core.stats.load_stack.push('" . substr($chart['code'], 6) . "');\n";
		
		return $html;
		
	}
	
	public static function form($table, $mode, $item=array()) {
		
		global $cfg, $var, $obj;
		
		$key_id = core::tableGet($table, "id");
		
		$fields = isset($var['page']['form']['fields']) ? $var['page']['form']['fields'] : $table;
		
		$html = "";
		
		$html .= "<form data-form=\"" . $table . "\" id=\"form-" . $table . "\" role=\"form\" action=\"\" method=\"post\" class=\"form-horizontal\">
							
								<input type=\"hidden\" name=\"element\" value=\"" . $table . "\" />
								<input type=\"hidden\" name=\"fields\" value=\"" . $fields . "\" />
								<input type=\"hidden\" name=\"mode\" value=\"" . $mode . "\" />
								<input type=\"hidden\" name=\"item\" value=\"" . (($mode == "edit") ? $var['item'][$key_id] : "0") . "\" />
								<input type=\"hidden\" name=\"destination\" value=\"\" />
								";
		
		// Form fields
		foreach ($cfg['fields'][$fields] as $field_name => $field_data) {
			
			$html .= adminHtml::formField($fields, $field_name, $field_data, $var['item']);
			
		}
		
		$html .= "</form>";
		
		// Javascript fields array
		$var['javascript-post'] .= "core.forms.fields['" . $table . "'] = [];\n";
		
		foreach ($cfg['fields'][$fields] as $field_name => $field_data) {
			
			if (!in_array($field_data['type'], $cfg['fields-cfg']['ignored-js'])) {
				
				$t = array();
				
				foreach ($cfg['fields-cfg']['public'] as $n => $f) {
					
					if (isset($field_data[$f])) {
						$t[$f] = (is_callable($field_data[$f]) ? $field_data[$f]() : $field_data[$f]);
					}
					
				}
				
				$var['javascript-post'] .= "core.forms.fields['" . $table . "']['" . $field_name . "'] = " . misc::jsonEncode($t) . ";\n";
				
			}
			
		}
		
		// Javascript events
		foreach ($cfg['fields'][$fields] as $field_name => $field_data) {
		
			if (isset($field_data['events']) && !empty($field_data['events'])) {
				
				foreach ($field_data['events'] as $event => $function) {
					
					if ($event != "load") {
						
						$var['javascript-post'] .= "$(\"#f_" . $field_name . "\").bind(\"" . $event . "\", function() {" . $function . "}).trigger(\"" . $event . "\");\n";
						
					} else {
						
						$var['javascript-post'] .= $function;
						
					}
				}
				
			}
			
		}
		
		return $html;
		
	}
	
	public static function formField($table, $name, $data, $item) {
		
		global $cfg, $var, $obj;
		
		$data['type'] = is_callable($data['type']) ? $data['type']() : $data['type'];
		
		if ($data['type'] == "internal" || $data['type'] == "post-process") {
			return "";
		}
		
		// Call function attributes
		
		if (isset($data['required'])) {
			$required = is_callable($data['required']) ? $data['required']() : $data['required'];
		} else {
			$required = false;
		}
		
		if (isset($data['legend'])) {
			$legend = is_callable($data['legend']) ? $data['legend']() : $data['legend'];
		} else {
			$legend = "";
		}
		
		if (isset($data['options'])) {
			$options = is_callable($data['options']) ? $data['options']() : $data['options'];
		} else {
			$options = array();
		}
		
		// Blocks ---------------------------
		if ($data['type'] == "block-open") {
			return "<div id=\"f_" . $name . "_container\" class=\"panel panel-default form-block\"><div class=\"panel-heading\">" . $data['label'] . "</div><div class=\"panel-body\">\n";
		}
		if ($data['type'] == "block-close") {
			return "</div></div>\n";
		}
		
		// Value
		$value = ($data['type'] == "password" || isset($data['nodb'])) ? "" : core::fieldGetVal($table, $item, $name);
		
		// Default value
		if ($value == "" && isset($data['default'])) {
			$value = is_callable($data['default']) ? $data['default']($item) : $data['default'];
		}
		// Pre-process
		if ($value != "" && isset($data['pre-process'])) {
			$value = $data['pre-process']($value);
		}
		
		// Hidden fields ---------------------------
		if ($data['type'] == "hidden") {
			return "<input type=\"hidden\" name=\"" . $name . "\" id=\"f_" . $name . "\" value=\"" . $value . "\" data-previous=\"" . $value . "\" />\n";
		}
		
		// Html fields ---------------------------
		if ($data['type'] == "html") {
			return is_callable($data['html']) ? $data['html']($item) : $data['html'];
		}
		
		// Form group
		$html = "<div id=\"f_" . $name . "_container\" class=\"form-group form-item\">
						    <label for=\"f_" . $name . "\" class=\"col-lg-4 control-label form-label\">" . $data['label'] . ($required ? "<em> *</em>" : "") . "</label>
						    <div class=\"col-lg-8\">";
		
		switch ($data['type']) {
			
			// ---------------------------
			case "uneditable":
				$html .= "<div class=\"form-control-static\"" . (isset($data['style']) ? " style=\"" . $data['style'] . "\"" : "") . ">" . ($value == "" ? "-" : $value) . "</div>";
			break;
			
			// ---------------------------
			case "text":
			case "password":
			case "number":
			case "email":
			case "tel":
				$html .= "<input type=\"" . $data['type'] . "\" name=\"" . $name . "\" id=\"f_" . $name . "\" class=\"form-control\" placeholder=\"" . $data['label'] . "\"" . (isset($data['length']) ? " maxlength=\"" . $data['length'] . "\"" : "") . " value=\"" . string::toParam($value) . "\" data-previous=\"" . string::toParam($value) . "\"" . (isset($data['style']) ? " style=\"" . $data['style'] . "\"" : "") . " />";
			break;
			
			// ---------------------------
			case "file":
				$html .= "<input type=\"file\" name=\"" . $name . "\" id=\"f_" . $name . "\" class=\"form-control\" />";
			break;
			
			// ---------------------------
			case "textarea":
				$html .= "<textarea name=\"" . $name . "\" id=\"f_" . $name . "\" class=\"form-control\"" . (isset($data['length']) ? " maxlength=\"" . $data['length'] . "\"" : "") . " data-previous=\"" . string::toParam($value) . "\"" . (isset($data['style']) ? " style=\"" . $data['style'] . "\"" : "") . ">" . $value . "</textarea>";
			break;
			
			// ---------------------------
			case "richtext":
				$html .= "<textarea name=\"" . $name . "_rt\" id=\"f_" . $name . "_rt\" class=\"form-control\"" . (isset($data['length']) ? " maxlength=\"" . $data['length'] . "\"" : "") . " data-previous=\"" . string::toParam($value) . "\"" . (isset($data['style']) ? " style=\"" . $data['style'] . "\"" : "") . ">" . $value . "</textarea>
									<input type=\"hidden\" name=\"" . $name . "\" id=\"f_" . $name . "\" value=\"" . string::toParam($value) . "\" data-previous=\"" . string::toParam($value) . "\" />";
				$var['javascript-post'] .= "$(\"#f_" . $name . "_rt\").cleditor({
																			height: 250, 
																			controls: \"" . (isset($data['controls']) ? $data['controls'] : "bold italic underline size color removeformat | alignleft center alignright justify | link unlink | cut copy paste pastetext") . "\", 
																			updateTextArea: function(html) {
																				$(\"#f_" . $name . "\").val(html);
																			}
																			});";
			break;
			
			// ---------------------------
			case "select":
				$html .= "<select name=\"" . $name . "\" id=\"f_" . $name . "\" class=\"form-control\" data-previous=\"" . string::toParam($value) . "\"" . (isset($data['style']) ? " style=\"" . $data['style'] . "\"" : "") . ">";
				foreach ($options as $k => $v) {
					$html .= "<option value=\"" . string::toParam($k) . "\"";
					if ($k == $value) {
						$html .= " selected=\"selected\"";
					}
					$html .= ">" . $v . "</option>";
				}
				$html .= "</select>";
			break;
			
			// ---------------------------
			case "select-plus":
				$html .= "<div class=\"form-select-plus\">
										<select name=\"" . $name . "\" id=\"f_" . $name . "\" class=\"form-control\" data-previous=\"" . string::toParam($value) . "\">";
				foreach ($options as $k => $v) {
					$html .= "<option value=\"" . string::toParam($k) . "\"";
					if ($k == $value) {
						$html .= " selected=\"selected\"";
					}
					$html .= ">" . $v . "</option>";
				}
				$html .= "	</select>
										<input type=\"hidden\" name=\"" . $name . "_selected\" id=\"f_" . $name . "_selected\" value=\"" . $value . "\" />
										<button type=\"button\" title=\"Añadir\" class=\"btn btn-info\" onclick=\"" . $data['aux'] . "\"><i class=\"glyphicon glyphicon-plus\"></i></button>
									</div>";
			break;
			
			// ---------------------------
			case "switch":
				$html .= "<div class=\"form-switch\" data-src=\"f_" . $name . "\">
										<div class=\"btn-group\">
									  	<button type=\"button\" class=\"btn form-switch-yes\" data-value=\"1\" title=\"Sí\">SÍ</button>
											<button type=\"button\" class=\"btn form-switch-no\" data-value=\"0\" title=\"No\">NO</button>
										</div>
										<input type=\"hidden\" name=\"" . $name . "\" id=\"f_" . $name . "\" value=\"" . $value . "\" data-previous=\"" . $value . "\" />
									</div>";
			break;
			
			// ---------------------------
			case "switch-list":
				$html .= "<div class=\"form-switch-list\" data-src=\"f_" . $name . "\">";
				$n = 0;
				foreach ($options as $k => $v) {
					$html .= "<div class=\"form-switch-list-item\" data-pos=\"" . $n . "\">
											<div class=\"btn-group\">
								 	 			<button type=\"button\" class=\"btn btn-sm form-switch-yes\" data-value=\"1\" title=\"Sí\">SÍ</button>
												<button type=\"button\" class=\"btn btn-sm form-switch-no\" data-value=\"0\" title=\"No\">NO</button>
											</div>
											<label>" . $v . "</label>
										</div>";
					$n++;
				}
				$html .= "	<input type=\"hidden\" name=\"" . $name . "\" id=\"f_" . $name . "\" value=\"" . $value . "\" data-previous=\"" . $value . "\" />
									</div>";
			break;
			
			// ---------------------------
			case "checkbox-list":
				$html .= "<div class=\"form-checkbox-list\" data-src=\"f_" . $name . "\">
										<ul>\n";
				$values = array();
				foreach ($options as $k => $v) {
					$html .= "	<li style=\"width: " . $data['aux'] . ";\"><button type=\"button\" class=\"btn btn-sm btn-default\" title=\"Añadir\" data-value=\"" . $k . "\"><i class=\"glyphicon glyphicon-unchecked\"></i><span>" . $v . "</span></button></li>\n";
				}
				$html .= "	</ul>
										<input type=\"hidden\" name=\"" . $name . "\" id=\"f_" . $name . "\" value=\"" . $value . "\" data-previous=\"" . $value . "\" />
									</div>\n";
			break;
			
			// ---------------------------
			case "autocomplete-list":
				$html .= "<div class=\"widget-autocomplete-list\" id=\"f_" . $name . "_autocomplete\" data-src=\"f_" . $name . "\">
										<ul class=\"widget-autocomplete-list-items\"></ul>
										<div class=\"input-group\">
											<input type=\"text\" name=\"" . $name . "_search\" id=\"f_" . $name . "_search\" class=\"form-control\" />
											<span class=\"input-group-addon\"><i class=\"glyphicon glyphicon-search\"></i></span>
										</div>
										<ul class=\"widget-autocomplete-list-options\"></ul>
										<input type=\"hidden\" name=\"" . $name . "_source\" id=\"f_" . $name . "_source\" value=\"" . $data['aux'] . "\" />
										<input type=\"hidden\" name=\"" . $name . "\" id=\"f_" . $name . "\" value=\"" . $value . "\" data-previous=\"" . $value . "\" />
									</div>";
			break;
			
			// ---------------------------
			case "button-group":
				$html .= "<div class=\"form-button-group\" data-src=\"f_" . $name . "\">
										<div class=\"btn-group\">";
				$n = 0;
				foreach ($options as $k => $v) {
					$html .= "<button type=\"button\" class=\"btn btn-default\" data-value=\"" . $k . "\" title=\"" . $v . "\">" . $v . "</button>";
					$n++;
				}
				$html .= "	</div>
										<input type=\"hidden\" name=\"" . $name . "\" id=\"f_" . $name . "\" value=\"" . $value . "\" data-previous=\"" . $value . "\" />
									</div>";
			break;
			
			// ---------------------------
			case "add-list":
				$html .= "<div class=\"form-add-list\" id=\"f_" . $name . "_addlist\" data-src=\"f_" . $name . "\">
										<ul class=\"form-add-list-items form-add-list\"></ul>
										<div class=\"input-group\">
											<input type=\"text\" name=\"" . $name . "_name\" id=\"f_" . $name . "_name\" class=\"form-control\" />
											<span class=\"input-group-btn\">
												<button type=\"button\" class=\"btn btn-info form-add-list-button\" title=\"Añadir\"><i class=\"glyphicon glyphicon-plus\"></i> Añadir</button>
											</span>
										</div>
										<input type=\"hidden\" name=\"" . $name . "\" id=\"f_" . $name . "\" value=\"" . $value . "\" data-previous=\"" . $value . "\" />";
				if (isset($data['aux'])) {
					$html .= "<input type=\"hidden\" name=\"" . $name . "_validation\" id=\"f_" . $name . "_validation\" value=\"" . $data['aux'] . "\" />";
				}
				$html .= "</div>";
			break;
			
			// ---------------------------
			case "add-list-texts":
				$html .= "<div class=\"form-add-list-texts\" id=\"f_" . $name . "_addlist\" data-src=\"" . $name . "\">
										<ul class=\"form-add-list-texts-items form-add-list\"></ul>
										<div class=\"form-add-list-texts-add\">
											<button type=\"button\" class=\"btn btn-info\" data-id=\"" . $name . "\" title=\"Añadir un nuevo elemento\"><i class=\"glyphicon glyphicon-plus\"></i> Añadir elemento</button>
										</div>
										<input type=\"hidden\" name=\"" . $name . "\" id=\"f_" . $name . "\" value=\"" . base64_encode(misc::jsonEncode($value)) . "\" data-previous=\"" . base64_encode(misc::jsonEncode($value)) . "\" />
									</div>";
				$var['javascript-post'] .= "core.forms.elements.addListsTexts.title = \"" . string::toParam($data['aux']) . "\";
																		core.forms.elements.addListsTexts.lists['" . $name . "'] = [];";
				foreach ($value as $n => $item) {
					$var['javascript-post'] .= "core.forms.elements.addListsTexts.lists['" . $name . "'].push({name:\"" . string::toParam($item['name']) . "\", text:\"" . string::toParam($item['text']) . "\"});";
				}
			break;
			
			// ---------------------------
			case "images-list":
				$html .= "<div class=\"widget-images-list\" id=\"f_" . $name . "_images_list\" data-src=\"f_" . $name . "\">
										<ul></ul>
										<div class=\"btn btn-info fileinput-button\">
											<i class=\"glyphicon glyphicon-cloud-upload\"></i>
											<span>Subir nueva imagen</span>
											<input name=\"f_img_upload\" id=\"f_" . $name . "_img_upload\" type=\"file\" />
										</div>
										<div class=\"upload-progress\">
											<div class=\"bar\">
												<div class=\"indicator\" style=\"width: 0%;\"></div>
											</div>
											<p>Subiendo imagen...</p>
										</div>
										<input type=\"hidden\" name=\"" . $name . "\" id=\"f_" . $name . "\" value=\"" . $value . "\" data-previous=\"" . $value . "\" />
										<input type=\"hidden\" name=\"gallery\" id=\"f_" . $name . "_gallery\" value=\"" . (isset($data['gallery']) ? $data['gallery'] : "0") . "\" />
										<input type=\"hidden\" name=\"outputs\" id=\"f_" . $name . "_outputs\" value=\"" . (isset($data['outputs']) ? $data['outputs'] : "0") . "\" />
									</div>";
				$t = explode(",", $value);
				foreach ($t as $n => $image_id) {
					$image = core::imageGetJs($image_id, "thumb");
					if ($image !== false) {
						$var['javascript-post'] .= "core.cfg.images[" . $image_id . "] = " . misc::jsonEncode($image) . ";";
					}
				}
				// Create image editing form (only once)
				if (!isset($var['page']['form']['image-edit'])) {
					
					$var['html-post'] .= adminHtml::imageEditForm();
					
					$var['page']['form']['image-edit'] = true;
					
				}
			break;
			
			// ---------------------------
			case "file-upload":
				$html .= "<div class=\"widget-file-upload\" id=\"f_" . $name . "_file_upload\" data-src=\"f_" . $name . "\">
										<div class=\"form-control-static\"" . (isset($data['style']) ? " style=\"" . $data['style'] . "\"" : "") . ">" . ($value == "" ? "-" : $value) . "</div>
										<div class=\"btn btn-info fileinput-button\">
											<i class=\"glyphicon glyphicon-cloud-upload\"></i>
											<span>Subir nuevo archivo</span>
											<input name=\"f_file_upload\" id=\"f_" . $name . "_file_upload\" type=\"file\" />
										</div>
										<div class=\"upload-progress\">
											<div class=\"bar\">
												<div class=\"indicator\" style=\"width: 0%;\"></div>
											</div>
											<p>Subiendo archivo...</p>
										</div>
										<input type=\"hidden\" name=\"" . $name . "\" id=\"f_" . $name . "\" value=\"" . $value . "\" data-previous=\"" . $value . "\" />
										<input type=\"hidden\" name=\"outputs\" id=\"f_" . $name . "_outputs\" value=\"" . (isset($data['outputs']) ? $data['outputs'] : "0") . "\" />
									</div>";
			break;
			
		}
		
		if ($legend != "") {
			$html .= "<span class=\"legend\">" . $legend . "</span>";
		}
		
		$html .= "    </div>
							  </div>\n";
		
		return $html;
		
	}
	
	public static function pagination($list) {
		
		global $cfg, $var, $obj;
		
		$url = $_SERVER['REQUEST_URI'];
		
		$html = "<div class=\"row\">
				        <div class=\"col-md-4\">
				        	<ul class=\"pager\">";
		if ($list->buttons['prev']) {
			$html .= "<li class=\"previous\"><a href=\"" . misc::urlReplace($url, "page", ($list->page_num - 1)) . "\" title=\"Ir a la página anterior\">&larr; Página anterior</a></li>";
		} else {
			$html .= "<li class=\"previous disabled\"><span>&larr; Página anterior</span></li>";
		}
		$html .= "		</ul>
								</div>
				        <div class=\"col-md-4 text-center\">
				        	<ul class=\"pagination\">";
		if ($list->buttons['prev_jump']) {
			$html .= "<li><a href=\"" . misc::urlReplace($url, "page", ($list->page_num - $list->buttons_jump)) . "\" title=\"Retroceder " . $list->buttons_jump . " páginas\">&laquo;</a></li>";
		} else {
			$html .= "<li class=\"disabled\"><span>&laquo;</span></li>";
		}
		
		$p_ini = $list->page_num - 2;
		$p_fin = $list->page_num + 2;
		if ($p_ini <= 0) {
			$p_ini = 1;
			$p_fin = 5;
		} else if ($p_fin > $list->pages_total) {
			$p_ini = $list->pages_total - 4;
			$p_fin = $list->pages_total;
			if ($p_ini <= 0) {
				$p_ini = 1;
				$p_fin = 5;
			}
		}
		for ($p=$p_ini; $p<=$p_fin; $p++) {
			if ($p <= $list->pages_total) {
				$html .= "<li" . (($p == $list->page_num) ? " class=\"active\"" : "") . "><a href=\"" . misc::urlReplace($url, "page", $p) . "\" title=\"Ir a la página " . $p . "\">" . $p . "</a></li>";
			} else {
				$html .= "<li class=\"disabled\"><span>&nbsp;</span></li>";
			}
		}
		
		
		if ($list->buttons['next_jump']) {
			$html .= "<li><a href=\"" . misc::urlReplace($url, "page", ($list->page_num + $list->buttons_jump)) . "\" title=\"Avanzar " . $list->buttons_jump . " páginas\">&raquo;</a></li>";
		} else {
			$html .= "<li class=\"disabled\"><span>&raquo;</span></li>";
		}
		$html .= "		</ul>";
		
		if ($list->items_total == 0) {
			$html .= "<p class=\"text-muted\">0 elementos</p>";
		} else {
			$html .= "<p class=\"text-muted\"><strong>" . number::format($list->items_total) . "</strong> " . (($list->items_total == 1) ? "elemento" : "elementos") . " en <strong>" . number_format($list->pages_total, 0, ",", ".") . "</strong> " . (($list->pages_total == 1) ? "página" : "páginas") . "</p>";
		}
		
		$html .= "	</div>
				        <div class=\"col-md-4\">
				        	<ul class=\"pager\">";
		if ($list->buttons['next']) {
			$html .= "<li class=\"next\"><a href=\"" . misc::urlReplace($url, "page", ($list->page_num + 1)) . "\" title=\"Ir a la página siguiente\">Página siguiente &rarr;</a></li>";
		} else {
			$html .= "<li class=\"next disabled\"><span>Página siguiente &rarr;</span></li>";
		}
		$html .= "		</ul>
								</div>
				      </div>";
							
		$var['javascript-post'] .= "var list_page_num = " . $list->page_num . ";\n";
		
		return $html;
		
	}
	
	public static function imageEditForm() {
		
		$html = "";
		
		$html .= "<div class=\"image-edit hide\"><div class=\"form\">" . adminHtml::form("images", "new") . "</div></div>";
		
		return $html;
		
	}
	
}

?>