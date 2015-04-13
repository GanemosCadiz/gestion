<?php

class adminCore {
	
	// ==============================================================
	// Core functions declaration file
	// --------------------------------------------------------------
	
	
	// --------------------------------------------------------------
	// User control
	
	// Check if user can write
	public static function userAllowWrite() {
		
		global $cfg, $var, $obj;
		
		// Check user object & restricted access
		if (!core::userCheckLogged()
				|| $obj['user']->data['type'] == "readonly" 
				|| !$obj['user']->data['permissions']['write']) {
			return false;
		}
		
		return true;
		
	}
	
	// Generate user permissions
	public static function userPermissionsCreate($post_data) {
		
		global $cfg, $var, $obj;
		
		$permissions = $cfg['defaults']['user-permissions'];
		
		// Modules
		$permissions['modules'] = (isset($post_data['permissions-modules']) && $post_data['permissions-modules'] != "") ? explode(",", trim(inputClean::clean($post_data['permissions-modules']))) : array();
		// Sites
		$permissions['sites'] = (isset($post_data['permissions-sites']) && $post_data['permissions-sites'] != "") ? explode(",", trim(inputClean::clean($post_data['permissions-sites']))) : "all";
		// Categories
		$permissions['categories'] = (isset($post_data['permissions-categories']) && $post_data['permissions-categories'] != "") ? explode(",", trim(inputClean::clean($post_data['permissions-categories']))) : "all";
		// Noauthor
		$permissions['noauthor'] = (isset($post_data['permissions-noauthor']) && $post_data['permissions-noauthor'] == "1") ? true : false;
		
		// User types
		switch ($post_data['type']) {
			
			case "admin":
				$permissions['modules'] = "all";
				$permissions['sites'] = "all";
				$permissions['categories'] = "all";
				$permissions['noauthor'] = true;
				$permissions['write'] = true;
			break;
			
			case "user":
				$permissions['write'] = true;
			break;
			
			case "readonly":
				$permissions['write'] = false;
			break;
			
			case "download":
				$permissions['modules'] = "download";
				$permissions['noauthor'] = true;
				$permissions['write'] = false;
			break;
			
		}
		
		return $permissions;
		
	}
	
	
	// --------------------------------------------------------------
	// Get Info
	
	// Get list of sites ids of an author
	public static function getAuthorSites($author_id) {
		
		global $cfg, $var, $obj;
		
		$q = "SELECT " . fieldGetName("sites", "id") . " FROM " . tableGetName("sites") . " 
								WHERE 
									" . fieldGetName("sites", "user_id") . "='" . $author_id . "' AND 
									" . fieldGetName("sites", "deleted") . "='0' 
								ORDER BY " . fieldGetName("sites", "id") . " ASC";
		$r = $obj['db']->query($q);
		
		$list = array();
		while ($f = $obj['db']->assoc($r)) {
			array_push($list, core::fieldGetVal("sites", $f, "id"));
		}
		
		return $list;
		
	}
	
	
	// --------------------------------------------------------------
	// List Items
	
	public static function itemActivate($input) {
		
		global $cfg, $var, $obj;
		
		// Don't allow readonly users
		if (!admin::userAllowWrite()) {
			misc::error404();
		}
		
		if (!isset($input['element']) || 
				!isset($input['item']) || 
				!isset($input['value'])) { misc::error404(); }
		
		$table = inputClean::clean($input['element'], 32);
		$item_id = inputClean::clean($input['item'], 11);
		$value = inputClean::clean($input['value'], 1);
		
		// Get item from database
		$var['item'] = core::getItem($table, $item_id);
		
		if (!$var['item']) {
			// Item not found
			return core::resultError("El elemento no existe o ya ha sido borrado.");
		}
		
		// Check user access to content
		if (!core::userAllowContent($table, $var['item'])) {
			misc::error404();
		}
		
		// Activation / deactivation
		$q = "UPDATE " . core::tableGet($table, "name") . " SET 
									" . core::fieldGetName($table, "active") . "='" . $value . "', 
									" . core::fieldGetName($table, "date_modification") . "='" . $var['now'] . "' 
								WHERE 
									" . core::fieldGetName($table, "id") . "='" . $item_id . "' 
								LIMIT 1";
		$r = $obj['db']->query($q);
		if (!$r) {
			return core::resultErrorDB();
		}
		
		// Extra actions
		$extra = core::tableGet($table, "extra");
		if ($extra != "" && isset($extra['activate'])) {
			$extra['activate']($var['item'], $value);
		}
		
		// Set action message
		$_SESSION['list-action'] = array(
			'action' => "activate", 
			'value' => $value, 
			'item' => $var['item']
		);
		
		// Log action
		admin::logAction($table, "activation: ".$value, $item_id);
		
		return core::resultOK();
		
	}
	
