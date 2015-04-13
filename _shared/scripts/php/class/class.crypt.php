<?php

/**
 * myCrypt
 * 
 * @package 26horas Shared
 * @author Pablo Fernandez
 * @copyright 2015 Pablo Fernandez
 * @version 0.02 // 2015-01-22
 * @access public
 */

/**
 * Changelog:
 * 2015-03-14:	Added myCrypt::getToken() to get random tokens.
 * 2015-01-22:	Added Hashids class.
 * 							Added myCrypt::numEncode() and myCrypt::numDecode().
 * 2015-01-09: 	First version.
 */

class myCrypt {
	
	/**
	 * crypt::__construct()
	 * 
	 * @return
	 */
	public function __construct($options=array()) {
		
		
		
	}
	
	/**
	 * crypt::crypto_rand_secure()
	 * 
	 * @param mixed $min
	 * @param mixed $max
	 * @return
	 */
	public static function crypto_rand_secure($min, $max) {
		
		$range = $max - $min;
		if ($range < 0) return $min; // not so random...
		$log = log($range, 2);
		$bytes = (int) ($log / 8) + 1; // length in bytes
		$bits = (int) $log + 1; // length in bits
		$filter = (int) (1 << $bits) - 1; // set all lower bits to 1
		do {
			$rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
			$rnd = $rnd & $filter; // discard irrelevant bits
		} while ($rnd >= $range);
		return $min + $rnd;
		
	}
	
	/**
	 * crypt::getSalt()
	 * 
	 * @return
	 */
	public static function getSalt() {
		
		return md5(uniqid(microtime(true)).microtime(true));
		
	}
	
	/**
	 * myCrypt::getToken()
	 * 
	 * @param integer $length
	 * @return
	 */
	public static function getToken($length=32) {
		
		$token = "";
		$codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$codeAlphabet .= "abcdefghijklmnopqrstuvwxyz";
		$codeAlphabet .= "0123456789";
		for	($i=0; $i<$length; $i++) {
			$token .= $codeAlphabet[myCrypt::crypto_rand_secure(0, strlen($codeAlphabet))];
		}
		return $token;
		
	}
	
	/**
	 * crypt::encrypt()
	 * 
	 * @param mixed $string
	 * @param mixed $key
	 * @param string $salt
	 * @return
	 */
	public static function encrypt($string, $key, $salt="") {
		
		if (32 !== strlen($key)) {
			$key = hash("SHA256", $key, true);
		}
		
		if (!$string) { return false; }
	  $salted = $salt . $string;
	  $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
	  $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	  $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $salted, MCRYPT_MODE_ECB, $iv);
	  $data = base64_encode($crypttext);
	  $data = str_replace(array('+', '/', '='),array('-', '_', ''), $data);
	  
	  return trim($data);
		
	}
	
	/**
	 * crypt::decrypt()
	 * 
	 * @param mixed $string
	 * @param mixed $key
	 * @param string $salt
	 * @return
	 */
	public static function decrypt($string, $key, $salt="") {
		
		if (32 !== strlen($key)) {
			$key = hash("SHA256", $key, true);
		}
		
		if (!$string) { return false; }
		$data = str_replace(array('-', '_'), array('+', '/'), $string);
		$mod4 = strlen($data) % 4;
		if ($mod4) {
			$data .= substr('====', $mod4);
		}
		$crypttext = base64_decode($data);
	  $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
	  $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	  $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $crypttext, MCRYPT_MODE_ECB, $iv);
	  
	  $desalted = substr($decrypttext, strlen($salt));
	  
	  return trim($desalted);
		
	}
	
	// Hashing
	
	/**
	 * crypt::hashCreate()
	 * 
	 * @param mixed $string
	 * @return
	 */
	public static function hashCreate($string) {
		
		$hasher = new PasswordHash();
		
		$hash = $hasher->HashPassword($string);
		
		unset($hasher);
		
		return $hash;
		
	}
	
	/**
	 * crypt::hashCompare()
	 * 
	 * @param mixed $string
	 * @param mixed $hash
	 * @return
	 */
	public static function hashCompare($string, $hash) {
		
		$hasher = new PasswordHash();
		
		$check = $hasher->CheckPassword($string, $hash);
		
		unset($hasher);
		
		return $check;
		
	}
	
	/**
	 * myCrypt::numEncode()
	 * 
	 * @param mixed $num
	 * @param mixed $key
	 * @return
	 */
	public static function numEncode($num, $key) {
		
		$hashids = new Hashids($key, 8);
		$id = $hashids->encode($num);
		return $id;
		
	}
	
	/**
	 * myCrypt::numDecode()
	 * 
	 * @param mixed $id
	 * @param mixed $key
	 * @return
	 */
	public static function numDecode($id, $key) {
		
		$hashids = new Hashids($key, 8);
		$num = $hashids->decode($id);
		return $num[0];
		
	}
	
}


