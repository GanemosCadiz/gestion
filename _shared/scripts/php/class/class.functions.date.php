<?php

class date {
	
	public static function invert($date) {
		
		if (strpos($date, "-") !== false) {
			$t = explode("-", $date);
			return $t[2] . "/" . $t[1] . "/" . $t[0];
		} else {
			$t = explode("/", $date);
			return $t[2] . "-" . $t[1] . "-" . $t[0];
		}
		
	}
	
	public static function invertFB($date) {
		
		if ($date != "") {
			$t = explode("/", $date);
			return $t[2] . "-" . $t[0] . "-" . $t[1];
		} else {
			return "";
		}
		
	}
	
	/*public static function toText($date) {
		
		if ($m == "") {
			$t = explode("-", $y);
			$y = $t[0];
			$m = $t[1];
			$d = $t[2];
		}
		
		$output = intval($d) . _(" de ") . $cfg['text-months'][intval($m)-1] . _(" de ") . intval($y);
		
		return $output;
		
	}*/
	
	public static function today() {
		
		return date("d-m-Y");
		
	}
	
	public static function dateCheck($date) {
		
		$t = explode("-", $date);
		
		return checkdate($t[1], $t[2], $t[0]);
		
	}
	
	public static function check($date) {
		
		global $cfg, $vars;
		
		$t = explode("-", $date);
		
		return checkdate($t[1], $t[2], $t[0]);
		
	}
	
	public static function complete($date) {
		
		$t = explode(" ", $date);
		$d = explode("-", $t[0]);
		
		return str_pad($d[2], 2, "0", STR_PAD_LEFT) . "/" . str_pad($d[1], 2, "0", STR_PAD_LEFT) . "/" . $d[0] . " " . _("a las") . " " . $t[1];
		
	}
	
	public static function complete2($date) {
		
		$t = explode(" ", $date);
		$d = explode("-", $t[0]);
		
		return str_pad($d[2], 2, "0", STR_PAD_LEFT) . "/" . str_pad($d[1], 2, "0", STR_PAD_LEFT) . "/" . $d[0] . " <span>" . $t[1] . "</span>";
		
	}
	
	public static function toDB($date) {
		
		$t = explode("/", $date);
		return $t[2] . "-" . $t[1] . "-" . $t[0];
		
	}
	
	public static function dateDB($time="") {
		
		if ($time == "") {
			$time = time();
		} else if (is_string($time)) {
			$time = strtotime($time);
		}
		
		return date("Y-m-d", $time);
		
	}
	
	public static function datetimeDB($time="") {
		
		if ($time == "") {
			$time = time();
		} else if (is_string($time)) {
			$time = strtotime($time);
		}
		
		return date("Y-m-d H:i:s", $time);
		
	}
	
	public static function format($time, $type="date", $format="%c", $seconds=false) {
		
		global $cfg, $var, $obj;
		
		if (is_string($time)) {
			$time = strtotime($time);
		}
		
		$time_format = ($seconds) ? "%X" : "%H:%M";
		
		if (!$time) { return "-"; }
		
		switch ($type) {
			
			case "date":
				$output = strftime("%x", $time);
			break;
			
			case "datetime":
			default:
				$output = strftime("%x <span>" . $time_format . "</span>", $time);
			break;
			
			case "dateText":
				$output = strftime("%d %B %Y", $time);
			break;
			
			case "datetimeText":
				$output = strftime("%d %B %Y <span>" . $time_format . "</span>", $time);
			break;
			
			case "dateTextShort":
				$output = strftime("%d %b <span>%Y</span>", $time);
			break;
			
			case "datetimeTextShort":
				$output = strftime("%d %b %Y <span>" . $time_format . "</span>", $time);
			break;
			
			case "dateTextLong":
				$output = strftime("%A, %d %B %Y", $time);
			break;
			
			case "datetimeTextLong":
				$output = strftime("%A, %d %B %Y <span>" . $time_format . "</span>", $time);
			break;
			
			case "custom":
				$output = strftime($format, $time);
			break;
			
		}
		
		return string::utf8Encode($output);
		
	}
	
	public static function ageGet($y2, $m2, $d2) {
		
		global $cfg, $vars;
		
		$t = explode("-", $cfg['race-date']);
		$y1 = intval($t[0]);
		$m1 = intval($t[1]);
		$d1 = intval($t[2]);
		if (($m2 == $m1) && ($d2 > $d1)) {
			$y1 = ($y1 - 1);
		} else if ($m2 > $m1) {
			$y1 =  $y1 - 1;
		}
		$age = $y1 - $y2;
		
		return age;
		
	}
	
}

?>