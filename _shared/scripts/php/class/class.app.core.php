<?php

class core {
	
	// ==============================================================
	// Main functions declaration file
	// --------------------------------------------------------------
	
	// --------------------------------------------------------------
	// Core modules handling
	
	// Checks page access and gets page info
	public static function getPage() {
		
		global $cfg, $var, $obj;
		
		$module = core::getPageModule();
		$section = core::getPageSection($module);
		$action = isset($_REQUEST['action']) ? inputClean::clean($_REQUEST['action'], 32) : "";
		
		// Logged redirection
		if (in_array($module, array("login")) && (isset($obj['user']) && $obj['user']->auth)) {
			header("Location: " . $cfg['urls']['app'] . "home/");
			die();
		}
		
		// Check if module file exists
		if (!isset($cfg['modules'][$module]) 
				|| !file_exists("mods/mod." . $cfg['modules'][$module]['code'] . ".php")) {
			core::errorFatal("<p>Module doesn't exists.</p>");
		}
		
		return array(
									'module' => $cfg['modules'][$module]['code'], 
									'section' => (isset($cfg['modules'][$module]['sections'])) ? $cfg['modules'][$module]['sections'][$section]['code'] : "", 
									'action' => $action, 
									'module-text' => $module, 
									'section-text' => $section, 
									'file' => "mods/mod." . $cfg['modules'][$module]['code'] . ".php", 
									'module-data' => $cfg['modules'][$module], 
									'section-data' => isset($cfg['modules'][$module]['sections'][$section]) ? $cfg['modules'][$module]['sections'][$section] : array()
									);
		
	}
	
	// Gets module
	public static function getPageModule() {
		
		global $cfg, $var, $obj;
		
		// Get default module
		$default = "";
		foreach ($cfg['modules'] as $k => $v) {
			$default = ($default == "") ? $k : $default;
			if (isset($v['default'])) {
				$default = $k;
				break;
			}
		}
		
		// Get module from url
		if (isset($_REQUEST['module'])) {
			$module = inputClean::clean($_REQUEST['module'], 32);
			if (!isset($cfg['modules'][$module])) {
				$module = $default;
			}
		} else {
			$module = $default;
		}
		
		return $module;
		
	}
	
	// Gests section
	public static function getPageSection($module) {
		
		global $cfg, $var, $obj;
		
		// Check module
		if (!isset($module)) {
			core::errorFatal("Module not defined.");
		}
		if (!isset($cfg['modules'][$module])) {
			core::errorFatal("Wrong module.");
		}
		
		if (!isset($cfg['modules'][$module]['sections'])) {
			return "";
		}
		
		// Get default section
		$default = "";
		foreach ($cfg['modules'][$module]['sections'] as $k => $v) {
			$default = ($default == "") ? $k : $default;
			if (isset($v['default'])) {
				$default = $k;
				break;
			}
		}
		
		// Get section from url
		if (isset($_REQUEST['section'])) {
			$section = inputClean::clean($_REQUEST['section'], 32);
			if (!isset($cfg['modules'][$module]['sections'][$section])) {
				$section = $default;
			}
		} else {
			$section = $default;
		}
		
		return $section;
		
	}
	
	
	// --------------------------------------------------------------
	// User control
	
	// Log user
	public static function login($username, $password) {
		
		global $cfg, $var, $obj;
		
		if ($obj['user']->auth) {
		  $login_ok = true;
		} else {
		  $username = inputClean::clean($username, 256);
		  $password = inputClean::clean($password, 256);
		  $login_ok = $obj['user']->login($username, $password);
		}
		if ($login_ok) {
			
			// Log action
			admin::logAction("users", "login-ok", "");
			
		  $output = core::resultOK();
		  
		} else {
		  
		  // Log action
			admin::logAction("users", "login-error", $username);
		  
			$output = core::resultError(_("Nombre de usuario o contraseña incorrectos."));
			
		}
		return $output;
		
	}
	
	public static function logout() {
		
		global $cfg, $var, $obj;
		
		if (isset($obj['user']) && $obj['user']->auth) {
			
			// Log action
			admin::logAction("users", "logout", "");
			
			$obj['user']->logout();
			
			core::userSessionDelete($obj['user']->data);
			
		}
		
	}
	
	// Check logged user
	public static function userCheckLogged() {
		
		global $cfg, $var, $obj;
		
		if (!isset($obj['user']) 
				|| empty($obj['user']->data) 
				|| !$obj['user']->auth) {
			return false;
		}
		
		return true;	
		
	}
	
	// Allow user access
	public static function userAllow($page) {
		
		global $cfg, $var, $obj;
		
		// Check module restricted access
		$restricted = empty($cfg['modules'][$page['module']]['allowed-users']) ? false : true;
		// Check section restricted access
		if ($page['section'] != "") {
			$restricted = empty($cfg['modules'][$page['module']][$page['section']]['allowed-users']) ? $restricted : true;
		}
		
		if ($restricted) {
			
			// Check user object & restricted access
			if (!core::userCheckLogged()) {
				admin::errorLogin("<p>No está autorizado a acceder a esta página.</p>");
			}
			
			// Check section restricted access
			if ($page['section'] != "" 
					&& !empty($cfg['modules'][$page['module']][$page['section']]['allowed-users']) 
					&& !$obj['user']->auth) {
				admin::errorLogin("<p>No está autorizado a acceder a esta página.</p>");
			}
			
			// Check user access
			$user = $obj['user']->data;
			
			// Special case for download users login
			if ($user['type'] == "download" && $page['module'] == "home") {
				header("Location: " . $cfg['urls']['app'] . "downloads/");
				die();
			}
			
			// Check active/deleted
			if ($user['active'] == 0 || $user['deleted'] == 1) {
				admin::errorLogin("<p>No está autorizado a acceder a esta página.</p>");
			}
			
			// Check allowed modules
			if (!core::userCheckModule($page['module'])) {
				admin::errorLogin("<p>No está autorizado a acceder a esta página.</p>");
			}
			
		}
		
		return true;
		
	}
	