#
# Portable PHP password hashing framework.
#
# Version 0.3 / genuine.
#
# Written by Solar Designer <solar at openwall.com> in 2004-2006 and placed in
# the public domain.  Revised in subsequent years, still public domain.
#
# There's absolutely no warranty.
#
# The homepage URL for this framework is:
#
#	http://www.openwall.com/phpass/
#
# Please be sure to update the Version line if you edit this file in any way.
# It is suggested that you leave the main version number intact, but indicate
# your project name (after the slash) and add your own revision information.
#
# Please do not change the "private" password hashing method implemented in
# here, thereby making your hashes incompatible.  However, if you must, please
# change the hash type identifier (the "$P$") to something different.
#
# Obviously, since this code is in the public domain, the above are not
# requirements (there can be none), but merely suggestions.
#
class PasswordHash {
	
	private $itoa64;
	private $iteration_count_log2;
	private $portable_hashes;
	private $random_state;

	function __construct($iteration_count_log2=12, $portable_hashes=FALSE)
	{
		$this->itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

		if ($iteration_count_log2 < 4 || $iteration_count_log2 > 31)
			$iteration_count_log2 = 8;
		$this->iteration_count_log2 = $iteration_count_log2;

		$this->portable_hashes = $portable_hashes;

		$this->random_state = microtime();
		if (function_exists('getmypid'))
			$this->random_state .= getmypid();
	}

	private function get_random_bytes($count)
	{
		$output = '';
		if (is_readable('/dev/urandom') &&
		    ($fh = @fopen('/dev/urandom', 'rb'))) {
			$output = fread($fh, $count);
			fclose($fh);
		}

		if (strlen($output) < $count) {
			$output = '';
			for ($i = 0; $i < $count; $i += 16) {
				$this->random_state =
				    md5(microtime() . $this->random_state);
				$output .=
				    pack('H*', md5($this->random_state));
			}
			$output = substr($output, 0, $count);
		}

		return $output;
	}

	private function encode64($input, $count)
	{
		$output = '';
		$i = 0;
		do {
			$value = ord($input[$i++]);
			$output .= $this->itoa64[$value & 0x3f];
			if ($i < $count)
				$value |= ord($input[$i]) << 8;
			$output .= $this->itoa64[($value >> 6) & 0x3f];
			if ($i++ >= $count)
				break;
			if ($i < $count)
				$value |= ord($input[$i]) << 16;
			$output .= $this->itoa64[($value >> 12) & 0x3f];
			if ($i++ >= $count)
				break;
			$output .= $this->itoa64[($value >> 18) & 0x3f];
		} while ($i < $count);

		return $output;
	}

	private function gensalt_private($input)
	{
		$output = '$P$';
		$output .= $this->itoa64[min($this->iteration_count_log2 +
			((PHP_VERSION >= '5') ? 5 : 3), 30)];
		$output .= $this->encode64($input, 6);

		return $output;
	}

	private function crypt_private($password, $setting)
	{
		$output = '*0';
		if (substr($setting, 0, 2) == $output)
			$output = '*1';

		$id = substr($setting, 0, 3);
		# We use "$P$", phpBB3 uses "$H$" for the same thing
		if ($id != '$P$' && $id != '$H$')
			return $output;

		$count_log2 = strpos($this->itoa64, $setting[3]);
		if ($count_log2 < 7 || $count_log2 > 30)
			return $output;

		$count = 1 << $count_log2;

		$salt = substr($setting, 4, 8);
		if (strlen($salt) != 8)
			return $output;

		# We're kind of forced to use MD5 here since it's the only
		# cryptographic primitive available in all versions of PHP
		# currently in use.  To implement our own low-level crypto
		# in PHP would result in much worse performance and
		# consequently in lower iteration counts and hashes that are
		# quicker to crack (by non-PHP code).
		if (PHP_VERSION >= '5') {
			$hash = md5($salt . $password, TRUE);
			do {
				$hash = md5($hash . $password, TRUE);
			} while (--$count);
		} else {
			$hash = pack('H*', md5($salt . $password));
			do {
				$hash = pack('H*', md5($hash . $password));
			} while (--$count);
		}

		$output = substr($setting, 0, 12);
		$output .= $this->encode64($hash, 16);

		return $output;
	}

