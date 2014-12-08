<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
include 'vtlib/thirdparty/network/Request.php';

/**
 * Provides API to work with HTTP Connection.
 * @package vtlib
 */
class Vtiger_Net_Client {
	var $client;
	var $url;
	var $response;

	/**
	 * Constructor
	 * @param String URL of the site
	 * Example: 
	 * $client = new Vtiger_New_Client('http://www.vtiger.com');
	 */
	function __construct($url) {
		$this->setURL($url);
	}

	/**
	 * Set another url for this instance
	 * @param String URL to use go forward
	 */
	function setURL($url) {
		$this->url = $url;
		$this->client = new HTTP_Request();
		$this->response = false;
		$this->setDefaultHeaders();
	}
	
	function setDefaultHeaders() {
		$headers = array();
		if (isset($_SERVER)) {
			global $site_URL;
			$headers['referer'] = isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER'] : ($site_URL . "?noreferer");
			
			if (isset($_SERVER['HTTP_USER_AGENT'])) {
				$headers['user-agent'] = $_SERVER['HTTP_USER_AGENT'];
			}
			
		} else {
			global $site_URL;
			$headers['referer'] = ($site_URL . "?noreferer");
		}
		
		$this->setHeaders($headers);
	}

	/**
	 * Set custom HTTP Headers
	 * @param Map HTTP Header and Value Pairs
	 */
	function setHeaders($values) {
		foreach($values as $key=>$value) {
			$this->client->addHeader($key, $value);
		}
	}
	
	/**
	 * Perform a GET request
	 * @param Map key-value pair or false
	 * @param Integer timeout value
	 */
	function doGet($params=false, $timeout=null) {
		if($timeout) $this->client->_timeout = $timeout;
		$this->client->setURL($this->url);
		$this->client->setMethod(HTTP_REQUEST_METHOD_GET);

		if($params) {
			foreach($params as $key=>$value) 
				$this->client->addQueryString($key, $value);
		}
		$this->response = $this->client->sendRequest();

		$content = false;
		if(!$this->wasError()) {
			$content = $this->client->getResponseBody();
		}
		$this->disconnect();
		return $content;
	}

	/**
	 * Perform a POST request
	 * @param Map key-value pair or false
	 * @param Integer timeout value
	 */
	function doPost($params=false, $timeout=null) {
		if($timeout) $this->client->_timeout = $timeout;
		$this->client->setURL($this->url);
		$this->client->setMethod(HTTP_REQUEST_METHOD_POST);

		if($params) {
			if(is_string($params)) $this->client->addRawPostData($params);
			else {
				foreach($params as $key=>$value)
					$this->client->addPostData($key, $value);
			}
		}
		$this->response = $this->client->sendRequest();

		$content = false;
		if(!$this->wasError()) {
			$content = $this->client->getResponseBody();
		}
		$this->disconnect();

		return $content;
	}

	/**
	 * Did last request resulted in error?
	 */
	function wasError() {
		return PEAR::isError($this->response);
	}

	/**
	 * Disconnect this instance
	 */
	function disconnect() {
		$this->client->disconnect();
	}
}
?>