	// Check user acces to module
	public static function userCheckModule($module) {
		
		global $cfg, $var, $obj;
		
		// Check module restricted access
		$restricted = empty($cfg['modules'][$module]['allowed-users']) ? false : true;
		
		if ($restricted) {
			
			// Check user object & restricted access
			if (!core::userCheckLogged()) {
				return false;
			}
			
			// Check if user type is allowed in module
			if (!in_array($obj['user']->data['type'], $cfg['modules'][$module]['allowed-users'])) {
				return false;
			}
			
			// Check user allowed modules
			$user = $obj['user']->data;
			
			if ((isset($cfg['modules'][$module]['restricted']) && $cfg['modules'][$module]['restricted']) // It's a restricted access module
					&& $user['permissions']['modules'] != "all" // Doesn't have granted access
					&& !in_array($module, $user['permissions']['modules'])) { // Module is not in user's allowed modules
				return false;
			}
			
			// Special cases (download user)
			if ($user['type'] == "download" && $module != "download") {
				return false;
			}
			
		}
		
		return true;
		
	}
	
	// Allow user to content
	public static function userAllowContent($table, $item, $checklist=array()) {
		
		global $cfg, $var, $obj;
		
		foreach ($checklist as $n => $check) {
			
			switch ($check) {
				
				case "noauthor":
					// ---------------------------------------------------------
					// Check allowed content author
					
					if (isset($obj['user']->data['permissions']['noauthor']) 
							&& !$obj['user']->data['permissions']['noauthor']) {
						
						if (core::tableGet($table, "user_id") != "") {
							// Table has user id
							if (core::fieldGetVal($table, $item, "user_id") != $obj['user']->data['user_id']) {
								admin::errorLogin("<p>No está autorizado a acceder a esta página.</p>");
							}
						} else {
							// Get related table
							$related_table = core::tableGet($table, "related_table");
							$items_list = core::getAuthorItems($related_table, "user_id", $obj['user']->data['user_id']);
							if (!in_array(core::fieldGetVal($table, $item, "user_id"), $items_list)) {
								admin::errorLogin("<p>No está autorizado a acceder a esta página.</p>");
							}
						}
						
					}
					
				break;
				
				case "categories":
					// ---------------------------------------------------------
					// Check allowed categories
					
					if (isset($obj['user']->data['permissions']['categories']) 
							&& $obj['user']->data['permissions']['categories'] != "all" 
							&& !empty($obj['user']->data['permissions']['categories'])) {
						
						if (core::tableGet($table, "category_id") != "") {
							// Table has category id
							if (!in_array(core::fieldGetVal($table, $item, "category_id"), $obj['user']->data['permissions']['categories'])) {
								admin::errorLogin("<p>No está autorizado a acceder a esta página.</p>");
							}
						} else {
							// Get related table
							// TODO
							$related_table = core::tableGet($table, "related_table");
							$items_list = core::getAuthorItems($related_table, "category_id", $obj['user']->data['permissions']['categories']);
							if (!in_array(core::fieldGetVal($table, $item, "site_id"), $items_list)) {
								admin::errorLogin("<p>No está autorizado a acceder a esta página.</p>");
							}
						}
						
					}
					
				break;
				
				case "items":
					// ---------------------------------------------------------
					// Check allowed items
					
					if (isset($obj['user']->data['permissions']['items']) 
							&& $obj['user']->data['permissions']['items'] != "all" 
							&& !empty($obj['user']->data['permissions']['items'])) {
						
						if (core::tableGet($table, "item_id") != "") {
							// Table has item id
							if (!in_array(core::fieldGetVal($table, $item, "item_id"), $obj['user']->data['permissions']['items'])) {
								admin::errorLogin("<p>No está autorizado a acceder a esta página.</p>");
							}
						} else {
							// Get related table
							$related_table = core::tableGet($table, "related_table");
							$items_list = core::getAuthorItems($related_table, "item_id", $obj['user']->data['permissions']['items']);
							array_push($query, core::fieldGetName($related_table, "id") . " IN ('" . implode("','", $items_list) . "')");
						}
						
					}
					
				break;
				
			}
			
		}
		
		return true;
		
	}
	