	public static function itemDelete($input) {
		
		global $cfg, $var, $obj;
		
		// Don't allow readonly users
		if (!admin::userAllowWrite()) {
			misc::error404();
		}
		
		if (!isset($input['element']) || 
				!isset($input['item'])) { misc::error404(); }
		
		$table = inputClean::clean($input['element'], 32);
		$item_id = inputClean::clean($input['item'], 11);
		
		// Get item from database
		$var['item'] = core::getItem($table, $item_id);
		
		if (!$var['item']) {
			// Item not found
			return core::resultError("El elemento no existe o ya ha sido borrado.");
		}
		
		// Check user access to content
		if (!core::userAllowContent($table, $var['item'])) {
			misc::error404();
		}
		
		// Delete item
		$q = "UPDATE " . core::tableGet($table, "name") . " SET 
									" . core::fieldGetName($table, "deleted") . "='1', 
									" . core::fieldGetName($table, "date_modification") . "='" . $var['now'] . "' 
								WHERE 
									" . core::fieldGetName($table, "id") . "='" . $item_id . "' 
								LIMIT 1";
		$r = $obj['db']->query($q);
		if (!$r) {
			return core::resultErrorDB();
		}
		
		// Extra actions
		$extra = core::tableGet($table, "extra");
		if ($extra != "" && isset($extra['delete'])) {
			$extra['delete']($var['item']);
		}
		
		// Set action message
		$_SESSION['list-action'] = array(
			'action' => "delete", 
			'item' => $var['item']
		);
		
		// Log action
		admin::logAction($table, "delete", $item_id);
		
		return core::resultOK();
		
	}
	
