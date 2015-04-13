<?php

// Security check
if (!isset($var) && !isset($var['page'])) { die("Access not allowed."); }

// ==============================================================
// Database operations
// --------------------------------------------------------------

if (!isset($_REQUEST['action'])) {
	misc::error404(true);
} else {
	$action = inputClean::clean($_REQUEST['action'], 32);
}

switch ($action) {
	
	// ************************************************************************
	// EDITING FORMS
	// ************************************************************************
	
	// ------------------------------------------------------------
	// Save values of form
	case "form-save":
		
		$action = core::formSave($_POST);
		
		die(misc::jsonEncode($action));
		
	break;
	
	
	// ************************************************************************
	// LIST ITEMS
	// ************************************************************************
	
	// ------------------------------------------------------------
	// Change active value of an item
	case "list-item-active":
		
		$action = admin::itemActivate($_POST);
		
		die(misc::jsonEncode($action));
		
	break;
	
	
	// ------------------------------------------------------------
	// Delete an item
	case "list-item-delete":
		
		$action = admin::itemDelete($_POST);
		
		die(misc::jsonEncode($action));
		
	break;
	
	
	// ------------------------------------------------------------
	// Change order an item
	case "list-item-order":
		
		$action = admin::itemOrder($_POST);
		
		die(misc::jsonEncode($action));
		
	break;
	
	
	// ************************************************************************
	// GET INFO
	// ************************************************************************
	
	// ------------------------------------------------------------
	// Get a list of items
	case "get-list":
		
		if (!isset($_POST['element']) || 
				!isset($_POST['options'])) { misc::error404(); }
		
		$table = inputClean::clean($_POST['element'], 32);
		$options = inputClean::clean($_POST['options']);
		
		$list = core::getList($table, $options);
		
		core::jsonOK(array(
			'list' => $list
		));
		
	break;
	
	
	// ************************************************************************
	// IMAGES
	// ************************************************************************
	
	// ------------------------------------------------------------
	// Load a gallery list
	case "image-gallery-list":
		
		$list = core::getList("images_galleries", array(
			'zero-first' => false
		));
		
		core::jsonOK(array(
			'list' => $list
		));
		
	break;
	
	// ------------------------------------------------------------
	// Save a gallery
	case "image-gallery-edit":
		
		$list = core::getList("images_galleries", array(
			'zero-first' => false
		));
		
		core::jsonOK(array(
			'list' => $list
		));
		
	break;
	
	// ------------------------------------------------------------
	// Load a gallery list
	case "image-list":
		
		$action = admin::imageList($_POST);
		
		die(misc::jsonEncode($action));
		
	break;
	
	// ------------------------------------------------------------
	// Upload a new image
	case "image-upload":
		
		$action = admin::imageUpload($_POST, "f_img_upload");
		
		if ($action['result'] == "error") {
			
			core::jsonError($action['error_msg']);
			
		} else if ($action['result'] == "empty") {
			
			core::jsonError("No hay imagen que subir.");
			
		} else {
			
			die(misc::jsonEncode($action));
			
		}
		
	break;
	
	// ------------------------------------------------------------
	// Load image
	case "image-load":
		
		if (!isset($_POST['image'])) { misc::error404(); }
		
		$image_id = inputClean::clean($_POST['image'], 11);
		
		$image = core::imageGet($image_id);
		
		if ($image !== false) {
			
			core::jsonOK(array(
				'image' => $image
			));
			
		} else {
			
			core::jsonError("La imagen no existe o fue borrada.");
			
		}
		
	break;
	
	// ------------------------------------------------------------
	// Save image
	case "image-save":
		
		if (!isset($_POST['item']) || 
				!isset($_POST['data'])) { misc::error404(); }
		
		$save = admin::imageSave("edit", $_POST['data'], $_POST['item']);
		
		$image = core::getItem("images", $save['item']);
		
		if ($image !== false) {
			
			core::jsonOK(array(
				'image_id' => $save['item'], 
				'image' => core::imageGetJs($save['item'], "thumb")
			));
			
		} else {
			
			core::jsonError("La imagen no existe o fue borrada.");
			
		}
		
	break;
	
	// ------------------------------------------------------------
	// Delete image
	case "image-delete":
		
		if (!isset($_POST['item'])) { misc::error404(); }
		
		$action = admin::imageDelete($_POST['item']);
		
		die(misc::jsonEncode($action));
		
	break;
	
	
	// ************************************************************************
	// FILES
	// ************************************************************************
	
	// ------------------------------------------------------------
	// Upload a new file
	case "file-upload":
		
		$upload = admin::fileUpload($_POST, "f_file_upload");
		
		if ($upload['result'] == "error") {
			
			core::jsonError($upload['error_msg']);
			
		} else if ($upload['result'] == "empty") {
			
			core::jsonError("No hay archivo que subir.");
			
		} else {
			
			die(misc::jsonEncode($upload));
			
		}
		
	break;
	
	
	// ************************************************************************
	// AUTOCOMPLETE
	// ************************************************************************
	
	// ------------------------------------------------------------
	// Search database for initial autocomplete values
	case "autocomplete-init":
		
		$action = admin::autocompleteInit($_POST);
		
		die(misc::jsonEncode($action));
		
	break;
	
	// ------------------------------------------------------------
	// Search database for autocomplete fields
	case "autocomplete-search":
		
		$action = admin::autocompleteSearch($_POST);
		
		die(misc::jsonEncode($action));
		
	break;
	
	
	// ************************************************************************
	// DOWNLOADS
	// ************************************************************************
	
	// ------------------------------------------------------------
	// Download file
	case "download":
		
		$download = admin::download($_POST);
		
	break;
	
	
	default:
		misc::error404();
	break;
	
}

?>