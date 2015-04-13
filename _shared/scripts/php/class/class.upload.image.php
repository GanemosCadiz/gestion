<?php

/**
 * uploadImage
 * 
 * @package 26horas Shared
 * @author Pablo Fernandez - pablo.fernandez@26horas.com
 * @copyright 2015 Pablo Fernandez
 * @version 0.4 // 2015-01-31
 * @access public
 */

/**
 * Changelog:
 * 
 * 2015-02-22:	Fixed black background when resizing png (now white).
 * 2015-01-31:	New PHP 5 format.
 * 							Changed output files to associative array.
 * 2014-02-19:	Added no limits in maximum width and height.
 * 							Added temp path option.
 * 2009-03-17:	First version.
 */

class uploadImage {
	
	// Default configuration
	private $defaults = array(
		// Default file values
		'path' => "", // Default absolute or relative path where files will be stored.
		'path-temp' => "", // Default absolute or relative path where temporary files will be stored (if default system temp path fails).
		'name' => "", // Final name of file (without extension). If left blank, source file name will be used.
		'prefix' => "", // Prefix to be added before file name.
		'suffix' => "", // Suffix to be added to the end of the file name.
		'max-width' => 0, // Maximum width of image (if set to 0, no maximum width).
		'max-height' => 0, // Maximum height of image (if set to 0, no maximum height).
		'resize' => false, // If true, image will be resized according to max-width and max-height values (excludes cropping).
		'crop' => false, // If true, image will be cropped according to max-width and max-height values.
		'stretch' => false, // If true, image will be stretched to fit max width o height if it's smaller (only with resize or cropping).
		'aspect-ratio' => true, // If true image aspect ratio will be kept when resizing.
		'output-format' => "", // Output format of image. If left blank, source image format will be used.
														// Supported formats are: jpg, gif and png.
		'quality' => 75, // Quality of output image (0: minimum quality, 100: maximum quality).
		// General settings
		'overwrite' => false, // Indicates whenever to overwrite files after upload (true) or not (false).
		'accepted-extensions' => array("jpg", "jpeg", "jpe", "gif", "png"), // Accepted file extensions.
		'accepted-formats' => array("image/pjpeg", "image/jpeg", "image/jpg", "image/gif", "image/png"), // Accepted file formats.
		'max-size' => 3000 // Maximum size (in kilobytes) of images that wil be accepted.
	);
	
	/* 	Output files that will be generated.
			If left blank, one only file will be generated with default options.
			If any value is left blank, default value will be used.
			Accepted values:
			- id: Code to identify image.
			- path: Absolute or relative path where file will be stored.
			- name: Final name of file (without extension). If left blank, source file name will be used.
			- prefix: Prefix to be added before file name.
			- suffix: Suffix to be added to the end of the file name.
			- resize: If true, image will be resized according to max-width and max-height values.
			- crop: If true, image will be cropped according to max-width and max-height values.
			- max-width: Maximum width of image (if set to 0, no maximum width).
			- max-height: Maximum height of image (if set to 0, no maximum height).
			- stretch: If true, image will be stretched to fit max width o height if it's smaller.
			- aspect-ratio: If true image aspect ratio will be kept when resizing.
			- output-format: Output format of image. If left blank, source image format will be used.
			- quality: Quality of output image (0: minimum quality, 100: maximum quality).*/
	public $file_outputs = array();
	
	// Output messages
	private $output_msgs = array(
													'upload-fail' => "No se pudo subir la imagen (%s)", 
													'extension-fail' => "La imagen tiene una extensión no aceptada (%s)", 
													'format-fail' => "La imagen tiene un formato no aceptado (%s)", 
													'size-fail' => "La imagen es mayor de lo aceptado (máximo: %s KB)", 
													'processing-fail' => "Error al procesar la imagen", 
													'resize-fail' => "No se pudo cambiar el tamaño de la imagen (%s)", 
													'format-fail' => "Formato de salida no compatible (sólo se aceptan jpg, gif y png): %s", 
													'final-fail' => "Error al crear la imagen final (%s)", 
													'gd-fail' => "Librería GD de gestión de imágenes no detectada", 
													'copy-fail' => "No se pudo copiar la imagen (%s)"
													);
	
