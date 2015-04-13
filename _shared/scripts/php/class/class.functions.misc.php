<?php

/**
 * misc
 * 
 * @package 26horas Shared
 * @author Pablo Fernandez
 * @copyright 2015 Pablo Fernandez
 * @version 0.01 // 2015-01-09
 * @access public
 */
class misc {
	
	/**
	 * misc::debugVar()
	 * 
	 * @param mixed $var
	 * @return
	 */
	public static function debugVar($var, $die=true) {
		
		echo "<pre>";
		print_r($var);
		
		if ($die) {
			die();
		}
		
	}
	
	/**
	 * misc::jsonEncode()
	 * 
	 * @param mixed $array
	 * @return
	 */
	public static function jsonEncode($array) {
		
		// http://www.avoid.org/replace-u-characters-in-json-string/
		// http://stackoverflow.com/questions/26509896/php-json-encode-utf-8-issue-on-database
		return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', function($matches) {
			return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UTF-16');
		}, json_encode($array));
		
	}
	
	
	public static function isJson($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}
	
	
	/**
	 * misc::error404()
	 * 
	 * @param bool $html
	 * @return
	 */
	public static function error404($html=false) {
		
		header("HTTP/1.0 404 Not Found");
		
		if (!$html) {
			die();
		} else {
			$output = "<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">
									<html><head>
									<title>404 Not Found</title>
									</head><body>
									<h1>Not Found</h1>
									<p>The requested URL was not found on this server.</p>
									</body></html>";
			die(str_replace("\t", "", $output));
		}
		
	}
	
	/**
	 * misc::getIP()
	 * 
	 * @return
	 */
	public static function getIP() {
		
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
		
	}
	
	/**
	 * misc::noCache()
	 * 
	 * @return
	 */
	public static function noCache() {
		
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		
	}
	
	public static function download($data, $filename="") {
		
		// Create output
		$output = "";
		
		foreach ($data as $n => $line) {
			for ($i=0; $i<=count($line); $i++) {
				$output .= $cfg['downloads']['field-delimiter'] . (isset($line[$i]) ? $line[$i] : "") . $cfg['downloads']['field-delimiter'] . $cfg['downloads']['field-separator'];
			}
			$output .= $cfg['downloads']['linebreaker'];
		}
		
		header('Content-Description: File Transfer');
		header('Content-Type: application/csv');
		header('Content-Disposition: attachment; filename='.basename($filename));
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		//header('Content-Length: ' . filesize($cfg['download-path'] . $filename));
		ob_clean();
		flush();
		
		//read the file from disk and output the content.
		echo $data;
		die();
		
	}
	
	/**
	 * misc::arrayNext()
	 * 
	 * @param mixed $array
	 * @param mixed $actual
	 * @return 
	 */
	public static function arrayNextKey($array, $key) {
		
		reset($array);
		
		$next = false;
		
		while (key($array) !== null) {
			$k = key($array);
			$n = next($array);
			if ($k == $key) {
				return ($n === false) ? false : key($array);
			}
		}
		
		return $next;
		
	}
	
	/**
	 * string::urlReplace()
	 * 
	 * @param mixed $url
	 * @param mixed $param
	 * @param mixed $new_value
	 * @return
	 */
	public static function urlReplace($url, $param, $new_value) {
		
		$parsed_url = parse_url($url);
		if (isset($parsed_url['query'])) {
			parse_str($parsed_url['query'], $query);
		} else {
			$query = array();
		}
		
		if (isset($query[$param])) {
			$query[$param] = $new_value;
			$output = $parsed_url['path'] . "?" . http_build_query($query);
		} else {
			if (strpos($url, "?") === false) {
				$output = $url . "?" . $param . "=" . urlencode($new_value);
			} else {
				$output = $url . "&amp;" . $param . "=" . urlencode($new_value);
			}
		}
		
		return $output;
		
	}
	
	/**
	 * misc::urlAdd()
	 * 
	 * @param mixed $url
	 * @param mixed $param
	 * @param mixed $new_value
	 * @return
	 */
	public static function urlAdd($url, $param, $new_value) {
		
		$val = ($new_value != "") ? "=" . urlencode($new_value) : "";
		
		if (strpos($url, "?") === false) {
			$output = $url . "?" . $param . $val;
		} else {
			$output = $url . "&amp;" . $param . $val;
		}
		
		return $output;
		
	}
	
	/**
	 * misc::xml2array()
	 * 
	 * @param mixed $contents
	 * @param integer $get_attributes
	 * @param string $priority
	 * @return
	 */
	public static function xml2array($contents, $get_attributes=1, $priority = 'tag') {
		
		/** 
		* xml2array() will convert the given XML text to an array in the XML structure. 
		* Link: http://www.bin-co.com/php/scripts/xml2array/ 
		* Arguments : $contents - The XML text 
		*                $get_attributes - 1 or 0. If this is 1 the function will get the attributes as well as the tag values - this results in a different array structure in the return value.
		*                $priority - Can be 'tag' or 'attribute'. This will change the way the resulting array sturcture. For 'tag', the tags are given more importance.
		* Return: The parsed XML in an array form. Use print_r() to see the resulting array structure. 
		* Examples: $array =  xml2array(file_get_contents('feed.xml')); 
		*              $array =  xml2array(file_get_contents('feed.xml', 1, 'attribute')); 
		*/ 
		
	    if(!$contents) return array(); 
	
	    if(!function_exists('xml_parser_create')) { 
	        //print "'xml_parser_create()' function not found!"; 
	        return array(); 
	    } 
	
	    //Get the XML parser of PHP - PHP must have this module for the parser to work 
	    $parser = xml_parser_create(''); 
	    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss 
	    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0); 
	    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1); 
	    xml_parse_into_struct($parser, trim($contents), $xml_values); 
	    xml_parser_free($parser); 
	
	    if(!$xml_values) return;//Hmm... 
	
	    //Initializations 
	    $xml_array = array(); 
	    $parents = array(); 
	    $opened_tags = array(); 
	    $arr = array(); 
	
	    $current = &$xml_array; //Refference 
	
	    //Go through the tags. 
	    $repeated_tag_index = array();//Multiple tags with same name will be turned into an array 
	    foreach($xml_values as $data) { 
	        unset($attributes,$value);//Remove existing values, or there will be trouble 
	
	        //This command will extract these variables into the foreach scope 
	        // tag(string), type(string), level(int), attributes(array). 
	        extract($data);//We could use the array by itself, but this cooler. 
	
	        $result = array(); 
	        $attributes_data = array(); 
	         
	        if(isset($value)) { 
	            if($priority == 'tag') $result = $value; 
	            else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
	        } 
	
	        //Set the attributes too. 
	        if(isset($attributes) and $get_attributes) { 
	            foreach($attributes as $attr => $val) { 
	                if($priority == 'tag') $attributes_data[$attr] = $val; 
	                else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr' 
	            } 
	        } 
	
	        //See tag status and do the needed. 
	        if($type == "open") {//The starting of the tag '<tag>' 
	            $parent[$level-1] = &$current; 
	            if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag 
	                $current[$tag] = $result; 
	                if($attributes_data) $current[$tag. '_attr'] = $attributes_data; 
	                $repeated_tag_index[$tag.'_'.$level] = 1; 
	
	                $current = &$current[$tag]; 
	
	            } else { //There was another element with the same tag name 
	
	                if(isset($current[$tag][0])) {//If there is a 0th element it is already an array 
	                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result; 
	                    $repeated_tag_index[$tag.'_'.$level]++; 
	                } else {//This section will make the value an array if multiple tags with the same name appear together
	                    $current[$tag] = array($current[$tag],$result);//This will combine the existing item and the new item together to make an array
	                    $repeated_tag_index[$tag.'_'.$level] = 2; 
	                     
	                    if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
	                        $current[$tag]['0_attr'] = $current[$tag.'_attr']; 
	                        unset($current[$tag.'_attr']); 
	                    } 
	
	                } 
	                $last_item_index = $repeated_tag_index[$tag.'_'.$level]-1; 
	                $current = &$current[$tag][$last_item_index]; 
	            } 
	
	        } elseif($type == "complete") { //Tags that ends in 1 line '<tag />' 
	            //See if the key is already taken. 
	            if(!isset($current[$tag])) { //New Key 
	                $current[$tag] = $result; 
	                $repeated_tag_index[$tag.'_'.$level] = 1; 
	                if($priority == 'tag' and $attributes_data) $current[$tag. '_attr'] = $attributes_data;
	
	            } else { //If taken, put all things inside a list(array) 
	                if(isset($current[$tag][0]) and is_array($current[$tag])) {//If it is already an array... 
	
	                    // ...push the new element into that array. 
	                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result; 
	                     
	                    if($priority == 'tag' and $get_attributes and $attributes_data) { 
	                        $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data; 
	                    } 
	                    $repeated_tag_index[$tag.'_'.$level]++; 
	
	                } else { //If it is not an array... 
	                    $current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value
	                    $repeated_tag_index[$tag.'_'.$level] = 1; 
	                    if($priority == 'tag' and $get_attributes) { 
	                        if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
	                             
	                            $current[$tag]['0_attr'] = $current[$tag.'_attr']; 
	                            unset($current[$tag.'_attr']); 
	                        } 
	                         
	                        if($attributes_data) { 
	                            $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data; 
	                        } 
	                    } 
	                    $repeated_tag_index[$tag.'_'.$level]++; //0 and 1 index is already taken 
	                } 
	            } 
	
	        } elseif($type == 'close') { //End of tag '</tag>' 
	            $current = &$parent[$level-1]; 
	        } 
	    } 
	     
	    return($xml_array); 
	}  
	
}

?>