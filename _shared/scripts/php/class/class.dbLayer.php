<?php

/**
 * dbLayer
 * 
 * @package 26horas Shared
 * @author Pablo Fernandez - pablo.fernandez@26horas.com
 * @copyright 2015 Pablo Fernandez
 * @version 0.02 // 2015-01-16
 * @access public
 */
 
/**
 * Changelog:
 * 2015-03-31:	Added dbLayer::selectDB().
 * 2015-01-16:	Modified error message CSS to properly reset styles.
 * 2015-01-15: 	Added dbLayer::values() and dbLayer::assoc() methods.
 * 2015-01-09: 	First version.
 */

class dbLayer {
	
	public $connection;
	public $connected = false;
	
	private $debug = false;
	private $force_errors = false;
	private $error_template = "";
	
	/**
	 * dbLayer::__construct()
	 * 
	 * @param mixed $options
	 * @return
	 */
	function __construct($options=array()) {
		
		$this->debug = $this->setVar($options, "debug", "boolean");
		$this->force_errors = $this->setVar($options, "force_errors", "boolean");
		$this->error_template = $this->setVar($options, "error_template", "string");
		
	}
	
	/**
	 * dbLayer::setVar()
	 * 
	 * @param mixed $options
	 * @param mixed $option
	 * @param string $type
	 * @return void
	 */
	private function setVar($options, $option, $type="string") {
		
		if (is_array($options)) {
			if (isset($options[$option]) && gettype($options[$option]) == $type) {
				return $options[$option];
			}
		}
		
		return $this->$option;
		
	}
	
	/**
	 * dbLayer::connect()
	 * 
	 * @param mixed $host
	 * @param mixed $db
	 * @param mixed $user
	 * @param mixed $pass
	 * @return
	 */
	public function connect($host, $db, $user, $pass) {
		
		$this->connection = new mysqli($host, $user, $pass, $db);
		
		if ($this->connection->connect_errno) {
			$this->connected = false;
			return false;
		}
		
		$this->connected = true;
		return true;
		
	}
	
	/**
	 * dbLayer::query()
	 * 
	 * @param mixed $q
	 * @param bool $show_error
	 * @return
	 */
	public function query($q, $show_error=false) {
		
		$r = $this->connection->query($q);
		
		if (!$r 
				&& ($show_error || $this->force_errors)) {
			$this->error();
		}
		
		return $r;
		
	}
	
	/**
	 * dbLayer::values()
	 * 
	 * @param mixed $r
	 * @return
	 */
	function values($r) {
		
		return $r->fetch_row();
		
	}
	
	/**
	 * dbLayer::assoc()
	 * 
	 * @param mixed $r
	 * @return
	 */
	function assoc($r) {
		
		return $r->fetch_assoc();
		
	}
	
	/**
	 * dbLayer::getValues()
	 * 
	 * @param mixed $r
	 * @return
	 */
	public function getValues($r) {
		
		$output = array();
		
		if (is_string($r)) {
			$r = $this->query($r);
		}
		
		if ($r) {
				
			$rows = $r->num_rows;
			
			if ($rows == 1) {
				
				$output = $r->fetch_row();
				
				if (count($output) == 1) {
					$output = $output[0];
				}
				
			} else {
				
				while ($t = $r->fetch_row()) {
					array_push($output, $t);
				}
				
			}
			
		}
		
		return $output;
		
	}
	
	/**
	 * dbLayer::getAssoc()
	 * 
	 * @param mixed $r
	 * @return
	 */
	public function getAssoc($r) {
		
		$output = array();
		
		if (is_string($r)) {
			$r = $this->query($r);
		}
		
		if ($r) {
			
			$rows = $r->num_rows;
			
			if ($rows == 1) {
				
				$output = $r->fetch_assoc();
				
			} else {
				
				while ($t = $r->fetch_assoc()) {
					array_push($output, $t);
				}
				
			}
			
		}
		
		return $output;
		
	}
	