	public static function itemOrder($input) {
		
		global $cfg, $var, $obj;
		
		// Don't allow readonly users
		if (!admin::userAllowWrite()) {
			misc::error404();
		}
		
		if (!isset($input['element']) || 
				!isset($input['item']) || 
				!isset($input['mode'])) { misc::error404(); }
		
		$table = inputClean::clean($input['element'], 32);
		$item_id = inputClean::clean($input['item'], 11);
		$mode = inputClean::clean($input['mode'], 32);
		$newvalue = isset($input['value']) ? inputClean::clean($input['value'], 6) : 0;
		
		// Get item from database
		$var['item'] = core::getItem($table, $item_id);
		
		if (!$var['item']) {
			// Item not found
			return core::resultError("El elemento no existe o ya ha sido borrado.");
		}
		
		// Check user access to content
		if (!core::userAllowContent($table, $var['item'])) {
			misc::error404();
		}
		
		$lastorder = admin::itemOrderLast($table);
		
		switch ($mode) {
			
			case "new":
				// -----------------------
				// New item, last order
				
				$neworder = $lastorder + 1;
				// Update order
				$q = "UPDATE " . core::tableGet($table, "name") . " SET 
											" . core::fieldGetName($table, "order") . "='" . $neworder . "' 
										WHERE 
											" . core::fieldGetName($table, "id") . "='" . $item_id . "' 
										LIMIT 1";
				$r = $obj['db']->query($q);
				if (!$r) {
					return core::resultErrorDB();
				}
				
			break;
			
			case "up":
			case "down":
				// -----------------------
				// Order up / dowm
				
				$actual = core::fieldGetVal($table, $var['item'], "order");
				
				if ($mode == "up") {
					if ($actual == 1) { return true; }
					$neworder = $actual - 1;
				} else if ($mode == "down") {
					if ($actual >= $lastorder) { return true; }
					$neworder = $actual + 1;
				}
				$q = "UPDATE " . core::tableGet($table, "name") . " SET 
											" . core::fieldGetName($table, "order") . "='" . $actual . "' 
										WHERE 
											" . core::fieldGetName($table, "order") . "='" . $neworder . "' 
										LIMIT 1";
				$r = $obj['db']->query($q);
				if (!$r) {
					return core::resultErrorDB();
				}
				// Update order
				$q = "UPDATE " . core::tableGet($table, "name") . " SET 
											" . core::fieldGetName($table, "order") . "='" . $neworder . "' 
										WHERE 
											" . core::fieldGetName($table, "id") . "='" . $item_id . "' 
										LIMIT 1";
				$r = $obj['db']->query($q);
				if (!$r) {
					return core::resultErrorDB();
				}
				
			break;
			
			case "neworder":
				// -----------------------
				// Set order value
				
				$actual = core::fieldGetVal($table, $var['item'], "order");
				if ($newvalue < 1) {
					$newvalue = 1;
				} else if ($newvalue > $lastorder) {
					$newvalue = $lastorder;
				}
				// Pull up elements behind actual
				$q = "UPDATE " . core::tableGet($table, "name") . " SET 
											" . core::fieldGetName($table, "order") . "=" . core::fieldGetName($table, "order") . "-1 
										WHERE 
											" . core::fieldGetName($table, "order") . ">'" . $actual . "'";
				$r = $obj['db']->query($q);
				if (!$r) {
					return core::resultErrorDB();
				}
				// Push down elements behind newpos
				$q = "UPDATE " . core::tableGet($table, "name") . " SET 
											" . core::fieldGetName($table, "order") . "=" . core::fieldGetName($table, "order") . "+1 
										WHERE 
											" . core::fieldGetName($table, "order") . ">='" . $newvalue . "'";
				$r = $obj['db']->query($q);
				if (!$r) {
					return core::resultErrorDB();
				}
				// Update order
				$q = "UPDATE " . core::tableGet($table, "name") . " SET 
											" . core::fieldGetName($table, "order") . "='" . $newvalue . "' 
										WHERE 
											" . core::fieldGetName($table, "id") . "='" . $item_id . "' 
										LIMIT 1";
				$r = $obj['db']->query($q);
				if (!$r) {
					return core::resultErrorDB();
				}
				
			break;
			
		}
		
		// Set action message
		$_SESSION['list-action'] = array(
			'action' => "order", 
			'item' => $var['item']
		);
		
		return core::resultOK();
		
	}
	
	public static function itemOrderLast($table) {
		
		global $cfg, $var, $obj;
		
		$q = "SELECT " . core::fieldGetName($table, "order") . " FROM " . core::tableGet($table, "name") . " 
								WHERE 
									" . core::fieldGetName($table, "deleted") . "='0' 
								ORDER BY 
									" . core::fieldGetName($table, "order") . " DESC 
								LIMIT 1";
		$r = $obj['db']->query($q);
		if (!$r) {
			core::jsonError("Error accediendo a base de datos.");
		}
		$last = $obj['db']->numRows($r) == 0 ? 0 : intval($obj['db']->getValues($r));
		
		return $last;
		
	}
	
	
	// --------------------------------------------------------------
	// Autocomplete
	