	// Querys for user acces to data
	public static function userPermissionQuery($table, $checklist=array()) {
		
		global $cfg, $var, $obj;
		
		$query = array();
		
		foreach ($checklist as $n => $check) {
			
			switch ($check) {
				
				case "noauthor":
					// ---------------------------------------------------------
					// Check allowed content author
					
					if (isset($obj['user']->data['permissions']['noauthor']) 
							&& !$obj['user']->data['permissions']['noauthor']) {
						
						if (core::tableGet($table, "user_id") != "") {
							// Table has user id
							array_push($query, core::fieldGetName($table, "user_id") . "='" . $obj['user']->data['user_id'] . "'");
						} else {
							// Get related table
							$related_table = core::tableGet($table, "related_table");
							$items_list = core::getAuthorItems($related_table, "user_id", $obj['user']->data['user_id']);
							array_push($query, core::fieldGetName($related_table, "id") . " IN ('" . implode("','", $items_list) . "')");
						}
						
					}
					
				break;
				
				case "categories":
					// ---------------------------------------------------------
					// Check allowed categories
					
					if (isset($obj['user']->data['permissions']['categories']) 
							&& $obj['user']->data['permissions']['categories'] != "all" 
							&& !empty($obj['user']->data['permissions']['categories'])) {
						
						if (core::tableGet($table, "category_id") != "") {
							// Table has category id
							array_push($query, core::fieldGetName($table, "category_id") . " IN ('" . implode("','", $obj['user']->data['permissions']['categories']) . "')");
						} else {
							// Get related table
							$related_table = core::tableGet($table, "related_table");
							$items_list = core::getAuthorItems($related_table, "category_id", $obj['user']->data['permissions']['categories']);
							array_push($query, core::fieldGetName($related_table, "id") . " IN ('" . implode("','", $items_list) . "')");
						}
						
					}
					
				break;
				
				case "items":
					// ---------------------------------------------------------
					// Check allowed items
					
					if (isset($obj['user']->data['permissions']['items']) 
							&& $obj['user']->data['permissions']['items'] != "all" 
							&& !empty($obj['user']->data['permissions']['items'])) {
						
						if (core::tableGet($table, "item_id") != "") {
							// Table has item id
							array_push($query, core::fieldGetName($table, "item_id") . " IN ('" . implode("','", $obj['user']->data['permissions']['items']) . "')");
						} else {
							// Get related table
							$related_table = core::tableGet($table, "related_table");
							$items_list = core::getAuthorItems($related_table, "item_id", $obj['user']->data['permissions']['items']);
							array_push($query, core::fieldGetName($related_table, "id") . " IN ('" . implode("','", $items_list) . "')");
						}
						
					}
					
				break;
				
			}
			
		}
		
		if (!empty($query)) {
			return " (" . implode(" AND ", $query) . ") ";
		} else {
			return "";
		}
		
	}
	
	// Delete user sessions
	public static function userSessionDelete($data) {
		
		global $cfg, $var, $obj;
		
		$q = "DELETE FROM " . core::tableGet("users_sessions", "name") . " 
								WHERE 
									session_user_id='" . core::fieldGetVal(core::tableGet("users", "name"), $data, "user_id") . "'";
		$r = $obj['db']->query($q);
		$q = "DELETE FROM " . core::tableGet("users_sessions_auto", "name") . " 
								WHERE 
									user_id='" . core::fieldGetVal(core::tableGet("users", "name"), $data, "user_id") . "'";
		$r = $obj['db']->query($q);
		
		return true;
		
	}
	
	
	// --------------------------------------------------------------
	// Fields data
	
	// Field value
	public static function fieldGetVal($table, $item, $field_name) {
		
		global $cfg, $var, $obj;
		
		if (!isset($cfg['fields']) || !isset($cfg['fields'][$table]) || !isset($cfg['fields'][$table][$field_name])) {
			return "";
		}
		
		$f = $cfg['fields'][$table][$field_name];
		$name = $f['name'];
		
		if (isset($f['encrypted']) && $f['encrypted']) {
			
			$salt = (isset($f['salted']) && $f['salted']) ? $item[core::fieldGetName($table, "salt")] : "";
			
			return myCrypt::decrypt($item[$name], $cfg['crypt-key'], $salt);
			
		} else {
			
			return (isset($item[$name]) ? $item[$name] : "");
			
		}
		
	}
	
	// Field name
	public static function fieldGetName($table, $field_name) {
		
		global $cfg, $var, $obj;
		
		if (!isset($cfg['fields']) || !isset($cfg['fields'][$table]) || !isset($cfg['fields'][$table][$field_name])) {
			return "";
		}
		
		return $cfg['fields'][$table][$field_name]['name'];
		
	}
	
	// Returs fields properties
	public static function fieldGet($table, $field_name) {
		
		global $cfg, $var, $obj;
		
		if (!isset($cfg['fields']) || !isset($cfg['fields'][$table]) || !isset($cfg['fields'][$table][$field_name])) {
			return false;
		}
		
		return $cfg['fields'][$table][$field_name];
		
	}
	
	public static function fieldVal($table, $field_name, $field_value, $salt="") {
		
		global $cfg, $var, $obj;
		
		$field_data = core::fieldGet($table, $field_name);
		
		if (isset($field_data['process'])) {
			
			$field_value = is_callable($field_data['process']) ? $field_data['process']($field_value) : $$field_data['process']($field_value);
			
		}
		
		if (isset($field_data['encrypted']) && $field_data['encrypted']) {
			if (isset($field_data['salted']) && $field_data['salted']) {
				$field_value = myCrypt::encrypt($field_value, $cfg['crypt-key'], $salt);
			} else {
				$field_value = myCrypt::encrypt($field_value, $cfg['crypt-key']);
			}
		}
		
		return $field_value;
		
	}
	
