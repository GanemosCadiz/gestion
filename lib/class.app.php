<?php

class app {
	
	// --------------------------------------------------------------
	// Output handling
	
	// Display fatal error page
	public static function errorFatal($text) {
		
		global $cfg, $var, $obj;
		
		$var['error'] = true;
		
		html::render(array(
			'type' => "error", 
			'content' => $text
		));
		
		die();
		
	}
	
	
	// --------------------------------------------------------------
	// Data validation
	
	public static function validate($data, $value, $type, $option="") {
		
		global $cfg, $var, $obj;
		
		switch ($type) {
			
			// --------------------------------------------------------
			case "email":
				return filter_var($value, FILTER_VALIDATE_EMAIL);
			break;
			
			// --------------------------------------------------------
			case "id":
				return true;
			break;
			
			// --------------------------------------------------------
			case "phone":
				return 	app::validate($data, $value, "onlynumbers") && 
								app::validate($data, $value, "length", 9) && 
								(substr($value, 0, 1) == "9" || substr($value, 0, 1) == "7" || substr($value, 0, 1) == "6") && 
								substr($value, 0, 3) != "800" && substr($value, 0, 3) != "900" && substr($value, 0, 3) != "901" && substr($value, 0, 3) != "902" && substr($value, 0, 3) != "905" && substr($value, 0, 3) != "908" && substr($value, 0, 3) != "909";
			break;
			
			// --------------------------------------------------------
			case "postal":
				return 	app::validate($data, $value, "onlynumbers") && 
								app::validate($data, $value, "length", 5);
			break;
			
			// --------------------------------------------------------
			case "onlytext":
				return !preg_match("/^\d+$/", $value);
			break;
			
			// --------------------------------------------------------
			case "onlynumbers":
				return preg_match("/^\d+$/", $value);
			break;
			
			// --------------------------------------------------------
			case "length":
				return strlen($value) == $option;
			break;
			
			// --------------------------------------------------------
			case "minlength":
				return strlen($value) >= $option;
			break;
			
			// --------------------------------------------------------
			case "maxlength":
				return strlen($value) <= $option;
			break;
			
			// --------------------------------------------------------
			case "date":
				if (strlen($value) != 10 || count(explode("/", $value)) != 3) {
					return false;
				} else {
					$t = explode("/", $value);
					return checkdate(intval($t[1]), intval($t[0]), intval($t[2]));
				}
			break;
			
			// --------------------------------------------------------
			case "samevalue":
				if (!isset($data[$option])) {
					return true;
				} else {
					return $value == $data[$option];
				}
			break;
			
		}
		
		return false;
		
	}
	
	
	// --------------------------------------------------------------
	// Data post-process
	
	public static function postprocess($value, $type, $option="") {
		
		global $cfg, $var, $obj;
		
		switch ($type) {
			
			// --------------------------------------------------------
			case "uppercase":
				return string::uppercase($value);
			break;
			
			// --------------------------------------------------------
			case "lowercase":
				return string::lowercase($value);
			break;
			
		}
		
		return $value;
		
	}
	
	
	// --------------------------------------------------------------
	// Lang texts
	
	public static function textGet($code) {
		
		global $cfg, $var, $obj;
		
		$text = "";
		
		if (isset($cfg['texts'][$obj['lang']->lang][$code])) {
			$text = $cfg['texts'][$obj['lang']->lang][$code];
		} else if (isset($cfg['texts'][$cfg['lang']['default']][$code])) {
			$text = $cfg['texts'][$cfg['lang']['default']][$code];
		}
		
		return $text;
		
	}
	
	
	// --------------------------------------------------------------
	// 404
	
	public static function error404() {
		
		global $cfg, $var, $obj;
		
		die(html::page404());
		
	}
	
}

class html {
	
	// --------------------------------------------------------------
	// Html generation
	
	public static function renderPage($options=array()) {
		
		global $cfg, $var, $obj;
		
		$options['type'] = !isset($options['type']) ? "normal" : $options['type'];
		
		$html = "";
		
		$html .= html::head();
		
		switch ($options['type']) {
			
			case "normal":
			default:
				$html .= (isset($options['content']) ? $options['content'] : "");
			break;
			
		}
		
		$html .= html::footer();
		
		echo $html;
		
	}
	