	public static function autocompleteInit($input) {
		
		global $cfg, $var, $obj;
		
		if (!isset($input['element']) || 
				!isset($input['value'])) { misc::error404(); }
		
		$table = inputClean::clean($input['element'], 32);
		$value = explode(",", inputClean::clean($input['value']));
		
		if (core::tableGet($table, "name") == "") {
			misc::error404();
		}
		
		$q = "SELECT " . core::tableGet($table, "id") . ", " . core::fieldGetName($table, "name") . " FROM " . core::tableGet($table, "name") . " 
								WHERE 
									" . core::fieldGetName($table, "id") . " IN ('" . implode("','", $value) . "') AND 
									" . core::fieldGetName($table, "deleted") . "='0' 
								ORDER BY " . core::fieldGetName($table, "name") . " ASC";
		$r = $obj['db']->query($q);
		if ($r) {
			return core::resultErrorDB();
		}
		
		$list = array();
		
		while ($i = $obj['db']->assoc($r)) {
			$list[$i[core::tableGet($table, "id")]] = core::fieldGetVal($table, $i, "name");
		}
		
		return core::resultOK(array(
			'list' => $list
		));
		
	}
	
	public static function autocompleteSearch($input) {
		
		global $cfg, $var, $obj;
		
		if (!isset($input['element']) || 
				!isset($input['value'])) { misc::error404(); }
		
		$table = inputClean::clean($input['element'], 32);
		$value = inputClean::clean($input['value'], 256);
		
		if (core::tableGet($table, "name") == "") {
			misc::error404();
		}
		
		$q = "SELECT " . core::tableGet($table, "id") . ", " . core::fieldGetName($table, "name") . " FROM " . core::tableGet($table, "name") . " 
								WHERE 
									" . core::fieldGetName($table, "name") . " LIKE '%" . $value . "%' AND 
									" . core::fieldGetName($table, "deleted") . "='0' 
								ORDER BY " . core::fieldGetName($table, "name") . " ASC";
		$r = $obj['db']->query($q);
		if (!$r) {
			return core::resultErrorDB();
		}
		
		$list = array();
		
		while ($i = $obj['db']->assoc($r)) {
			$list[$i[core::tableGet($table, "id")]] = core::fieldGetVal($table, $i, "name");
		}
		
		return core::resultOK(array(
			'list' => $list
		));
		
	}
	
	
	// --------------------------------------------------------------
	// Images
	
	public static function imageList($input) {
		// Upload an image
		
		global $cfg, $var, $obj;
		
		if (!isset($input['gallery'])) { misc::error404(); }
		
		$gallery = inputClean::clean($input['gallery'], 11);
		
		$user_permissions = core::userPermissionQuery("images");
		
		$q = "SELECT * FROM " . core::tableGet("images", "name") . " 
								WHERE 
									" . core::fieldGetName("images", "gallery_id") . "='" . $gallery . "' 
									AND " . core::fieldGetName("images", "active") . "='1' 
									AND " . core::fieldGetName("images", "deleted") . "='0' 
									" . ($user_permissions != "" ? " AND (" . $user_permissions . ") " : "") . " 
								ORDER BY " . core::fieldGetName("images", "id") . " DESC";
		$r = $obj['db']->query($q);
		if (!$r) {
			return core::resultErrorDB();
		}
		
		$list = array();
		
		while ($i = $obj['db']->assoc($r)) {
			
			$main = core::imageId($i, "main");
			$thumb = core::imageId($i, "thumb");
			
			$list[core::fieldGetVal("images", $i, "id")] = array(
				'id' => core::fieldGetVal("images", $i, "id"), 
				'name' => core::fieldGetVal("images", $i, "name"), 
				'gallery' => core::fieldGetVal("images", $i, "gallery_id"), 
				'main' => array(
												'filename' => $main['filename'], 
												'width' => $main['width'], 
												'height' => $main['height']
												), 
				'thumb' => array(
												'filename' => $thumb['filename'], 
												'width' => $thumb['width'], 
												'height' => $thumb['height']
												)
			);
			
		}
		
		return core::resultOK(array(
			'list' => $list
		));
		
	}
	