	// Table
	public static function tableGet($table, $param) {
		
		global $cfg, $var, $obj;
		
		if (!isset($cfg['db-tables']) || !isset($cfg['db-tables'][$table]) || !isset($cfg['db-tables'][$table][$param])) {
			return "";
		}
		
		return $cfg['db-tables'][$table][$param];
		
	}
	
	// Table name
	public static function tableGetName($table) {
		
		global $cfg, $var, $obj;
		
		if (!isset($cfg['db-tables']) || !isset($cfg['db-tables'][$table]) || !isset($cfg['db-tables'][$table]['name'])) {
			return "";
		}
		
		return core::tableGet($table, "name");
		
	}
	
	public static function tableGetId($table) {
		
		global $cfg, $var, $obj;
		
		if (!isset($cfg['db-tables']) || !isset($cfg['db-tables'][$table]) || !isset($cfg['db-tables'][$table]['id'])) {
			return "";
		}
		
		return core::tableGet($table, "id");
		
	}
	
	
	// --------------------------------------------------------------
	// Get info
	
	// Gests page number
	public static function getListPageNum() {
		
		global $cfg, $var, $obj;
		
		if (isset($_REQUEST['page'])) {
			$page = inputClean::clean($_REQUEST['page'], 6);
			if (!is_numeric($page)) {
				$page = 1;
			}
		} else {
			$page = 1;
		}
		
		return $page;
		
	}
	
	// Gets list ordering criteria
	public static function getListCriteria() {
		
		global $cfg, $var, $obj;
		
		if (isset($var['page']['list'])) {
			
			if (isset($_REQUEST['sorting'])) {
				// Url parameters
				
				$t = explode("|", $_REQUEST['sorting']);
				$sorting_field = inputClean::clean($t[0], 32);
				$sorting_order = inputClean::clean($t[1], 4);
				
			} else if (isset($_COOKIE[$var['page']['list']['id']])) {
				// Cookie values
				
				$t = explode("|", myCrypt::decrypt($_COOKIE[$var['page']['list']['id']], $cfg['crypt-key']));
				$sorting_field = inputClean::clean($t[0], 32);
				$sorting_order = inputClean::clean($t[1], 4);
				
			} else {
				// Default values
				
				$sorting_field = $var['page']['list']['sorting-field'];
				$sorting_order = $var['page']['list']['sorting-order'];
				
			}
			
			// Check correct values
			$ok = "";
			foreach ($var['page']['list']['columns'] as $n => $column) {
				if ($sorting_field == $column['field'] && $column['sortable']) {
					$ok = $n;
				}
			}
			if (!$ok) {
				$sorting_field = $var['page']['list']['sorting-field'];
				$var['page']['list']['sorting-fieldnum'] = 0;
			} else {
				$var['page']['list']['sorting-fieldnum'] = $ok;
			}
			if (!in_array(strtoupper($sorting_order), array("ASC", "DESC"))) {
				$sorting_order = $var['page']['list']['sorting-order'];
			}
			
			// Write cookie
			@setcookie($var['page']['list']['id'], myCrypt::encrypt($sorting_field . "|" . $sorting_order, $cfg['crypt-key']), time() + (10 * 365 * 24 * 60 * 60), $cfg['paths']['root']);
			
			return array(
				'field' => $sorting_field, 
				'order' => $sorting_order
			);
			
		} else {
			
			return array();
			
		}
		
	}
	
	// Gets list filters
	public static function getListFilters() {
		
		global $cfg, $var, $obj;
		
		$filters = array();
		
		if (isset($var['page']['header']['filters'])) {
			
			if (!isset($_SESSION['list-filters']) || $_SESSION['list-filters']['id'] != $var['page']['list']['id']) {
				$filters = array(
					'id' => $var['page']['list']['id'], 
					'filters' => array()
				);
			} else {
				$filters = $_SESSION['list-filters'];
			}
			
			if (isset($_REQUEST['filter'])) {
				// Filter operation comes from url
				$t = explode("|", inputClean::clean($_REQUEST['filter']));
				if (count($t) >= 2 && count($t) <= 3) {
					
					$action = $t[0];
					$field = $t[1];
					$value = isset($t[2]) ? $t[2] : 0;
					
					switch ($action) {
						
						case "add":
							// Check values
							foreach ($var['page']['header']['filters']['list'] as $n => $filter) {
								if ($field == $filter['field'] && isset($filter['options'][$value])) {
									// Field and value are ok
									$filters['filters'][$filter['field']] = array(
										'title' => $filter['title'], 
										'value' => $value, 
										'value-title' => $filter['options'][$value]
									);
								}
							}
						break;
						
						case "remove":
							unset($filters['filters'][$field]);
						break;
						
					}
					
				}
			}
			
		}
		
		if (!empty($filters)) {
			$_SESSION['list-filters'] = $filters;
		}
		
		return $filters;
		
	}
	