	// Output variables
	public $error_msg = ""; // Text that will describe the error. Blank if no errors.
	public $filedata_output = array(); // Info about final files.
	public $filedata_source = array(); // Info about source files.
	
	// Internal
	private $config = array();
	private $temp_file;
	private $image_obj;
	private $image_new;
	
	/**
	 * uploadImage::__construct()
	 * 
	 * @param mixed $options
	 * @return void
	 */
	public function __construct($options=array()) {
		
		// ----------------------------------------------------------------
		// Class constructor
		
		foreach ($this->defaults as $k => $v) {
			$this->defaults[$k] = $this->setVar($options, $k, $v);
		}
		
		// Temp path
		$this->defaults['path-temp'] = $this->defaults['path-temp'] != "" ? $this->defaults['path-temp'] : sys_get_temp_dir();
		
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
	 * uploadImage::upload()
	 * 
	 * @param mixed $field_name
	 * @return
	 */
	public function go($field_name) {
	 	// ----------------------------------------------------------------
		// Executes upload
	 	
	 	// Check if file was uploaded
	 	if (!isset($_FILES) || !isset($_FILES[$field_name]) || !is_array($_FILES[$field_name]) || !$_FILES[$field_name]['name']) {
	 		// No file to upload
	    return true;
		}
		
		// Assign file handler
		$file = $_FILES[$field_name];
		
		// Check if upload was done correctly
		$this->temp_file = uniqid(microtime(), true);
		if (!@move_uploaded_file($file['tmp_name'], $this->defaults['path-temp'] . $this->temp_file) || !$file['size']) {
			// File was not correctly uploaded
	    $this->errorMsg(sprintf($this->output_msgs['upload-fail'], $file['name']));
	    return false;
	  }
		
		// Check accepted extension
		if (!in_array(strtolower(substr(strrchr($file['name'], "."), 1)), $this->defaults['accepted-extensions'])) {
			// No accepted
			$this->errorMsg(sprintf($this->output_msgs['extension-fail'], strtoupper(strtolower(substr(strrchr($file['name'], "."), 1)))));
			return false;
		}
		
		// Check accpeted format
		if (!in_array($file['type'], $this->defaults['accepted-formats'])) {
			// Not accepted
			$this->errorMsg(sprintf($this->output_msgs['format-fail'], $file['type']));
			return false;
		}
		
		// Check accepted maximum size
		if ($file['size'] > $this->defaults['max-size'] * 1024) {
			// Not accepted
			$this->errorMsg(sprintf($this->output_msgs['extension-fail'], $this->defaults['max-size']));
			return false;
		}
		
		// Image formats
		$t = explode("/", $file['type']);
		
		switch ($t[1]) {
			
			case "jpeg":
			case "jpg":
			case "pjpeg":
			$this->image_obj = @imagecreatefromjpeg($this->defaults['path-temp'] . $this->temp_file);
			break;
			
			case "gif":
			$this->image_obj = @imagecreatefromgif($this->defaults['path-temp'] . $this->temp_file);
			break;
			
			case "png":
			$this->image_obj = @imagecreatefrompng($this->defaults['path-temp'] . $this->temp_file);
			break;
			
			default:
			$this->errorMsg(sprintf($this->output_msgs['format-fail'], $t[1]));
			return false;
			break;
			
		}
		
		if (!$this->image_obj) {
			// Error creating image format
			$this->errorMsg($this->output_msgs['processing-fail']);
			return false;
		}
		
		// Source file data
		list($source_width, $source_height) = getimagesize($this->defaults['path-temp'] . $this->temp_file);
		$this->filedata_source = array(
			'type' => $file['type'], 
			'filename' => $file['name'], 
			'name' => (strpos($file['name'], ".") === false) ? $file['name'] : substr($file['name'], 0, strrpos($file['name'], ".")), 
			'extension' => (strpos($file['name'], ".") === false) ? "" : strtolower(substr(strrchr($file['name'], "."), 1)), 
			'size' => $file['size'], 
			'tmp' => $this->defaults['path-temp'] . $this->temp_file, 
			'width' => $source_width, 
			'height' => $source_height
		);
		
		// Check file definitions or default values 
		if (empty($this->file_outputs)) {
			// No files where defined, we take default values
			$this->file_outputs = array(
																	'main' => array('path' => $this->defaults['path'])
																	);
		}
	  
	  // Generation of files
	  foreach ($this->file_outputs as $id => $f) {
			
			// Value sdefinition
			$path						= (isset($f['path']))						? $f['path']					: $this->defaults['path'];
			$name						= (isset($f['name']))						? $f['name']					: $this->defaults['name'];
			$prefix					= (isset($f['prefix']))					? $f['prefix']				: $this->defaults['prefix'];
			$suffix					= (isset($f['suffix']))					? $f['suffix']				: $this->defaults['suffix'];
			$max_width			= (isset($f['max-width']))			? $f['max-width']			: $this->defaults['max-width'];
			$max_height			= (isset($f['max-height']))			? $f['max-height']		: $this->defaults['max-height'];
			$resize					= (isset($f['resize']))					? $f['resize']				: $this->defaults['resize'];
			$crop						= (isset($f['crop']))						? $f['crop']					: $this->defaults['crop'];			
			$stretch				= (isset($f['stretch']))				? $f['stretch']				: $this->defaults['stretch'];
			$aspect_ratio		= (isset($f['aspect-ratio']))		? $f['aspect-ratio']	: $this->defaults['aspect-ratio'];
			$output_format	= (isset($f['output-format']))	? $f['output-format']	: $this->defaults['output-format'];
			$quality				= (isset($f['quality']))				? $f['quality']				: $this->defaults['quality'];
			
			// No maximum sizes
			$max_width = ($max_width == 0) ? 5000 : $max_width;
			$max_height = ($max_height == 0) ? 5000 : $max_height;
			
			if ($resize || $crop) {
				// ----------------------------------------------
				// Resizing or Cropping
				
				if ($resize) {
					// Calculation of new size
					$size = $this->sizeCalculate($this->filedata_source, $max_width, $max_height, $stretch, $aspect_ratio);
				} else if ($crop) {
					// Calculation of cropping
					$cropping = $this->cropCalculate($this->filedata_source['width'], $this->filedata_source['height'], $max_width, $max_height);
					$size = array('width' => $max_width, 'height' => $max_height);
				}
				
				// Creation of image with new size
				if (function_exists("imagecreatetruecolor")) {
					
					// Image resizing
					if ($resize) {
						// Resize
						$this->image_new = imagecreatetruecolor($size['width'], $size['height']);
						$bkg = imagecolorallocate($this->image_new, 255, 255, 255);
						imagefill($this->image_new, 0, 0, $bkg);
						$resizing = @imagecopyresampled($this->image_new, $this->image_obj, 0, 0, 0, 0, $size['width'], $size['height'], $this->filedata_source['width'], $this->filedata_source['height']);
						if (!$resizing) {
							$this->errorMsg(sprintf($this->output_msgs['resize-fail'], $name));
							return false;
						}
					} else if ($crop) {
						// Crop
						$this->image_new = imagecreatetruecolor($max_width, $max_height);
						$bkg = imagecolorallocate($this->image_new, 255, 255, 255);
						imagefill($this->image_new, 0, 0, $bkg);
						$resizing = @imagecopyresampled($this->image_new, $this->image_obj, 0, 0, $cropping['x'], $cropping['y'], $max_width, $max_height, $cropping['width'], $cropping['height']);
						if (!$resizing) {
							$this->errorMsg(sprintf($this->output_msgs['resize-fail'], $name));
							return false;
						}
					}
					
					// Output format
					if ($output_format == "") {
						// No output format defined, source image taken
						$t = explode("/", $this->filedata_source['type']);
						$output_format = $t[1];
					}
					if (!in_array($output_format, $this->defaults['accepted-extensions'])) {
						$this->errorMsg(sprintf($this->output_msgs['format-fail'], $output_format));
						return false;
					}
					
					// Creation of final image
					switch ($output_format) {
						
						case "jpeg":
						case "jpg":
						case "pjpeg":
							$extension = "jpg";
							$filename = $this->nameCreate($path, $name, $prefix, $suffix, $extension);
							if ($this->defaults['overwrite']) {
								@unlink($path . $filename . "." . $extension);
							}
							$image_output = imagejpeg($this->image_new, $path . $filename . "." . $extension, $quality);
						break;
						
						case "gif":
							$extension = "gif";
							$filename = $this->nameCreate($path, $name, $prefix, $suffix, $extension);
							if ($this->defaults['overwrite']) {
								@unlink($path . $filename . "." . $extension);
							}
							$image_output = imagegif($this->image_new, $path . $filename . "." . $extension);
						break;
						
						case "png":
							$extension = "png";
							$compression = round((1 - ($quality / 100)) * 9);
							$filename = $this->nameCreate($path, $name, $prefix, $suffix, $extension);
							if ($this->defaults['overwrite']) {
								@unlink($path . $filename . "." . $extension);
							}
							$image_output = imagepng($this->image_new, $path . $filename . "." . $extension, $compression);
						break;
						
						default:
							$this->errorMsg(sprintf($this->output_msgs['format-fail'], $output_format));
							return false;
						break;
						
					}
					
					if (!$image_output) {
						$this->errorMsg(sprintf($this->output_msgs['final-fail'], $filename . "." . $extension));
						return false;
					}
					
				} else {
					$this->errorMsg($this->output_msgs['gd-fail']);
					return false;
				}
				
			} else {
				// ----------------------------------------------
				// No resizing
				
				// Definition of size
				$size = array('width' => $this->filedata_source['width'], 'height' => $this->filedata_source['height']);
				
				// Output format
				$t = explode("/", $this->filedata_source['type']);
				$output_format = $t[1];
				if (!in_array($output_format, $this->defaults['accepted-extensions'])) {
					$this->errorMsg(sprintf($this->output_msgs['format-fail'], $output_format));
					return false;
				}
				
				switch ($output_format) {
					
					case "jpeg":
					case "jpg":
					case "pjpeg":
						$extension = "jpg";
					break;
					
					case "gif":
						$extension = "gif";
						
					break;
					
					case "png":
						$extension = "png";
					break;
					
					default:
						$this->errorMsg(sprintf($this->output_msgs['format-fail'], $output_format));
						return false;
					break;
					
				}
				
				// Copy image
				$filename = $this->nameCreate($path, $name, $prefix, $suffix, $extension);
				if ($this->defaults['overwrite']) {
					@unlink($path . $filename . "." . $extension);
				}
				$copy = copy($this->defaults['path-temp'] . $this->temp_file, $path . $filename . "." . $extension);
				if (!$copy) {
					$this->errorMsg(sprintf($this->output_msgs['copy-fail'], $path . $filename . "." . $extension));
					return false;
				}
				
			}
			
			// Permission change so image is available to every server user
			@chmod($path . $filename . "." . $extension, 0777);
			
			// Add image to output queue
			$this->filedata_output[$id] = array(
																					'path' => $path, 
																					'name' => $filename, 
																					'extension' => $extension, 
																					'filename' => $filename . "." . $extension, 
																					'path_full' => $path . $filename . "." . $extension, 
																					'format' => $output_format, 
																					'prefix' => $prefix, 
																					'suffix' => $suffix, 
																					'width' => $size['width'], 
																					'height' => $size['height']
																					);
			
		}
		
		// Clean files and memory
		$this->fileClear();
		
		// End :)
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
	
	/**
	 * uploadImage::nameCreate()
	 * 
	 * @param mixed $path
	 * @param mixed $name
	 * @param mixed $prefix
	 * @param mixed $suffix
	 * @param mixed $extension
	 * @return
	 */
	private function nameCreate($path, $name, $prefix, $suffix, $extension) {
		// Creates a file name (without extension)
		if ($name == "") {
			// No name defined, we take the source image name
			$name_ok = $this->stringFormat($this->filedata_source['name']);
		} else {
			// User defined name
			$name_ok = $this->stringFormat($name);
		}
		if (file_exists($path . $prefix . $name_ok . $suffix . "." . $extension) && !$this->defaults['overwrite']) {
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
	 * uploadImage::sizeCalculate()
	 * 
	 * @param mixed $image_data
	 * @param mixed $max_width
	 * @param mixed $max_height
	 * @param mixed $stretch
	 * @param mixed $aspect_ratio
	 * @return
	 */
	private function sizeCalculate($image_data, $max_width, $max_height, $stretch, $aspect_ratio) {
		// Calculates final size of image
		
		// Image aspect ratio
		$ratio = $image_data['width'] / $image_data['height'];
		
		if ($image_data['width'] <= $max_width && $image_data['height'] <= $max_height) {
			// Image is smaller than max parameters
			
			if ($stretch) {
				// Must stretch
				if ($aspect_ratio) {
					// Keep aspect ratio
					$width = $max_width;
					$height = round($width * (1 / $ratio));
					if ($height > $max_height) {
						$width = round($max_height * $ratio);
						$height = $max_height;
					}
				} else {
					// No keeping aspect ratio
					$width = $max_width;
					$height = $max_height;
				}
			} else {
				// No stretch
				$width = $image_data['width'];
				$height = $image_data['height'];
			}
			
		} else {
			// Image is bigger than max parameters
			
			if ($aspect_ratio) {
				// Keep aspect ratio
				$width = $max_width;
				$height = round($width * (1 / $ratio));
				if ($height > $max_height) {
					$width = round($max_height * $ratio);
					$height = $max_height;
				}
			} else {
				// No keeping aspect ratio
				$width = $max_width;
				$height = $max_height;
			}
			
		}
		
		return array('width' => $width, 'height' => $height);
		
	}
	
	/**
	 * uploadImage::cropCalculate()
	 * 
	 * @param mixed $original_width
	 * @param mixed $original_height
	 * @param mixed $final_width
	 * @param mixed $final_height
	 * @return
	 */
	private function cropCalculate($original_width, $original_height, $final_width, $final_height) {
		// Calculates cropping coordinates
		
		$w_ratio = $original_width / $final_width;
		$h_ratio = $original_height / $final_height;
		
		if ($w_ratio <= $h_ratio) {
			// Width dominates
			
			$w = $final_width * $w_ratio;
			$h = $final_height * $w_ratio;
			
		} else {
			// Height dominates
			
			$w = $final_width * $h_ratio;
			$h = $final_height * $h_ratio;
			
		}
		
		$x = round(($original_width - $w) / 2);
		$y = round(($original_height - $h) / 2);
		
		return array(
			'x' => $x, 
			'y' => $y, 
			'width' => $w, 
			'height' => $h
		);
		
	}
	
	/**
	 * uploadImage::fileClear()
	 * 
	 * @return void
	 */
	private function fileClear() {
		// Cleaning temp files
		@imagedestroy($this->image_new);
		@imagedestroy($this->image_obj);
		@unlink($this->defaults['path-temp'] . $this->temp_file);
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