	private function gensalt_extended($input)
	{
		$count_log2 = min($this->iteration_count_log2 + 8, 24);
		# This should be odd to not reveal weak DES keys, and the
		# maximum valid value is (2**24 - 1) which is odd anyway.
		$count = (1 << $count_log2) - 1;

		$output = '_';
		$output .= $this->itoa64[$count & 0x3f];
		$output .= $this->itoa64[($count >> 6) & 0x3f];
		$output .= $this->itoa64[($count >> 12) & 0x3f];
		$output .= $this->itoa64[($count >> 18) & 0x3f];

		$output .= $this->encode64($input, 3);

		return $output;
	}

	private function gensalt_blowfish($input)
	{
		# This one needs to use a different order of characters and a
		# different encoding scheme from the one in encode64() above.
		# We care because the last character in our encoded string will
		# only represent 2 bits.  While two known implementations of
		# bcrypt will happily accept and correct a salt string which
		# has the 4 unused bits set to non-zero, we do not want to take
		# chances and we also do not want to waste an additional byte
		# of entropy.
		$itoa64 = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

		$output = '$2a$';
		$output .= chr(ord('0') + $this->iteration_count_log2 / 10);
		$output .= chr(ord('0') + $this->iteration_count_log2 % 10);
		$output .= '$';

		$i = 0;
		do {
			$c1 = ord($input[$i++]);
			$output .= $itoa64[$c1 >> 2];
			$c1 = ($c1 & 0x03) << 4;
			if ($i >= 16) {
				$output .= $itoa64[$c1];
				break;
			}

			$c2 = ord($input[$i++]);
			$c1 |= $c2 >> 4;
			$output .= $itoa64[$c1];
			$c1 = ($c2 & 0x0f) << 2;

			$c2 = ord($input[$i++]);
			$c1 |= $c2 >> 6;
			$output .= $itoa64[$c1];
			$output .= $itoa64[$c2 & 0x3f];
		} while (1);

		return $output;
	}

	public function HashPassword($password)
	{
		$random = '';

		if (CRYPT_BLOWFISH == 1 && !$this->portable_hashes) {
			$random = $this->get_random_bytes(16);
			$hash =
			    crypt($password, $this->gensalt_blowfish($random));
			if (strlen($hash) == 60)
				return $hash;
		}

		if (CRYPT_EXT_DES == 1 && !$this->portable_hashes) {
			if (strlen($random) < 3)
				$random = $this->get_random_bytes(3);
			$hash =
			    crypt($password, $this->gensalt_extended($random));
			if (strlen($hash) == 20)
				return $hash;
		}

		if (strlen($random) < 6)
			$random = $this->get_random_bytes(6);
		$hash =
		    $this->crypt_private($password,
		    $this->gensalt_private($random));
		if (strlen($hash) == 34)
			return $hash;

		# Returning '*' on error is safe here, but would _not_ be safe
		# in a crypt(3)-like function used _both_ for generating new
		# hashes and for validating passwords against existing hashes.
		return '*';
	}

	public function CheckPassword($password, $stored_hash)
	{
		$hash = $this->crypt_private($password, $stored_hash);
		
		if ($hash[0] == '*')
			$hash = crypt($password, $stored_hash);

		return $hash == $stored_hash;
	}
}


/*
	
	Hashids
	http://hashids.org/php
	(c) 2013 Ivan Akimov
	
	https://github.com/ivanakimov/hashids.php
	hashids may be freely distributed under the MIT license.
	
*/

class Hashids {
	
	const VERSION = '1.0.5';
	
	/* internal settings */
	
	const MIN_ALPHABET_LENGTH = 16;
	const SEP_DIV = 3.5;
	const GUARD_DIV = 12;
	
	/* error messages */
	
	const E_ALPHABET_LENGTH = 'alphabet must contain at least %d unique characters';
	const E_ALPHABET_SPACE = 'alphabet cannot contain spaces';
	
	/* set at constructor */
	
	private $_alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
	private $_seps = 'cfhistuCFHISTU';
	private $_min_hash_length = 0;
	private $_math_functions = array();
	private $_max_int_value = 1000000000;
	