	// Get item from id (provided or in url)
	public static function getItem($table, $id="") {
		
		global $cfg, $var, $obj;
		
		if (core::tableGet($table, "name") == "") {
			return false;
		}
		
		if ($id == "" && isset($_REQUEST['item']) && $_REQUEST['item'] != "") {
			// Try to get id from url
			$id = inputClean::clean($_REQUEST['item'], 11);
		}
		
		if ($id == "") {
			// No item id provided, return empty array
			
			if (!isset($cfg['fields'][$table])) {
				return false;
			}
			
			$item = array();
			
			foreach ($cfg['fields'][$table] as $k => $v) {
				$item[$k] = "";
			}
			
			return $item;
			
		} else {
			// Get item from database
			
			$q = "SELECT * FROM " . core::tableGet($table, "name") . " 
									WHERE 
										" . core::tableGet($table, "id") . "='" . $id . "' 
										" . (core::fieldGetName($table, "deleted") != "" ? " AND " . core::fieldGetName($table, "deleted") . "='0' " : "") . " 
									ORDER BY " . core::tableGet($table, "id") . " ASC 
									LIMIT 1";
			$r = $obj['db']->query($q);
			
			if ($obj['db']->numRows($r) == 0) {
				return false;
			}
			
			return $obj['db']->assoc($r);
			
		}
		
	}
	
	// Gest list of items
	public static function getList($table, $options=array()) {
		
		global $cfg, $var, $obj;
		
		if (core::tableGet($table, "name") == "") {
			return array();
		}
		
		$options['active-filter'] = isset($options['active-filter']) ? $options['active-filter'] : false;
		$options['user-filter'] = isset($options['user-filter']) ? $options['user-filter'] : true;
		$options['deleted-filter'] = isset($options['deleted-filter']) ? $options['deleted-filter'] : true;
		$options['exception-filter'] = isset($options['exception-filter']) ? $options['exception-filter'] : array();
		$options['parent_id'] = isset($options['parent_id']) ? $options['parent_id'] : "";
		$options['zero-first'] = isset($options['zero-first']) ? $options['zero-first'] : true;
		
		if ($options['zero-first']) {
			$output = array(
				'0' => "Sin " . core::tableGet($table, "title-single")
			);
		} else {
			$output = array();
		}
		
		// Get list
		
		// User filter (default: yes)
		if ($options['user-filter']) {
			$user_permissions = core::userPermissionQuery($table);
		} else {
			$user_permissions = "";
		}
		
		$wheres = array();
		
		// Active filter
		if ($options['active-filter']) {
			array_push($wheres, core::fieldGetName($table, "active") . "='1'");
		}
		// Deleted filter
		if ($options['deleted-filter']) {
			array_push($wheres, core::fieldGetName($table, "deleted") . "='0'");
		}
		// User filter
		if ($user_permissions != "") {
			array_push($wheres, $user_permissions);
		}
		// Exceptions
		if (!empty($options['exception-filter'])) {
			foreach ($options['exception-filter'] as $n => $exception) {
				array_push($wheres, $exception);
			}
		}
		// Parent
		if ($options['parent_id'] != "") {
			array_push($wheres, core::fieldGetName($table, "parent_id") . "='" . $options['parent_id'] . "'");
		}
		
		$q = "SELECT * FROM " . core::tableGet($table, "name") . " 
								" . ((!empty($wheres)) ? " WHERE " . implode(" AND ", $wheres) : "") . " 
								ORDER BY " . core::fieldGetName($table, "name") . " ASC";
		$r = $obj['db']->query($q);
		
		while ($i = $obj['db']->assoc($r)) {
			
			$output[$i[core::tableGet($table, "id")]] = core::fieldGetVal($table, $i, core::fieldGetName($table, "name"));
			
		}
		
		return $output;
		
	}
	
	
	// --------------------------------------------------------------
	// Config
	
	public static function configFields() {
		
		global $cfg, $var, $obj;
		
		$output = array();
		
		$q = "SELECT * FROM " . core::tableGet("config", "name") . " 
								ORDER BY  
									param ASC";
		$r = $obj['db']->query($q);
		
		while ($c = $obj['db']->assoc($r)) {
			$output[$c['param']] = $c['value'];
		}
		
		$output['id'] = "";
		
		return $output;
		
	}
	
	public static function configGet($param) {
		
		global $cfg, $var, $obj;
		
		$q = "SELECT value FROM " . core::tableGet("config", "name") . " 
								WHERE 
									param='" . $param . "' 
								LIMIT 1";
		$r = $obj['db']->query($q);
		if (!$r || $obj['db']->numRows($r) == 0) {
			return "";
		} else {
			return $obj['db']->getValues($r);
		}
		
	}
	
	public static function configSave($input) {
		
		global $cfg, $var, $obj;
		
		// Don't allow readonly users
		if (!admin::userAllowWrite()) {
			admin::errorLogin("<p>No está autorizado a acceder a esta página.</p>");
		}
		
		if (!isset($input['data'])) { misc::error404(); }
		
		foreach ($input['data'] as $k => $v) {
			
			$param = inputClean::clean($k);
			$value = inputClean::clean($v);
			
			$value = core::fieldVal("config", $param, $value);
			
			$q = "UPDATE " . core::tableGet("config", "name") . " 
									SET 
										value='" . $value . "' 
									WHERE 
										param='" . $param . "' 
									LIMIT 1";
			$r = $obj['db']->query($q);
			if (!$r) {
				return core::resultError("Error al guardar el parámetro " . $param);
			}
			
		}
		
		return core::resultOK();
		
	}
	
	
	// --------------------------------------------------------------
	// Forms
	