	/**
	 * dbLayer::numRows()
	 * 
	 * @param mixed $r
	 * @return
	 */
	public function numRows($r) {
		
		$output = 0;
		
		if (is_string($r)) {
			$r = $this->query($r);
		}
		
		if ($r) {
			
			$output = $r->num_rows;
			
		}
		
		return $output;
		
	}
	
	/**
	 * dbLayer::affectedRows()
	 * 
	 * @return
	 */
	public function affectedRows() {
		
		return $this->connection->affected_rows;
		
	}
	
	/**
	 * dbLayer::insertId()
	 * 
	 * @return
	 */
	public function insertId() {
		
		return $this->connection->insert_id;
		
	}
	
	/**
	 * dbLayer::selectDB()
	 * 
	 * @param mixed $db
	 * @return
	 */
	public function selectDB($db) {
		
		return $this->connection->select_db($db);
		
	}
	
	/**
	 * dbLayer::escape()
	 * 
	 * @param mixed $string
	 * @return
	 */
	public function escapeString($string) {
		
		return $this->connection->real_escape_string($string);
		
	}
	
	/**
	 * dbLayer::close()
	 * 
	 * @return
	 */
	public function close() {
		
		$this->connection->close();
		
	}
	
	/**
	 * dbLayer::error()
	 * 
	 * @return
	 */
	public function error($forced_text="") {
		
		$error = !$this->connection->connect_errno ? $this->connection->error : "";
		
		if ($this->error_template == "") {
			
			$error_txt = $this->debug ? "<p><strong>Código del error:</strong><br /><small>" . $error . "</smal></strong></p>" : "";
			
			if ($forced_text != "") {
				$error_txt = "<p><strong>" . $forced_text . "</strong></p>";
			}
			
			$output = "<!DOCTYPE html><html><meta charset=\"utf-8\" /><head><title>Error</title>
								<script src=\"//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js\"></script>
								</head><body>
								<div id=\"dberr_overlay\"></div>
								<div id=\"dberr_cont\">
									<h1>Se ha producido un error</h1>
									" . $error_txt . "
									<p>Rogamos disculpen las molestias.</p>
								</div>
								<script>
								$('#dberr_overlay').css({
									'position': 'absolute', 
									'width': $(document).width()+'px', 
									'height': $(document).height()+'px', 
									'left': '0px', 
									'top': '0px', 
									'background': 'rgba(0, 0, 0, 0.85)', 
									'z-index': 999999998
								});
								$('#dberr_cont').css({
									'position': 'absolute', 
									'width': '360px', 
									'height': '180px', 
									'left': '50%', 
									'top': '50%', 
									'padding': '10px 20px', 
									'margin': '-100px 0px 0px -200px', 
									'border': '6px solid #bbbbbb', 
									'border-radius': '8px', 
									'background': '#ffffff', 
									'font-family': 'Arial', 
									'z-index': 999999999
								});
								$('#dberr_cont h1').css({
									'display': 'block', 
									'width': 'auto', 
									'height': 'auto', 
									'margin': '0px', 
									'padding': '10px 0 15px 0', 
									'color': '#aa0000', 
									'font-size': '18px', 
									'line-height': '22px', 
									'font-weight': 'bold', 
									'text-align': 'left'
								});
								$('#dberr_cont p').css({
									'display': 'block', 
									'width': 'auto', 
									'height': 'auto', 
									'margin': '0px', 
									'padding': '0 0 10px 0', 
									'background': '#ffffff', 
									'border-radius': '0', 
									'border': 'none', 
									'color': '#555555', 
									'font-size': '14px', 
									'line-height': '18px', 
									'font-weight': 'normal', 
									'font-style': 'none', 
									'text-align': 'left'
								});
								$('#dberr_cont p strong').css({
									'font-weight': 'bold'
								});
								$('#dberr_cont p small').css({
									'font-size': '12px'
								});
								</script>
								</body></html>";
								
		} else {
			
			$error_txt = $this->debug ? $error : "";
			
			if ($forced_text != "") {
				$error_txt = $forced_text;
			}
			
			$output = sprintf($this->error_template, $error_txt);
			
		}
		
		die(str_replace("\t", "", $output));
	
	}

}

?>