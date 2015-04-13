<?php

/**
 * uploadFile
 * 
 * @package 26horas Shared
 * @author Pablo Fernandez - pablo.fernandez@26horas.com
 * @copyright 2015 Pablo Fernandez
 * @version 0.3 // 2015-02-09
 * @access public
 */

/**
 * Changelog:
 * 
 * 2015-02-09:	New PHP 5 format.
 * 2014-02-19:	Fixed autoenum bug.
 * 2013-12-18:	English translation and minimum corrections.
 * 2010-01-22:	First version
 * 
 */

class uploadFile {
	
	// Default configuration
	private $config = array(
		// Default file values
		'path' => "", // Default absolute or relative path where files will be stored.
		'path-temp' => "", // Default absolute or relative path where temporary files will be stored (if default system temp path fails).
		'name' => "", // Final name of file (without extension). If left blank, source file name will be used.
		'prefix' => "", // Prefix to be added before file name.
		'suffix' => "", // Suffix to be added to the end of the file name.
		// General settings
		'overwrite' => false, // Indicates whenever to overwrite files after upload (true) or not (false).
		'accepted-extensions' => array(), // Accepted extensions (lowercase). If empty, all extensions will be accepted.
		'accepted-formats' => array(), // Accepted formats. If empty, all formats will be accepted.
		'max-size' => 0 // Maximum size of accepted files in kb (0 for no limit or system limit).
	);
	
	// Output vars
	public $error_msg = ""; // Text that will describe the error. Blank if no errors.
	public $filedata_output = array(); // Info about final file.
	public $filedata_source = array(); // Info about source file.
	
	/**
	 * uploadImage::__construct()
	 * 
	 * @param mixed $options
	 * @return void
	 */
	public function __construct($options=array()) {
		
		// ----------------------------------------------------------------
		// Class constructor
		
		foreach ($this->config as $k => $v) {
			$this->config[$k] = $this->setVar($options, $k, $v);
		}
		
		// Temp path
		$this->config['path-temp'] = $this->config['path-temp'] != "" ? $this->config['path-temp'] : sys_get_temp_dir();
		
	}
	
	/**
	 * uploadImage::setVar()
	 * 
	 * @param mixed $options
	 * @param mixed $option
	 * @param mixed $default
	 * @param string $type
	 * @return
	 */
	private function setVar($options, $option, $default, $type="") {
		
		if (is_array($options) && isset($options[$option])) {
			if ($type != "") {
				if (gettype($options[$option]) == $type) {
					return $options[$option];
				}
			} else {
				return $options[$option];
			}
		}
		
		return $default;
		
	}
	