	public static function formSave($input) {
		
		global $cfg, $var, $obj;
		
		// Don't allow readonly users
		if (!admin::userAllowWrite()) {
			misc::error404();
		}
		
		if (!isset($input['element']) || 
				!isset($input['mode']) || 
				!isset($input['data'])) { misc::error404(); }
		
		$var['form-table'] = inputClean::clean($input['element'], 32);
		$var['form-fields'] = isset($input['fields']) ? inputClean::clean($input['fields'], 32) : $var['form-table'];
		$var['form-mode'] = inputClean::clean($input['mode'], 11);
		$var['form-item_id'] = inputClean::clean($input['item'], 11);
		
		if (!isset($cfg['db-tables'][$var['form-table']]) || !isset($cfg['fields'][$var['form-fields']])) {
			misc::error404();
		}
		
		if ($var['form-mode'] == "edit") {
			// Edit item
			
			if (!isset($input['item'])) { misc::error404(); }
			
			$var['item'] = core::getItem($var['form-table'], $var['form-item_id']);
			
			if (!$var['item']) {
				return core::resultError("El elemento no existe o fue borrado.");
			}
			
			// Check user access to content
			if (!core::userAllowContent($var['form-table'], $var['item'])) {
				misc::error404();
			}
			
			$salt = core::tableGet($var['form-table'], "encrypted") ? core::fieldGetVal($var['form-table'], $var['item'], "salt") : "";
			
		} else {
			// New item
			
			$var['item'] = array();
			
			$salt = myCrypt::getSalt();
			
		}
		
		$insert_data = array();
		
		// Get fields
		foreach ($cfg['fields'][$var['form-fields']] as $field_name => $field_data) {
			
			if (isset($field_data['nodb']) || 
					(!isset($input['data'][$field_name]) && $field_data['type'] != "post-process")) {
				continue;
			}
			$field_value = inputClean::clean(isset($input['data'][$field_name]) ? $input['data'][$field_name] : "", isset($field_data['length']) ? $field_data['length'] : 0);
			
			$insert_data[$field_name] = core::fieldVal($var['form-fields'], $field_name, $field_value, $salt);
			
		}
		
		if (empty($insert_data)) {
			return core::resultError("No hay datos que guardar.");
		}
		
		// Common fields
		$insert_data[core::fieldGetName($var['form-fields'], "user_id")] = ($var['form-mode'] == "new") ? $obj['user']->data['user_id'] : core::fieldGetVal($var['form-table'], $var['item'], "user_id");
		$insert_data[core::fieldGetName($var['form-fields'], "date_creation")] = ($var['form-mode'] == "new") ? $var['now'] : core::fieldGetVal($var['form-table'], $var['item'], "date_creation");
		$insert_data[core::fieldGetName($var['form-fields'], "date_modification")] = $var['now'];
		if (core::tableGet($var['form-table'], "encrypted")) {
			$insert_data[core::fieldGetName($var['form-fields'], "salt")] = $salt;
		}
		
		// Check duplicities
		$check_duplicated = core::tableGet($var['form-table'], "check-duplicated");
		if ($check_duplicated != "") {
			
			foreach ($check_duplicated as $n => $f) {
				if ($var['form-mode'] == "new") {
					$ok = core::checkItemDuplicate($var['form-fields'], $var['form-mode'], $f, $insert_data[core::fieldGetName($var['form-fields'], $f)]);
				} else {
					$ok = core::checkItemDuplicate($var['form-fields'], $var['form-mode'], $f, $insert_data[core::fieldGetName($var['form-fields'], $f)], $var['form-item_id']);
				}
				if (!$ok) {
					return core::resultError("<strong>Ya existe un elemento con ese nombre</strong>. Por favor, elija otro.");
				}
			}
			
		}
		
		// Insert or update database
		
		$insert_data_sql = array();
		foreach ($insert_data as $k => $v) {
			array_push($insert_data_sql, $k . "='" . $v . "'");
		}
		
		if ($var['form-mode'] == "new") {
			
			$q = "INSERT INTO " . core::tableGet($var['form-table'], "name") . " SET 
										" . implode(", \n", $insert_data_sql);
			
		} else if ($var['form-mode'] == "edit") {
			
			$q = "UPDATE " .  core::tableGet($var['form-table'], "name") . " SET 
										" . implode(", \n", $insert_data_sql) . " 
									WHERE 
										" . core::tableGet($var['form-table'], "id") . "='" . $var['form-item_id'] . "' 
									ORDER BY " . core::tableGet($var['form-table'], "id") . " ASC 
									LIMIT 1";
			
		}
		
		$r = $obj['db']->query($q);
		if (!$r) {
			return core::resultErrorDB();
		}
		
		if ($var['form-mode'] == "new") {
			$var['form-item_id'] = $obj['db']->insertId();
			$insert_data[core::fieldGetName($var['form-table'], "id")] = $var['form-item_id'];
		}
		
		// Extra actions
		$extra = core::tableGet($var['form-table'], "extra");
		if ($extra != "" && isset($extra['save'])) {
			$extra['save']($insert_data, $var['form-mode'], $var['item']);
		}
		
		// Log action
		admin::logAction($var['form-table'], $var['form-mode'], $var['form-item_id']);
		
		return core::resultOK(array(
			'item' => $var['form-item_id']
		));
		
	}
	
	
	// --------------------------------------------------------------
	// Check functions
	