	public function __construct($salt = '', $min_hash_length = 0, $alphabet = '') {
		
		/* if either math precision library is present, raise $this->_max_int_value */
		
		if (function_exists('gmp_add')) {
			$this->_math_functions['add'] = 'gmp_add';
			$this->_math_functions['div'] = 'gmp_div';
			$this->_math_functions['str'] = 'gmp_strval';
		} else if (function_exists('bcadd')) {
			$this->_math_functions['add'] = 'bcadd';
			$this->_math_functions['div'] = 'bcdiv';
			$this->_math_functions['str'] = 'strval';
		}
		
		$this->_lower_max_int_value = $this->_max_int_value;
		if ($this->_math_functions) {
			$this->_max_int_value = PHP_INT_MAX;
		}
		
		/* handle parameters */
		
		$this->_salt = $salt;
		
		if ((int)$min_hash_length > 0) {
			$this->_min_hash_length = (int)$min_hash_length;
		}
		
		if ($alphabet) {
			$this->_alphabet = implode('', array_unique(str_split($alphabet)));
		}
		
		if (strlen($this->_alphabet) < self::MIN_ALPHABET_LENGTH) {
			throw new \Exception(sprintf(self::E_ALPHABET_LENGTH, self::MIN_ALPHABET_LENGTH));
		}
		
		if (is_int(strpos($this->_alphabet, ' '))) {
			throw new \Exception(self::E_ALPHABET_SPACE);
		}
		
		$alphabet_array = str_split($this->_alphabet);
		$seps_array = str_split($this->_seps);
		
		$this->_seps = implode('', array_intersect($alphabet_array, $seps_array));
		$this->_alphabet = implode('', array_diff($alphabet_array, $seps_array));
		$this->_seps = $this->_consistent_shuffle($this->_seps, $this->_salt);
		
		if (!$this->_seps || (strlen($this->_alphabet) / strlen($this->_seps)) > self::SEP_DIV) {
			
			$seps_length = (int)ceil(strlen($this->_alphabet) / self::SEP_DIV);
			
			if ($seps_length == 1) {
				$seps_length++;
			}
			
			if ($seps_length > strlen($this->_seps)) {
				
				$diff = $seps_length - strlen($this->_seps);
				$this->_seps .= substr($this->_alphabet, 0, $diff);
				$this->_alphabet = substr($this->_alphabet, $diff);
				
			} else {
				$this->_seps = substr($this->_seps, 0, $seps_length);
			}
			
		}
		
		$this->_alphabet = $this->_consistent_shuffle($this->_alphabet, $this->_salt);
		$guard_count = (int)ceil(strlen($this->_alphabet) / self::GUARD_DIV);
		
		if (strlen($this->_alphabet) < 3) {
			$this->_guards = substr($this->_seps, 0, $guard_count);
			$this->_seps = substr($this->_seps, $guard_count);
		} else {
			$this->_guards = substr($this->_alphabet, 0, $guard_count);
			$this->_alphabet = substr($this->_alphabet, $guard_count);
		}
		
	}
	
	public function encode() {
		
		$ret = '';
		$numbers = func_get_args();
		
		if (func_num_args() == 1 && is_array(func_get_arg(0))) {
			$numbers = $numbers[0];
		}
		
		if (!$numbers) {
			return $ret;
		}
		
		foreach ($numbers as $number) {
			
			$is_number = ctype_digit((string)$number);
			
			if (!$is_number || $number < 0 || $number > $this->_max_int_value) {
				return $ret;
			}
			
		}
		
		return $this->_encode($numbers);
		
	}
	
	public function decode($hash) {
		
		$ret = array();
		
		if (!$hash || !is_string($hash) || !trim($hash)) {
			return $ret;
		}
		
		return $this->_decode(trim($hash), $this->_alphabet);
		
	}
	
	public function encode_hex($str) {
		
		if (!ctype_xdigit((string)$str)) {
			return '';
		}
		
		$numbers = trim(chunk_split($str, 12, ' '));
		$numbers = explode(' ', $numbers);
		
		foreach ($numbers as $i => $number) {
			$numbers[$i] = hexdec('1' . $number);
		}
		
		return call_user_func_array(array($this, 'encode'), $numbers);
		
	}
	
	public function decode_hex($hash) {
		
		$ret = "";
		$numbers = $this->decode($hash);
		
		foreach ($numbers as $i => $number) {
			$ret .= substr(dechex($number), 1);
		}
		
		return $ret;
		
	}
	
	public function get_max_int_value() {
		return $this->_max_int_value;
	}
	
