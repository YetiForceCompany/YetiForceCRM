<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Vtiger_Response {
	// Constants

	/**
	 * Emit response wrapper as raw string
	 */
	static $EMIT_RAW = 0;

	/**
	 * Emit response wrapper as json string
	 */
	static $EMIT_JSON= 1;

	/**
	 * Emit response wrapper as html string
	 */
	static $EMIT_HTML= 2;

	/**
	 * Emit response wrapper as string/jsonstring
	 */
	static $EMIT_JSONTEXT= 3;

	/**
	 * Emit response wrapper as padded-json
	 */
	static $EMIT_JSONP = 4;

	/**
	 * Error data.
	 */
	private $error = NULL;
	/**
	 * Result data.
	 */
	private $result = NULL;

	/* Active emit type */
	private $emitType= 1; // EMIT_JSON

	/* JSONP padding */
	private $emitJSONPFn=false;// for EMIT_JSONP

	/* List of response headers */
	private $headers = array();

	/**
	 * Set headers to send
	 */
	function setHeader($header) {
		$this->headers[] = $header;
	}

	/**
	 * Set error data to send
	 */
	function setError($code, $message=null) {
		if ($message == null) $message = $code;
		$error = array('code' => $code, 'message' => $message);
		$this->error = $error;
	}

	/**
	 * Set emit type.
	 */
	function setEmitType($type) {
		$this->emitType = $type;
	}

	/**
	 * Set padding method name for JSONP emit type.
	 */
	function setEmitJSONP($fn) {
		$this->setEmitType(self::$EMIT_JSONP);
		$this->emitJSONPFn = $fn;
	}

	/**
	 * Is emit type configured to JSON?
	 */
	function isJSON() {
		return $this->emitType == self::$EMIT_JSON;
	}

	/**
	 * Get the error data
	 */
	function getError() {
		return $this->error;
	}

	/**
	 * Check the presence of error data
	 */
	function hasError() {
		return !is_null($this->error);
	}

	/**
	 * Set the result data.
	 */
	function setResult($result) {
		$this->result = $result;
	}

	/**
	 * Update the result data.
	 */
	function updateResult($key, $value) {
		$this->result[$key] = $value;
	}

	/**
	 * Get the result data.
	 */
	function getResult() {
		return $this->result;
	}

	/**
	 * Prepare the response wrapper.
	 */
	protected function prepareResponse() {
		$response = array();
		if($this->error !== NULL) {
			$response['success'] = false;
			$response['error'] = $this->error;
		} else {
			$response['success'] = true;
			$response['result'] = $this->result;
		}
		return $response;
	}

	/**
	 * Send response to client.
	 */
	function emit() {

		$contentTypeSent = false;
		foreach ($this->headers as $header) {
			if (!$contentTypeSent && stripos($header, 'content-type') === 0) { $contentTypeSent = true; }
			header($header);
		}

		/* Set right charset (UTF-8) to avoid IE complaining about c00ce56e error */
		if ($this->emitType == self::$EMIT_JSON) {
			if (!$contentTypeSent) header('Content-type: text/json; charset=UTF-8');
			$this->emitJSON();
		} else if ($this->emitType == self::$EMIT_JSONTEXT){
			if (!$contentTypeSent) header('Content-type: text/json; charset=UTF-8');
			$this->emitText();
		} else if ($this->emitType == self::$EMIT_HTML){
			if (!$contentTypeSent) header('Content-type: text/html; charset=UTF-8');
			$this->emitRaw();
		} else if ($this->emitType == self::$EMIT_RAW) {
			if (!$contentTypeSent) header('Content-type: text/plain; charset=UTF-8');
			$this->emitRaw();
		} else if ($this->emitType == self::$EMIT_JSONP) {
			if (!$contentTypeSent) header('Content-type: application/javascript; charset=UTF-8');
			echo $this->emitJSONPFn . "(";
			$this->emitJSON();
			echo ")";
		}
	}

	/**
	 * Emit response wrapper as JSONString
	 */
	protected function emitJSON() {
		echo Zend_Json::encode($this->prepareResponse());
	}

	/**
	 * Emit response wrapper as String/JSONString
	 */
	protected function emitText() {
		if ($this->result === NULL) {
			if (is_string($this->error)) echo $this->error;
			else echo Zend_Json::encode($this->prepareResponse());
		} else {
			if (is_string($this->result)) echo $this->result;
			else echo Zend_Json::encode($this->prepareResponse());
		}
	}

	/**
	 * Emit response wrapper as String.
	 */
	protected function emitRaw() {
		if($this->result === NULL) echo (is_string($this->error))? $this->error : var_export($this->error, true);
		echo $this->result;
	}

}