	// Checks for duplicates existence
	public static function checkItemDuplicate($table, $mode, $field, $value, $item_id="") {
		
		global $cfg, $var, $obj;
		
		$q = "SELECT " . core::fieldGetName($table, "name") . " FROM " . core::tableGet($table, "name") . " 
								WHERE 
									" . (($mode == "edit") ? core::fieldGetName($table, "id") . "!='" . $item_id . "' AND " : "") . " 
									" . core::fieldGetName($table, $field) . "='" . $value . "' AND 
									" . core::fieldGetName($table, "deleted") . "='0' 
								LIMIT 1";
		$r = $obj['db']->query($q);
		
		if ($obj['db']->numRows($r) > 0) {
			return false;
		} else {
			return true;
		}
		
	}
	
	
	// --------------------------------------------------------------
	// Images
	
	// Get an image by id
	public static function imageGet($image_id) {
		
		global $cfg, $var, $obj;
		
		$q = "SELECT * FROM " . core::tableGet("images", "name") . " 
								WHERE 
									" . core::fieldGetName("images", "id") . "='" . $image_id . "' 
									AND " . core::fieldGetName("images", "deleted") . "='0' 
								LIMIT 1";
		$r = $obj['db']->query($q);
		
		if (!$r || $obj['db']->numRows($r) == 0) {
			return false;
		} else {
			return $obj['db']->getAssoc($r);
		}
		
	}
	
	// Return image data 
	public static function imageGetId($image_id, $id="main") {
		
		global $cfg, $var, $obj;
		
		$image = core::imageGet($image_id);
		
		if (!$image) {
			return false;
		}
		
		return core::imageId($image, $id);
		
	}
	
	// Return image data for js use
	public static function imageGetJs($image_id, $id="main") {
		
		global $cfg, $var, $obj;
		
		$image = core::imageGet($image_id);
		
		if (!$image) {
			return false;
		}
			
		$files = json_decode(core::fieldGetVal("images", $image, "files"), true);
		$img = $files[$id];
		
		if (!empty($img)) {
			
			return array(
				'name' => core::fieldGetVal("images", $image, "name"), 
				'alt' => core::fieldGetVal("images", $image, "alt"), 
				'filename' => core::fieldGetVal("images", $image, "filename"), 
				'type' => core::fieldGetVal("images", $image, "type"), 
				'width' => $img['width'], 
				'height' => $img['height']
			);
			
		} else {
			return false;
		}
		
	}
	
	// Return image data 
	public static function imageId($image, $id="main") {
		
		global $cfg, $var, $obj;
			
		$files = json_decode(core::fieldGetVal("images", $image, "files"), true);
		$img = isset($files[$id]) ? $files[$id] : array();
		
		if (!empty($img)) {
			
			return array(
				'name' => core::fieldGetVal("images", $image, "name"), 
				'alt' => core::fieldGetVal("images", $image, "alt"), 
				'filename' => $img['filename'], 
				'type' => $img['format'], 
				'width' => $img['width'], 
				'height' => $img['height']
			);
			
		} else {
			return false;
		}
		
	}
	
	
	// --------------------------------------------------------------
	// Mailings
	