	public static function imageUpload($input, $filename) {
		// Upload an image
		
		global $cfg, $var, $obj;
		
		// Don't allow readonly users
		if (!admin::userAllowWrite()) {
			misc::error404();
		}
		
		if (!isset($input['gallery']) || 
				!isset($input['outputs'])) { misc::error404(); }
		
		$gallery = inputClean::clean($input['gallery'], 11);
		$outputs = inputClean::clean($input['outputs'], 32);
		
		// Upload image
		$upload = new uploadImage($cfg['images']['defaults']);
		if (isset($cfg['images']['outputs'][$outputs])) {
			$upload->file_outputs = $cfg['images']['outputs'][$outputs];
		}
		$result = $upload->go($filename);
		
		if ($result === false) {
			
			return core::resultError($upload->error_msg);
			
		} else {
			
			if (empty($upload->filedata_output)) {
				
				return array(
					'result' => "empty"
				);
				
			} else {
				
				// Insert image
				
				$img = $upload->filedata_output['main'];
				
				$data = array(
					core::fieldGetName("images", "gallery_id") => $gallery, 
					core::fieldGetName("images", "name") => $cfg['images']['default-name'], 
					core::fieldGetName("images", "filename") => $img['name'], 
					core::fieldGetName("images", "type") => $img['extension'], 
					core::fieldGetName("images", "files") => stripcslashes(misc::jsonEncode($upload->filedata_output)), 
					core::fieldGetName("images", "alt") => $cfg['images']['default-alt'], 
					core::fieldGetName("images", "user_id") => $obj['user']->data['user_id'], 
					core::fieldGetName("images", "date_creation") => $var['now'], 
					core::fieldGetName("images", "date_modification") => $var['now'], 
					core::fieldGetName("images", "active") => 1, 
					core::fieldGetName("images", "deleted") => 0
				);
				
				$save = admin::imageSave("new", $data);
				
				if ($save['result'] == "ok") {
					
					// Log action
					admin::logAction("images", "upload: " . $img['filename'], $save['item']);
					
					return core::resultOK(array(
						'image_id' => $save['item'], 
						'image' => core::imageGetJs($save['item'], "thumb")
					));
					
				} else {
					
					return core::resultError("No se pudo guardar la imagen.");
					
				}
				
			}
			
		}
		
	}
	
	public static function imageSave($mode, $data, $item_id=0) {
		// Save an image
		
		global $cfg, $var, $obj;
		
		// Don't allow readonly users
		if (!admin::userAllowWrite()) {
			misc::error404();
		}
		
		$input = array(
			'element' => "images", 
			'mode' => $mode, 
			'item' => $item_id, 
			'data' => $data
		);
		
		return core::formSave($input);
		
	}
	
	public static function imageRename($new_image, $old_image) {
		
		global $cfg, $var, $obj;
		
		$filename_new = core::fieldGetVal("images", $new_image, "filename");
		$filename_old = core::fieldGetVal("images", $old_image, "filename");
		
		if ($filename_old != "" && $filename_new != $filename_old) {
			
			// Check name duplicity
			$ok = core::checkItemDuplicate("images", "edit", "filename", $filename_new, core::fieldGetVal("images", $old_image, "id"));
			
			if (!$ok) {
				core::jsonError("Ya existe una imagen con ese nombre de archivo.");
			}
			
			// Rename files
			$files = json_decode(core::fieldGetVal("images", $old_image, "files"), true);
			foreach ($files as $code => $image) {
				$new_path_full = $image['path'] . $image['prefix'] . $filename_new . $image['suffix'] . "." . $image['extension'];
				$rename = rename($image['path_full'], $new_path_full);
				if (!$rename) {
					core::jsonError("Error renombrando archivo: " . $image['prefix'] . $filename_new . $image['suffix'] . "." . $image['extension']);
				}
				$files[$code]['name'] = $filename_new;
				$files[$code]['filename'] = $image['prefix'] . $filename_new . $image['suffix'] . "." . $image['extension'];
				$files[$code]['path_full'] = $new_path_full;
			}
			
			// Update files
			$q = "UPDATE " . core::tableGet("images", "name") . " 
									SET 
										" . core::fieldGetName("images", "files") . "='" . inputClean::clean(stripcslashes(misc::jsonEncode($files))) . "' 
									WHERE 
										" . core::fieldGetName("images", "id") . "='" . core::fieldGetVal("images", $old_image, "id") . "' 
									LIMIT 1";
			$r = $obj['db']->query($q);
			
			if (!$r) {
				core::jsonError("Error al actualizar ficheros.");
			}
			
		}
		
		return true;
		
	}
	