	public static function head($options=array()) {
		
		global $cfg, $var, $obj;
		
		$html = "";
		
		$html = "<!doctype html>
							<!--[if lt IE 7]>      <html class=\"no-js lt-ie9 lt-ie8 lt-ie7\" lang=\"\"> <![endif]-->
							<!--[if IE 7]>         <html class=\"no-js lt-ie9 lt-ie8\" lang=\"\"> <![endif]-->
							<!--[if IE 8]>         <html class=\"no-js lt-ie9\" lang=\"\"> <![endif]-->
							<!--[if gt IE 8]><!--> <html class=\"no-js\" lang=\"\"> <!--<![endif]-->
							<head>
								
								<meta charset=\"utf-8\" />
								<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge,chrome=1\" />
								
								<title>" . $cfg['app']['title'] . "</title>
								
								<meta name=\"description\" content=\"\" />
								<meta name=\"keywords\" content=\"\" />
								
								<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\" />
								
								<link rel=\"shortcut icon\" type=\"image/x-icon\" href=\"" . $cfg['urls']['app'] . "img/favicon.gif\" />
								
								<link rel=\"stylesheet\" href=\"" . $cfg['urls']['app'] . "css/vendor/normalize.min.css\" />
								<link rel=\"stylesheet\" href=\"" . $cfg['urls']['app'] . "css/vendor/main.css\" />
								<link rel=\"stylesheet\" href=\"" . $cfg['urls']['app'] . "css/framewoork.css\" />
								<link rel=\"stylesheet\" href=\"" . $cfg['urls']['app'] . "css/styles.css\" />
								<!--[if lt IE 9]>
									<link rel=\"stylesheet\" href=\"" . $cfg['urls']['app'] . "css/styles-ie.css\" />
								<![endif]-->
								
								<script src=\"" . $cfg['urls']['app'] . "js/vendor/modernizr-2.8.3-respond-1.4.2.min.js\"></script>
								
							</head>
							
							<body>";
		
		return $html;
		
	}
	
	public static function footer($options=array()) {
		
		global $cfg, $var, $obj;
		
		$html = "	<script src=\"" . $cfg['urls']['app'] . "js/vendor/jquery-1.11.2.min.js\"></script>
							<script src=\"" . $cfg['urls']['app'] . "js/vendor/plugins.js\"></script>
							<script src=\"" . $cfg['urls']['app'] . "js/locale_" . $obj['lang']->lang . ".js\"></script>
							<script src=\"" . $cfg['urls']['app'] . "js/?script=app.aux\"></script>
							<script src=\"" . $cfg['urls']['app'] . "js/main.js\"></script>";
		
		$html .= html::javascript();
		
		// Responsive test
		if ($cfg['localhost']) {
			$html .= "<div class=\"responsive\"><span></span></div>";
		}
		
		// Add extra CSS
		$html .= ($var['css-post'] != "") ? "<style type=\"text/css\">" . $var['css-post'] . "</style>" : "";
		
		// Add extra HTML
		$html .= $var['html-post'];
		
		$html .= "</body>
							</html>";
		
		return $html;
		
	}
	
	public static function javascript() {
		
		global $cfg, $var, $obj;
		
		$html = "";
		
		$html .= "<script>
								app.cfg.root = \"" . $cfg['urls']['app'] . "\";
								app.cfg.module = \"" . (isset($var['page']) ? $var['page']['module'] : "") . "\";
								app.cfg.section = \"" . (isset($var['page']) ? $var['page']['section'] : "") . "\";
								app.cfg.action = \"" . (isset($var['page']) ? $var['page']['action'] : "") . "\";
							</script>";
		
		// Add extra HTML
		$html .= ($var['javascript-post'] != "") ? "<script>" . $var['javascript-post'] . "</script>" : "";
		
		return $html;
		
	}
	
}

?>