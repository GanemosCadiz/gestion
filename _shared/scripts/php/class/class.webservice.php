<?php

/**
 * webservice
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
 * 2015-03-27:	Added webserviceClient::last_request.
 * 							Changed webserviceCliente::call() behaviour, using __soapCall().
 * 2015-03-13:	First version.
 */

class webserviceClient {
	
	public $client;
	public $error = false;
	public $error_msg = "";
	public $last_request;
	public $version = SOAP_1_1;
	
	// Internal
	private $wsdl = true;
	private $url = "";
	
	/**
	 * pagination::__construct()
	 * 
	 * @param mixed $options
	 * @return void
	 */
	public function __construct($options=array()) {
		
		// Class Constructor
		
		$this->wsdl = $this->setVar($options, "wsdl", "boolean");
		$this->url = $this->setVar($options, "url", "string");
		
		if (!class_exists("SoapClient")) {
			$this->errorSet("noclass");
			return false;
		}
		
		$this->connect();
		
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
	 * webserviceClient::connect()
	 * 
	 * @return
	 */
	private function connect() {
		
		if ($this->url == "") {
			$this->errorSet("No url specified");
			return false;
		}
		
		$url = $this->wsdl ? $this->url . (strpos($this->url, "?") === false ? "?WSDL" : "&WSDL") : $this->url;
		
		$options = array(
			'version' => $this->version, 
			'cache_wsdl' => WSDL_CACHE_NONE, 
			'trace' => true
		);
		
		try {
			
			$this->client = new SoapClient($url, $options);
			
		}	catch(SoapFault $e) {
			
			$this->errorSet($e->getMessage());
			return false;
			
		}
		
	}
	
	/**
	 * webserviceClient::call()
	 * 
	 * @param mixed $function
	 * @param mixed $data
	 * @return void
	 */
	public function call($function, $data) {
		
		try {
			
			$result = $this->client->__soapCall($function, $data);
			
			$this->last_request = $this->client->__getLastRequest();
			
		}	catch(SoapFault $e) {
			
			$this->errorSet($e->getMessage());
			return false;
			
		}
		
		return $result;
		
	}
	
	/**
	 * webserviceClient::errorSet()
	 * 
	 * @param mixed $msg
	 * @return void
	 */
	private function errorSet($msg) {
		
		$this->error = true;
		$this->error_msg = $msg;
		
	}
	
}

?>