	public static function imageDelete($image_id) {
		
		global $cfg, $var, $obj;
		
		// Don't allow readonly users
		if (!admin::userAllowWrite()) {
			misc::error404();
		}
		
		$image_id = inputClean::clean($image_id, 11);
		
		$image = core::getItem("images", $image_id);
		
		if (empty($image)) {
			return core::resultError("La imagen no existe o fue borrada");
		}
		
		// Delete files
		$files = json_decode(core::fieldGetVal("images", $image, "files"), true);
		foreach ($files as $code => $image) {
			@unlink($files[$code]['path_full']);
		}
		
		// Delete form database
		$q = "UPDATE " . core::tableGet("images", "name") . " 
								SET 
									" . core::fieldGetName("images", "deleted") . "='1' 
								WHERE 
									" . core::fieldGetName("images", "id") . "='" . $image_id . "' 
								LIMIT 1";
		$r = $obj['db']->query($q);
		if (!$r) {
			return core::resultErrorDB();
		}
		
		return core::resultOK(array(
				'image_id' => $image_id
		));
		
	}
	
	
	// --------------------------------------------------------------
	// Galleries
	
	public static function imageGalleryDelete($item) {
		
		global $cfg, $var, $obj;
		
		$q = "SELECT * FROM " . core::tableGet("images", "name") . " 
								WHERE 
									" . core::fieldGetName("images", "gallery_id") . "='" . core::fieldGetVal("images_gallery", $item, "id") . "' 
									AND " . core::fieldGetName("images", "deleted") . "='0' 
								ORDER BY " . core::fieldGetName("images", "id") . " ASC";
		$r = $obj['db']->query($q);
		
		while ($i = $obj['db']->assoc($r)) {
			admin::imageDelete(core::fieldGetVal("images", $i, "id"));
		}
		
		return core::resultOK();
		
	}
	
	
	// --------------------------------------------------------------
	// Files
	
	public static function fileUpload($input, $filename) {
		// Upload an image
		
		global $cfg, $var, $obj;
		
		// Don't allow readonly users
		if (!admin::userAllowWrite()) {
			misc::error404();
		}
		
		if (!isset($input['outputs'])) { misc::error404(); }
		
		$outputs = inputClean::clean($input['outputs'], 32);
		
		// Upload file
		if (!isset($cfg['file-outputs'][$outputs])) {
			return core::resultError("No se pudo subir el archivo.");
		}
		$upload = new uploadFile($cfg['file-outputs'][$outputs]);
		$result = $upload->go($filename);
		
		if (!$result) {
			
			return core::resultError($upload->error_msg);
			
		} else {
			
			if (empty($upload->filedata_output)) {
				
				return array(
					'result' => "empty"
				);
				
			}
			
			// Log action
			admin::logAction("fileUpload", "upload: " . $upload->filedata_output['filename'], "");
			
			return core::resultOK(array(
				'filename' => $upload->filedata_output['filename']
			));
			
		}
		
	}
	
	
	// --------------------------------------------------------------
	// Logs
	
	public static function logAction($table, $action, $item) {
		
		global $cfg, $var, $obj;
		
		$encrypted = core::tableGet("log_admin", "encrypted");
		$salt = ($encrypted != "" && $encrypted) ? myCrypt::getSalt() : "";
		
		$target = core::fieldVal("log_admin", "target", $table, $salt);
		$action = core::fieldVal("log_admin", "action", $action, $salt);
		$item = core::fieldVal("log_admin", "item", $item, $salt);
		$ip = core::fieldVal("log_admin", "ip", misc::getIP(), $salt);
		
		$q = "INSERT INTO " . core::tableGet("log_admin", "name") . " SET 
									" . core::fieldGetName("log_admin", "target") . "='" . $target . "', 
									" . core::fieldGetName("log_admin", "action") . "='" . $action . "', 
									" . core::fieldGetName("log_admin", "item") . "='" . $item . "', 
									" . core::fieldGetName("log_admin", "ip") . "='" . $ip . "', 
									" . core::fieldGetName("log_admin", "user_id") . "='" . (isset($obj['user']) ? $obj['user']->user_id : "") . "', 
									" . core::fieldGetName("log_admin", "date") . "='" . $var['now'] . "', 
									" . core::fieldGetName("log_admin", "salt") . "='" . $salt . "'";
		$r = $obj['db']->query($q);
		
		return $r;
		
	}
	
	
	// --------------------------------------------------------------
	// Downloads
	