	public static function mailSend($options) {
		
		global $cfg, $var, $obj;
		
		$result = "ok";
		$msg = "";
		
		if (!isset($options['to'])) {
			return core::resultError("Destinatario no especificado");
		}
		if (!isset($options['subject'])) {
			return core::resultError("Asunto no especificado");
		}
		if (!isset($options['content'])) {
			return core::resultError("Contenido no especificado");
		}
		
		require_once($cfg['paths']['shared'] . "scripts/php/libs/phpmailer/class.phpmailer.php");
		require_once($cfg['paths']['shared'] . "scripts/php/libs/phpmailer/class.smtp.php");
		
		$mail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch
		
		$mail->CharSet = "UTF-8";
		$mail->Timeout = $cfg['smtp']['timeout'];
		$mail->IsSMTP(); // telling the class to use SMTP
		
		try {
			
			$mail->SMTPAuth      = (isset($cfg['smtp']['auth'])) ? $cfg['smtp']['auth'] : true;
			if (isset($cfg['smtp']['secure']) && $cfg['smtp']['secure'] != "") {
				$mail->SMTPSecure = $cfg['smtp']['secure'];
			}
			$mail->Host          = $cfg['smtp']['server'];
			$mail->Port          = $cfg['smtp']['port'];
			$mail->Username      = $cfg['smtp']['username'];
			$mail->Password      = $cfg['smtp']['password'];
			
			if (isset($options['from'])) {
				if (strpos($options['from'], ",") !== false) {
					$t = explode(",", $options['from']);
					$mail->SetFrom(trim($t[0]), trim($t[1]));
				} else {
					$mail->SetFrom(trim($options['from']));
				}
			} else {
				if (strpos($cfg['smtp']['from'], ",") !== false) {
					$t = explode(",", $cfg['smtp']['from']);
					$mail->SetFrom(trim($t[0]), trim($t[1]));
				} else {
					$mail->SetFrom($cfg['smtp']['from']);
				}
			}
			
			if (isset($options['replyto'])) {
				if (strpos($options['replyto'], ",") !== false) {
					$t = explode(",", $options['replyto']);
					$mail->AddReplyTo(trim($t[0]), trim($t[1]));
				} else {
					$mail->AddReplyTo(trim($options['replyto']));
				}
			}
			
			$mail->Subject = $options['subject'];
			$mail->AltBody = (isset($options['content_text'])) ? $options['content_text'] : strip_tags($options['content']);
		  $mail->MsgHTML($options['content']);
		  
		  if (is_array($options['to'])) {
	  		foreach ($options['to'] as $n => $d) {
		  		if (strpos($d, ",") !== false) {
						$t = explode(",", $d);
						$mail->AddAddress(trim($t[0]), trim($t[1]));
					} else {
						$mail->AddAddress(trim($d));
					}
		  	}
	  	} else {
	  		if (strpos($options['to'], ",") !== false) {
					$t = explode(",", $options['to']);
					$mail->AddAddress(trim($t[0]), trim($t[1]));
				} else {
					$mail->AddAddress(trim($options['to']));
				}
	  	}
	  	
	  	if (isset($options['ccs'])) {
				if (strpos($options['ccs'], ",") !== false) {
					$t = explode(",", $options['ccs']);
					$mail->AddCC(trim($t[0]), trim($t[1]));
				} else {
					$mail->AddCC(trim($options['ccs']));
				}
			}
			
			if (isset($options['ccos'])) {
				if (strpos($options['ccos'], ",") !== false) {
					$t = explode(",", $options['ccos']);
					$mail->AddBCC(trim($t[0]), trim($t[1]));
				} else {
					$mail->AddBCC(trim($options['ccos']));
				}
			}
		  
		  $mail->Send();
			
		} catch (phpmailerException $e) {
			
			// PHPMailer error 
			$result = "error";
			$msg = $e->errorMessage();
		  
		} catch (Exception $e) {
			
			// Error
			$result = "error";
			$msg = $e->errorMessage();
		  
		}
	  
	  $mail->ClearAllRecipients();
	  
	  return array('result' => $result, 'error_msg' => $msg);
	  
	}
	
	
	// --------------------------------------------------------------
	// Misc
	
	public static function jsLoad() {
		
		global $cfg, $var, $obj;
		
		header("Content-Type: application/javascript");
		
		if (!isset($_GET['script'])) {
			die("");
		}
		
		$script = inputClean::clean($_GET['script'], 16);		
		$filename = $cfg['paths']['shared'] . "scripts/js/" . $script . ".js";
		
		if (!file_exists($filename)) {
			die("");
		}
		
		$fh = fopen($filename, "r");
		$fr = fread($fh, filesize($filename));
		$fc = fclose($fh);
		
		echo $fr;
		die();
		
	}
		
	
	// --------------------------------------------------------------
	// Output handling
	
	// Result messages
	public static function resultOK($options=array()) {
		
		global $cfg, $var, $obj;
		
		$result = array(
			'result' => "ok"
		);
		
		foreach ($options as $k => $v) {
			$result[$k] = $v;
		}
		
		return $result;
		
	}
	
	public static function resultError($msg="", $options=array()) {
		
		global $cfg, $var, $obj;
		
		$result = array(
			'result' => "error"
		);
		
		if ($msg != "") {
			$result['error_msg'] = $msg;
		}
		
		foreach ($options as $k => $v) {
			$result[$k] = $v;
		}
		
		return $result;
		
	}
	
	public static function resultErrorDB() {
		
		global $cfg, $var, $obj;
		
		if ($cfg['app']['test-mode']) {
			
			$text = "<p><strong>Se ha producido un error al acceder a la base de datos:</strong></p>
								<pre>" . $obj['db']->connection->error . "</pre>";
			
		} else {
			
			$text = "<p><strong>Se ha producido un error al acceder a la base de datos:</strong></p>";
			
		}
		
		$text .= "<p>Si el roblema persiste póngase en contacto con el administrador.</p>";
		
		return core::resultError($text);
		
	}
	
	// Json messages
	
	public static function jsonOK($options=array()) {
		
		global $cfg, $var, $obj;
		
		die(misc::jsonEncode(core::resultOK($options)));
		
	}
	
	public static function jsonError($msg="", $options=array()) {
		
		global $cfg, $var, $obj;
		
		die(misc::jsonEncode(core::resultError($msg, $options)));
		
	}
	
	public static function errorFatal($text) {
		
		global $cfg, $vars;
		
		if (APP == "admin") {
			admin::errorFatal($text);
		} else {
			app::errorFatal($text);
		}
		
	}
	
	
	// --------------------------------------------------------------
	// Garbage Collection
	
	public static function garbageCollector() {
		
		global $cfg, $var, $obj;
		
		// Delete session variables for list filters (if not in filter page)
		if (isset($_SESSION['list-filters'])) {
			
			if (!isset($var['page']) || !isset($var['page']['list'])) {
				unset($_SESSION['list-filters']);
			} else {
				if ($_SESSION['list-filters']['id'] != $var['page']['list']['id']) {
					unset($_SESSION['list-filters']);
				}
			}
			
		}
		
		// Close DB connection
		@$obj['db']->close();
		
	}
	
}

?>