	/**
	 * uploadFile::upload()
	 * 
	 * @param mixed $field_name
	 * @return
	 */
	public function go($field_name) {
		// ----------------------------------------------------------------
		// Executes file upload
		// Options required: field_name
	 	
	 	// Normalize vars
	 	if (substr($this->config['path'], -1) != "/") {
	 		$this->config['path'] .= "/";
	 	}
	 	
	 	// Check if file was uploaded
	 	if (!isset($_FILES) || !isset($_FILES[$field_name]) || !is_array($_FILES[$field_name]) || !$_FILES[$field_name]['name']) {
	 		// No file to upload
	    return true;
		}
		
		// Assign file handler
		$file = $_FILES[$field_name];
	  
	  // Check if target path exists
	  if (!@file_exists($this->config['path'])) {
	  	$this->errorMsg("Target path doesn't exists");
	    return false;
	  }
		
		// Get extension and check if it is accepted
		$extension = (strpos($file['name'], ".") === false) ? "" : strtolower(substr(strrchr($file['name'], "."), 1));
		if (!empty($this->config['accepted-extensions'])) {
			if (!in_array($extension, $this->config['accepted-extensions'])) {
				// Extension not accepted
				$this->errorMsg("Extension not accepted (" . $extension . ")");
				return false;
			}
		}
		
		// Check if file format is accepted
		if (!empty($this->config['accepted-formats'])) {
			if (!in_array($file['type'], $this->config['accepted-formats'])) {
				// Format not accepted
				$this->errorMsg("Format not accepted (" . $file['type'] . ")");
				return false;
			}
		}
		
		// Check for size
		if ($this->config['max-size'] > 0) {
			if ($file['size'] > $this->config['max-size'] * 1024) {
				// Size not accepted
				$this->errorMsg("File size is bigger than accepted (max " . $this->config['max-size'] . " KB)");
				return false;
			}
		}
		
		// Get original file data
		$this->filedata_source = array(
			'type' => $file['type'], 
			'name' => (strpos($file['name'], ".") === false) ? $file['name'] : substr($file['name'], 0, strrpos($file['name'], ".")), 
			'extension' => (strpos($file['name'], ".") === false) ? "" : substr(strrchr($file['name'], "."), 1), 
			'filename' => $file['name'], 
			'size' => $file['size'], 
			'tmp_name' => $file['tmp_name']
		);
		
		// Define target filename
		$filename = $this->nameCreate($this->config['path'], $this->config['name'], $this->config['prefix'], $this->config['suffix'], $extension);
		$filename_full = $extension != "" ? $filename . "." . $extension : $filename;
		
		// Delete previous file if we overwrite
		if ($this->config['overwrite']) {
			@unlink($this->config['path'] . $filename_full);
		}
		
		// Move file to ist target folder
		if (!@move_uploaded_file($file['tmp_name'], $this->config['path'] . $filename_full)) {
			$this->errorMsg("File could not be uploaded (" . $file['name'] . ")");
			return false;
		}
		
		// Change new file permissions
		@chmod($this->config['path'] . $filename_full, 0777);
		
		// Size in pixels (if it is an image)
		$size_pixels = @getimagesize($this->config['path'] . $filename_full);
		if (is_array($size_pixels) && !empty($size_pixels)) {
			$width = $size_pixels[0];
			$height = $size_pixels[1];
		} else {
			$width = 0;
			$height = 0;
		}
		
		// Create final output data
		$this->filedata_output = array(
																	'path' => $this->config['path'], 
																	'name' => $filename, 
																	'extension' => $extension, 
																	'filename' => $filename_full, 
																	'path_full' => $this->config['path'] . $filename_full, 
																	'prefix' => $this->config['prefix'], 
																	'suffix' => $this->config['suffix'], 
																	'width' => $width, 
																	'height' => $height, 
																	'size' => $this->filedata_source['size']
																	);
		
		// Clean files and memory
		$this->fileClear();
		
		// Successful end
		return true;
		
	}
	
	/**
	 * uploadImage::errorMsg()
	 * 
	 * @param mixed $txt
	 * @return void
	 */
	private function errorMsg($txt) {
		// Sets error message
		$this->fileClear();
		$this->error_msg = $txt;
	}
	
	private function nameCreate($path, $name, $prefix, $suffix, $extension) {
		// Creates a file name (without extension)
		if ($name == "") {
			// No name defined, we take the source image name
			$name_ok = $this->stringFormat($this->filedata_source['name']);
		} else {
			// User defined name
			$name_ok = $this->stringFormat($name);
		}
		if (file_exists($path . $prefix . $name_ok . $suffix . "." . $extension) && !$this->config['overwrite']) {
			// File exists and we can't overwrite (secuential renaming)
			$ok = false;
			$n = 1;
			while (!$ok) {
				if (file_exists($path . $prefix . $name_ok . "-" . $n . $suffix . "." . $extension)) {
					$n++;
				} else {
					$name_ok = $name_ok . "-" . $n;
					$ok = true;
				}
			}
		}
		
		return $prefix . $name_ok . $suffix;
	}
	
	
	/**
	 * uploadFile::fileClear()
	 * 
	 * @return void
	 */
	private function fileClear() {
		// Clean temp files and memory
		@unlink($this->config['path-temp'] . $this->temp_file);
	}
	
	/**
	 * uploadImage::stringFormat()
	 * 
	 * @param mixed $string
	 * @return
	 */
	private function stringFormat($string) {
		// Return a correctly formatted string
		return string::normalizeUrl($string);
	}
	
}

?>