<?php

/**
 * pagination
 * 
 * @package 26horas Shared
 * @author Pablo Fernandez - pablo.fernandez@26horas.com
 * @copyright 2015 Pablo Fernandez
 * @version 0.01 // 2015-01-29
 * @access public
 */
 
/**
 * Changelog:
 * 
 * 2015-01-29:	New PHP 5 format.
 * 2014-03-11:	Added force query.
 * 2014-02-23:	Added LIMIT setting.
 * 2013-11-15:	Added $table var.
 * 							Added $fields var.
 * 							Added $where var.
 * 							Changed behaviour when counting total values (COUNT() insted of complete query).
 * 							Fields list is now customizable.
 * 2013-10-29:	First version.
 */

class pagination {
	
	// General settings of paginations
	public $items_page = 25; // Items to show per page
	public $debug = false; // Debug mode (shows database errors)
	public $buttons_jump = 5; // Number for n pages, 0 for beggining/end
	
	// Input settings
	public $query = array(
												'table' => "", // Table we are going to query
												'fields' => array(), // Fields to retrieve form database (if empty array, * is used)
												'where' => "", // Where clausules for query (WHERE not included)
												'sorting-field' => "", // Query sorting field
												'sorting-order' => "", // Query sorting order
												'sorting-extra' => "", // Extra sorting options
												'limit' => "" // Query limit
												);
	public $query_forced = ""; // Forces a query
	public $page = 1; // Page to load
	
	// Output settings
	public $items_total = 0; // Total amount of items of query
	public $pages_total = 0; // Total amount of pages
	public $page_num = 1; // Current page
	public $items = array(); // List of items of current page
	public $error_msg = ""; // Error message
	public $buttons = array( // Buttons presence in current page
													'prev_jump' => false, 
													'prev' => false, 
													'next' => false, 
													'next_jump' => false
													);
	
	// Internal use
  private $db_connection; // Stores database mysqli connection
  private $q;
  
	
	/**
	 * pagination::__construct()
	 * 
	 * @param mixed $options
	 * @return void
	 */
	public function __construct($options=array()) {
		
		// ================================================================
		// Class Constructor
		
		$this->items_page = $this->setVar($options, "items_page", "integer");
		$this->buttons_jump = $this->setVar($options, "buttons_jump", "integer");
		$this->debug = $this->setVar($options, "debug", "boolean");
		$this->db_connection = $this->setVar($options, "db_connection", "object");
		
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
	 * pagination::go()
	 * 
	 * @return
	 */
	public function go() {
		
		// ================================================================
		// Executes pagination
		
		// Page number
		$this->page_num = $this->page;
		
		if (!is_numeric($this->page_num)) {
			$this->page_num = 1;
		}
	 	
		// Total number of items
		if ($this->query_forced == "") {
			
			if ($this->query['table'] != "") {
				
				$query_total = "SELECT COUNT(*) AS num FROM " . $this->query['table'];
				if ($this->query['where'] != "") {
					$query_total .= " WHERE " . str_ireplace("WHERE", "", $this->query['where']);
				}
				$r = $this->dbQuery($query_total);
				if (!$r) {
					$this->error_msg = "Error accessing database";
					return false;
				}
				
				list($this->items_total) = $this->value($r);
				
			} else {
				
				$this->error_msg = "No table or id supplied";
				return false;
				
			}
			
		} else {
			
			$r = $this->dbQuery($this->query_forced);
			if (!$r) {
				$this->error_msg = "Error accessing database";
				return false;
			}
			
			$this->items_total = $this->dbNumRows($r);
			
		}
		
		// Total number of pages
		$this->pages_total = ceil($this->items_total / $this->items_page);
		
		// Page number check
		if ($this->page_num > $this->pages_total && $this->pages_total > 0) {
			$this->page_num = $this->pages_total;
		}
		
		// Start of list
		$list_start = ($this->page_num - 1) * $this->items_page;
		
		// Building final query
		if ($this->query_forced != "") {
			
			$this->q = $this->query_forced;
			
		} else {
			
			$this->q = "SELECT ";
			
			if (empty($this->fields)) {
				$this->q .= " * ";
			} else {
				$this->q .= " " . implode(",", $this->fields) . " ";
			}
			$this->q .= " FROM " . $this->query['table'];
			if ($this->query['where'] != "") {
				$this->q .= " WHERE " . str_ireplace("WHERE", "", $this->query['where']);
			}
			
		}
		
		if (strpos(strtoupper($this->q), " ORDER BY ") === false
				&& $this->query['sorting-field'] != "") {
			$this->q .= " ORDER BY " . $this->query['sorting-field'] . " " . $this->query['sorting-order'];
		}
		if ($this->query['sorting-extra'] != "") {
			$this->q .= ", " . $this->query['sorting-extra'];
		}
		
		if ($this->query['limit'] == "") {
			$this->q .= " LIMIT " . $list_start . "," . $this->items_page;
		} else {
			$this->q .= " LIMIT " . $this->query['limit'];
		}
		
		$r = $this->dbQuery($this->q);
		if (!$r) {
			$this->error_msg = "Error accessing database";
			return false;
		}
		
		// Creating array of items
		while ($i = $this->dbFetchAssoc($r)) {
			array_push($this->items, $i);
		}
		
		// Buttons presence
		$this->buttons['prev'] = ($this->page_num > 1) ? true : false;
		$this->buttons['next'] = ($this->page_num < $this->pages_total && $this->pages_total > 1) ? true : false;
		if ($this->buttons_jump == 0) {
			$this->buttons['prev_jump'] = ($this->page_num > 1) ? true : false;
			$this->buttons['next_jump'] = ($this->page_num < $this->pages_total && $this->pages_total > 1) ? true : false;
		} else {
			$this->buttons['prev_jump'] = ($this->page_num > $this->buttons_jump) ? true : false;
			$this->buttons['next_jump'] = ($this->page_num < $this->pages_total - $this->buttons_jump + 1 && $this->pages_total > 1) ? true : false;
		}
		
		$this->pages_total = ($this->pages_total == 0) ? 1 : $this->pages_total;
		
		return true;
		
	}
	
	// --------------------------------------------------------------
	// Misc functions
	
	function dbQuery($q) {
		
		if ($this->db_connection === null) {
			$this->error_msg = "No database connection";
			return false;
		}
		$r = @$this->db_connection->query($q);
		if (!$r) {
			if ($this->debug) {
				$this->error_msg = "Database error: " . $this->db_connection->error();
			} else {
				$this->error_msg = "Database error";
			}
			return false;
		} else {
			return $r;
		}
		
	}
	
	function dbNumRows($r) {
		return $r->num_rows;
	}
	
	function dbFetchAssoc($r) {
		return $r->fetch_assoc();
	}
	
	function value($r) {
		return $r->fetch_row();
	}
	
}

?>