	public static function download($input) {
		
		global $cfg, $var, $obj;
		
		if (!isset($input['type']) || 
				!isset($input['filters'])) { misc::error404(); }
		
		$type = inputClean::clean($input['type'], 32);
		$filters = inputClean::clean(json_decode($input['filters'], true));
		
		if (!isset($cfg['downloads']['list'][$type])) {
			misc::error404();
		}
		
		set_time_limit(600);
		ini_set("memory_limit", "-1");
		
		$start = microtime(true);
		
		$table = $cfg['downloads']['list'][$type]['table'];
		$wheres = !empty($cfg['downloads']['list'][$type]['query-where']) ? array($cfg['downloads']['list'][$type]['query-where']) : array();
		$order = $cfg['downloads']['list'][$type]['query-order'];
		
		if (!empty($filters)) {
				
			foreach ($filters as $field => $field_data) {
				
				if ($field_data['type'] == "checkbox") {
					
					$t = array();
					foreach ($field_data['list'] as $v => $txt) {
						array_push($t, $field . " LIKE '%" . $v . "%'");
					}
					
					array_push($wheres, implode(" OR ", $t));
					
				} else if ($field_data['type'] == "text" || $field_data['type'] == "number") {
					
					if (isset($field_data['list']['from'])) {
						$t = explode(" ", $field_data['list']['from']);
						$last = $t[count($t)-1];
						if (strpos($last, "/") !== false) {
							$v = date::toDB($t[count($t)-1]) . " 00:00:00";
						} else {
							$v = $last;
						}
						array_push($wheres, $field . ">='" . $v . "'");
					}
					
					if (isset($field_data['list']['to'])) {
						$t = explode(" ", $field_data['list']['to']);
						$last = $t[count($t)-1];
						if (strpos($last, "/") !== false) {
							$v = date::toDB($t[count($t)-1]) . " 23:59:59";
						} else {
							$v = $last;
						}
						array_push($wheres, $field . "<='" . $v . "'");
					}
				}
			}
		}
		
		$wheres = (!empty($wheres)) ? " WHERE (" . implode(") AND \n(", $wheres) . ") " : "";
		
		// Get columns list
		$q = "SHOW COLUMNS FROM " . core::tableGet($table, "name");
		$r = $obj['db']->query($q);
		
		$columns = $obj['db']->getAssoc($r);
		
		// Count elements
		$q = "SELECT COUNT(*) AS num FROM " . core::tableGet($table, "name") . $wheres . " ORDER BY " . $order;
		$r = $obj['db']->query($q);
		$total = $obj['db']->getValues($r);
		
		$continue = true;
		$limit = 0;
		
		// Download file
		$filename = $type . "_" . (($wheres == "") ? "complete" : "filtered") . "_" . date::dateDB() . ".csv";
		if (file_exists($cfg['paths']['admin'] . $cfg['folders']['downloads'] . $filename)) {
			unlink($cfg['paths']['admin'] . $cfg['folders']['downloads'] . $filename);
		}
		
		// Create output
		$output = "";
		$header = "";
		
		// Header
		foreach ($columns as $n => $column) {
			$header .= 	$cfg['downloads']['field-delimiter'] . 
									addslashes($column['Field']) . 
									$cfg['downloads']['field-delimiter'] . 
									$cfg['downloads']['field-separator'];
		}
		$header .= $cfg['downloads']['linebreaker'];
		
		$fh = fopen($cfg['paths']['admin'] . $cfg['folders']['downloads'] . $filename, "a");
		$fw = fwrite($fh, $header);
		$fc = fclose($fh);
		
		$cont = 1;
		
		while ($continue) {
			
			$r = null; unset($r);
			if (!isset($output)) { $output = ""; }
			
			// Build query
			$q = "SELECT * FROM " . core::tableGet($table, "name") . $wheres . " ORDER BY " . $order . " LIMIT " . $limit . ", " . $cfg['downloads']['loop-limit'];
			$r = $obj['db']->query($q);
			
			if ($obj['db']->numRows($r) == 0) {
				
				$continue = false;
				
			} else {
				
				while ($item = $obj['db']->getAssoc($r)) {
					
					foreach ($columns as $n => $column) {
						
						$value = core::fieldGetVal($table, $item, $column['Field']);
						
						if (misc::isJson($value)) {
							
						}
						
						$output .= 	$cfg['downloads']['field-delimiter'] . 
												addslashes(utf8_decode($value)) . 
												$cfg['downloads']['field-delimiter'] . 
												$cfg['downloads']['field-separator'];
						
					}
					
					$output .= $cfg['downloads']['linebreaker'];
					
				}
				
				$fh = fopen($cfg['paths']['admin'] . $cfg['folders']['downloads'] . $filename, "a");
				$fw = fwrite($fh, $output);
				$fc = fclose($fh);
				
				$limit += $cfg['downloads']['loop-limit'];
				$cont++;
				
			}
			
			$output = null;
			unset($output);
			
		}
		
		header('Content-Description: File Transfer');
		header('Content-Type: application/csv');
		header('Content-Disposition: attachment; filename='.basename($filename));
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($cfg['paths']['admin'] . $cfg['folders']['downloads'] . $filename));
		ob_clean();
		flush();
		
