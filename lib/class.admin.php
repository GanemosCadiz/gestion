<?php

class admin extends adminCore {
	
	// Restricted access modules
	public static function getModulesRestricted() {
		
		global $cfg, $var, $obj;
		
		$restricted = array();
		
		foreach ($cfg['modules'] as $k => $data) {
			
			if (isset($data['restricted']) && $data['restricted']) {
				$restricted[$k] = $data['texts']['name'];
			}
			
		}
		
		return $restricted;
		
	}
	
	// Get search query
	public static function getSearchQuery($keywords) {
		
		global $cfg, $var, $obj;
		
		$query = "";
		
		if ($keywords == "" || strlen($keywords) < 3) {
			return "";
		}
		
		$queries = array();
		
		// Categories
		// User query permissions
		$user_permissions = core::userPermissionQuery("categories");
		array_push($queries, "SELECT 'category' AS type, 
																	" . core::fieldGetName("categories", "id") . " AS id, 
																	" . core::fieldGetName("categories", "name") . " AS name, 
																	" . core::fieldGetName("categories", "date_creation") . " AS date, 
																	" . core::fieldGetName("categories", "user_id") . " AS user_id, 
																	" . core::fieldGetName("categories", "active") . " AS active 
																FROM " . core::tableGetName("categories") . " 
																WHERE 
																	" . core::fieldGetName("categories", "deleted") . "='0' AND 
																	(" . core::fieldGetName("categories", "name") . " LIKE '%" . $keywords . "%') 
																	" . ($user_permissions != "" ? " AND (" . $user_permissions . ") " : ""));
		// Users
		// User permissions
		if (in_array($obj['user']->data['type'], array("root", "admin"))) {
			array_push($queries, "SELECT 'user' AS type, 
																		" . core::fieldGetName("users", "id") . " AS id, 
																		" . core::fieldGetName("users", "name") . " AS name, 
																		" . core::fieldGetName("users", "date_creation") . " AS date, 
																		" . core::fieldGetName("users", "user_id") . " AS user_id, 
																		" . core::fieldGetName("users", "active") . " AS active 
																	FROM " . core::tableGetName("users") . " 
																	WHERE 
																		" . core::fieldGetName("users", "deleted") . "='0' AND 
																		(" . core::fieldGetName("users", "name") . " LIKE '%" . $keywords . "%') ");
		}
		
		// Build query
		$query = "(" . implode(") UNION (", $queries) . ")";
		
		return $query;
		
	}
	
}

?>