	private function _encode(array $numbers) {
		
		$alphabet = $this->_alphabet;
		$numbers_size = sizeof($numbers);
		$numbers_hash_int = 0;
		
		foreach ($numbers as $i => $number) {
			$numbers_hash_int += ($number % ($i + 100));
		}
		
		$lottery = $ret = $alphabet[$numbers_hash_int % strlen($alphabet)];
		foreach ($numbers as $i => $number) {
			
			$alphabet = $this->_consistent_shuffle($alphabet, substr($lottery . $this->_salt . $alphabet, 0, strlen($alphabet)));
			$ret .= $last = $this->_hash($number, $alphabet);
			
			if ($i + 1 < $numbers_size) {
				$number %= (ord($last) + $i);
				$seps_index = $number % strlen($this->_seps);
				$ret .= $this->_seps[$seps_index];
			}
			
		}
		
		if (strlen($ret) < $this->_min_hash_length) {
			
			$guard_index = ($numbers_hash_int + ord($ret[0])) % strlen($this->_guards);
			
			$guard = $this->_guards[$guard_index];
			$ret = $guard . $ret;
			
			if (strlen($ret) < $this->_min_hash_length) {
				
				$guard_index = ($numbers_hash_int + ord($ret[2])) % strlen($this->_guards);
				$guard = $this->_guards[$guard_index];
				
				$ret .= $guard;
				
			}
			
		}
		
		$half_length = (int)(strlen($alphabet) / 2);
		while (strlen($ret) < $this->_min_hash_length) {
			
			$alphabet = $this->_consistent_shuffle($alphabet, $alphabet);
			$ret = substr($alphabet, $half_length) . $ret . substr($alphabet, 0, $half_length);
			
			$excess = strlen($ret) - $this->_min_hash_length;
			if ($excess > 0) {
				$ret = substr($ret, $excess / 2, $this->_min_hash_length);
			}
			
		}
		
		return $ret;
		
	}
	
	private function _decode($hash, $alphabet) {
		
		$ret = array();
		
		$hash_breakdown = str_replace(str_split($this->_guards), ' ', $hash);
		$hash_array = explode(' ', $hash_breakdown);
		
		$i = 0;
		if (sizeof($hash_array) == 3 || sizeof($hash_array) == 2) {
			$i = 1;
		}
		
		$hash_breakdown = $hash_array[$i];
		if (isset($hash_breakdown[0])) {
			
			$lottery = $hash_breakdown[0];
			$hash_breakdown = substr($hash_breakdown, 1);
			
			$hash_breakdown = str_replace(str_split($this->_seps), ' ', $hash_breakdown);
			$hash_array = explode(' ', $hash_breakdown);
			
			foreach ($hash_array as $sub_hash) {
				$alphabet = $this->_consistent_shuffle($alphabet, substr($lottery . $this->_salt . $alphabet, 0, strlen($alphabet)));
				$ret[] = (int)$this->_unhash($sub_hash, $alphabet);
			}
			
			if ($this->_encode($ret) != $hash) {
				$ret = array();
			}
			
		}
		
		return $ret;
		
	}
	
	private function _consistent_shuffle($alphabet, $salt) {
		
		if (!strlen($salt)) {
			return $alphabet;
		}
		
		for ($i = strlen($alphabet) - 1, $v = 0, $p = 0; $i > 0; $i--, $v++) {
			
			$v %= strlen($salt);
			$p += $int = ord($salt[$v]);
			$j = ($int + $v + $p) % $i;
			
			$temp = $alphabet[$j];
			$alphabet[$j] = $alphabet[$i];
			$alphabet[$i] = $temp;
			
		}
		
		return $alphabet;
		
	}
	
	private function _hash($input, $alphabet) {
		
		$hash = '';
		$alphabet_length = strlen($alphabet);
		
		do {
			
			$hash = $alphabet[$input % $alphabet_length] . $hash;
			if ($input > $this->_lower_max_int_value && $this->_math_functions) {
				$input = $this->_math_functions['str']($this->_math_functions['div']($input, $alphabet_length));
			} else {
				$input = (int)($input / $alphabet_length);
			}
			
		} while ($input);
		
		return $hash;
		
	}
	
	private function _unhash($input, $alphabet) {
		
		$number = 0;
		if (strlen($input) && $alphabet) {
			
			$alphabet_length = strlen($alphabet);
			$input_chars = str_split($input);
			
			foreach ($input_chars as $i => $char) {
				
				$pos = strpos($alphabet, $char);
				if ($this->_math_functions) {
					$number = $this->_math_functions['str']($this->_math_functions['add']($number, $pos * pow($alphabet_length, (strlen($input) - $i - 1))));
				} else {
					$number += $pos * pow($alphabet_length, (strlen($input) - $i - 1));
				}
				
			}
			
		}
		
		return $number;
		
	}
	
}


?>