		//read the file from disk and output the content.
		readfile($cfg['paths']['admin'] . $cfg['folders']['downloads'] . $filename);
		
		@unlink($cfg['paths']['admin'] . $cfg['folders']['downloads'] . $filename);
		exit;
		
	}
	
	
	// --------------------------------------------------------------
	// Output handling
	
	// Display fatal error page
	public static function errorFatal($text) {
		
		global $cfg, $var, $obj;
		
		$var['error'] = true;
		
		include_once("lib/html.head.php");
		include_once("lib/html.header.php");
		
		echo "<section id=\"error\" class=\"panel panel-danger panel-main\">
						<div class=\"panel-heading\">
	    				<h3 class=\"panel-title\"><i class=\"glyphicon glyphicon-exclamation-sign\"></i> Error</h3>
	  				</div>
	  				<div class=\"panel-body\">
							" . $text . "
							<div class=\"links\">
								<a href=\"" . $cfg['urls']['app'] . "home/\" title=\"Ir a la página de inicio\" class=\"btn btn-primary\"><i class=\"glyphicon glyphicon-circle-arrow-left\"></i> Ir a página de inicio</a>
							</div>
						</div>
					</section>";
		
		include_once("lib/html.footer.php");
		
		die();
		
	}
	
	// Display login error page
	public static function errorLogin($text) {
		
		global $cfg, $var, $obj;
		
		$var['error'] = true;
		
		include_once("lib/html.head.php");
		include_once("lib/html.header.php");
		
		echo "<section id=\"error\" class=\"panel panel-danger panel-main\">
						<div class=\"panel-heading\">
	    				<h3 class=\"panel-title\"><i class=\"glyphicon glyphicon-exclamation-sign\"></i> Error</h3>
	  				</div>
	  				<div class=\"panel-body\">
							" . $text . "
							<div class=\"links\">
								<a href=\"" . $cfg['urls']['app'] . "login/\" title=\"Ir a la página de inicio\" class=\"btn btn-primary\"><i class=\"glyphicon glyphicon-circle-arrow-left\"></i> Ir a página de inicio</a>
							</div>
						</div>
					</section>";
		
		include_once("lib/html.footer.php");
		
		die();
		
	}
	
}

?>