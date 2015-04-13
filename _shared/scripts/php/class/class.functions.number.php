<?php

class number {
	
	public static function numberMoney($number, $decimals=true) {
		if ($decimals) {
			$number = number_format($number, 2, ",", ".");
		} else {
			$number = number_format(floor($number), 0, ",", ".");
		}
		return $number;
	}
	
	public static function numberThousands($number) {
		$number = number_format(floor($number), 0, ",", ".");
		return $number;
	}
	
	public static function amount($float, $sign=false) {
		
		global $cfg, $vars;
		
		$n = number_format($float, 2, ",", ".");
		$t = explode(",", $n);
		if ($sign && $t[0] > 0) {
			//$t[0] = "+" . $t[0];
		}
		
		return $t[0] . ",<i>" . $t[1] . "</i><em> €</em>";
		
	}
	
	public static function floatToDB($float) {
		
		global $cfg, $vars;
		
		return str_replace($cfg['separator-decimals'], ".", $float);
		
	}
	
	public static function DBToFloat($float) {
		
		global $cfg, $vars;
		
		$f = str_replace("." , $cfg['separator-decimals'], $float);
		if (strpos($f, $cfg['separator-decimals']) !== false) {
			$t = explode($cfg['separator-decimals'], $f);
			if ($t[1] == 0) {
				$f = $t[0];
			}
		}
		
		return $f;
		
	}
	
	public static function format($number, $decimals=false) {
		
		$locale = localeconv();
		
		if ($locale['frac_digits'] == 127) {
			// Correct default values
			$locale['frac_digits'] = 2;
			$locale['decimal_point'] = ",";
			$locale['thousands_sep'] = ".";
		}
		
		$decimals_num = is_float($number) ? $locale['frac_digits'] : 0;
		if (!$decimals) {
			$decimals_num = 0;
		} else {
			$decimals_num = $locale['frac_digits'];
		}
		
		$output = number_format($number, $decimals_num, $locale['decimal_point'], $locale['thousands_sep']);
		
		return $output;
		
	}
	
	public static function TPVFormat($number) {
		
		$n = number_format($number, 2, ".", "") * 100;
		$n = str_replace(".", "", $n);
		
		return $n;
		
	}
	
	public static function TPVMySQLFormat($number) {
		return $number / 100;
	}
	
}

?>