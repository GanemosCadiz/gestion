<?php

/**
 * inputClean
 * 
 * @package 26horas Shared
 * @author Pablo Fernandez
 * @copyright 2015 Pablo Fernandez
 * @version 0.01 // 2015-01-19
 * @access public
 */

/**
 * Changelog:
 * 2015-01-19:	Corrected recursivity at inputClean::clean().
 * 							Added globals to detect database connection at inputClean::clean().
 * 2015-01-16:	Added offline cleaning.
 * 2015-01-08: 	First version.
 */

class inputClean {
	
	private $db;
	
	
	/**
	 * inputClean::__construct()
	 * 
	 * @return
	 */
	function __construct($options=array()) {
		
		
		
	}
	
	/**
	 * inputClean::clean()
	 * 
	 * @param mixed $dirty
	 * @param integer $long
	 * @param bool $allow_html
	 * @return
	 */
	public static function clean($dirty, $long=0, $allow_html=true) {
		
		global $cfg, $var, $obj;
		
		if (!isset($obj['db'])) {
			return inputClean::cleanOffline($dirty, $long, $allow_html);
		}
		
		// clean all elements in this array
		if (is_array($dirty)) {
			$clean = array();
			foreach ($dirty as $key => $value) {
				// Recursive
				$clean[$key] = inputClean::clean($value, $long, $allow_html);
			}
			return $dirty;
		// clean this string
		} else {
			// filter source for SQL injection
		  if ($long != 0) { $dirty = substr($dirty, 0, $long); } // Desired length
		  $clean = inputClean::quoteSmart(inputClean::decode($dirty, $allow_html));
		  return $clean;
		}
		
	}
	
	/**
	 * inputClean::inputCleanOffline()
	 * 
	 * @param mixed $dirty
	 * @param integer $long
	 * @param bool $allow_html
	 * @return
	 */
	 public static function cleanOffline($dirty, $long=0, $allow_html=false) {
		
		if (!is_string($dirty)) {
			return "";
		} else {
			if ($long != 0) { $dirty = substr($dirty, 0, $long); }
		  $dirty = inputClean::decode($dirty, $allow_html);
		  return $dirty;
		}
		
	}
	
	/**
	 * inputClean::quoteSmart()
	 * 
	 * @param mixed $string
	 * @return
	 */
	public static function quoteSmart($string) {
		
		global $cfg, $var, $obj;
		
		$string = trim($string);
		if (get_magic_quotes_gpc()) {
			$string = stripslashes($string);
		}
		if (!is_numeric($string)) {
			// only need to do this part for strings
		  $string = @$obj['db']->connection->real_escape_string($string);
		  if ($string === FALSE) {
		  	// we must not be connected to mysql, so....
		    $string = $obj['db']->connection->real_escape_string($string);
		  }
		}
		
		return $string;
		
	}
	
	/**
	 * inputClean::decode()
	 * 
	 * @param mixed $string
	 * @param bool $allow_html
	 * @return
	 */
	public static function decode($string, $allow_html=false) {
		
		// url decode
		$string = html_entity_decode($string, ENT_QUOTES, "UTF-8");
		// strip tags
		if (!$allow_html) {
			$string = strip_tags($string);
		}
		// convert decimal
		$string = preg_replace_callback('/&#(\d+);/m', function($m) {
								return chr($m[1]);
							}, $string);
		// convert hex
		$string = preg_replace_callback('/&#x([a-f0-9]+);/mi', function($m) {
								return chr("0x".$m[1]);
							}, $string);
		
		return $string;
		
	}
	
	/**
	 * inputClean::escapeString()
	 * 
	 * @param mixed $string
	 * @return
	 */
	public static function escapeString($string) {
		
		// depreciated function
		if (version_compare(phpversion(),"4.3.0", "<")) $obj['db']->connection->real_escape_string($string);
		// current function
		else $obj['db']->connection->real_escape_string($string);
		
		return $string;
		
	}
	
}

?>