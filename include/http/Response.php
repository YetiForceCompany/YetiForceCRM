<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Vtiger_Response
{
	// Constants

	/**
	 * Emit response wrapper as raw string.
	 */
	public static $EMIT_RAW = 0;

	/**
	 * Emit response wrapper as json string.
	 */
	public static $EMIT_JSON = 1;

	/**
	 * Emit response wrapper as html string.
	 */
	public static $EMIT_HTML = 2;

	/**
	 * Emit response wrapper as string/jsonstring.
	 */
	public static $EMIT_JSONTEXT = 3;

	/**
	 * Emit response wrapper as padded-json.
	 */
	public static $EMIT_JSONP = 4;

	/**
	 * Error data.
	 */
	private $error;

	/**
	 * Result data.
	 */
	private $result;

	// Active emit type
	private $emitType = 1; // EMIT_JSON

	// JSONP padding
	private $emitJSONPFn = false; // for EMIT_JSONP

	// List of response headers
	private $headers = [];

	/**
	 * Set headers to send.
	 *
	 * @param mixed $header
	 */
	public function setHeader($header)
	{
		$this->headers[] = $header;
	}

	/**
	 * Set error data to send.
	 *
	 * @param mixed      $code
	 * @param mixed|null $message
	 * @param mixed      $trace
	 *
	 * @return void
	 */
	public function setError($code = 500, $message = null, $trace = false): void
	{
		if (null === $message) {
			$message = $code;
		}
		$error = ['code' => $code, 'message' => $message, 'trace' => $trace];
		$this->error = $error;
		if (is_numeric($code)) {
			http_response_code($code);
		}
	}

	/**
	 * Set exception error to send.
	 *
	 * @param Throwable $e
	 *
	 * @return void
	 */
	public function setException(Throwable $e): void
	{
		$error = [
			'code' => $e->getCode(),
		];
		$statusCode = \is_int($e->getCode()) ? $e->getCode() : 500;
		if (\Config\Debug::$DISPLAY_EXCEPTION_BACKTRACE) {
			$error['trace'] = str_replace(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR, '', $e->getTraceAsString());
		}
		$message = $e->getMessage();
		$show = \Config\Debug::$EXCEPTION_ERROR_TO_SHOW || 0 === strpos($message, 'ERR_');
		$reasonPhrase = $error['message'] = $show ? $message : \App\Language::translate('ERR_OCCURRED_ERROR');
		if ($show && ($e instanceof \App\Exceptions\AppException)) {
			$error['message'] = $e->getDisplayMessage();
		}
		$this->setHeader(\App\Request::_getServer('SERVER_PROTOCOL') . ' ' . $statusCode . ' ' . str_ireplace(["\r\n", "\r", "\n"], ' ', $reasonPhrase));
		$this->error = $error;
		http_response_code($statusCode);
	}

	/**
	 * Set emit type.
	 *
	 * @param mixed $type
	 */
	public function setEmitType($type)
	{
		$this->emitType = $type;
	}

	/**
	 * Set padding method name for JSONP emit type.
	 *
	 * @param mixed $fn
	 */
	public function setEmitJSONP($fn)
	{
		$this->setEmitType(self::$EMIT_JSONP);
		$this->emitJSONPFn = $fn;
	}

	/**
	 * Is emit type configured to JSON?
	 */
	public function isJSON()
	{
		return $this->emitType == self::$EMIT_JSON;
	}

	/**
	 * Get the error data.
	 */
	public function getError()
	{
		return $this->error;
	}

	/**
	 * Check the presence of error data.
	 */
	public function hasError()
	{
		return null !== $this->error;
	}

	/**
	 * Set the result data.
	 *
	 * @param mixed $result
	 */
	public function setResult($result)
	{
		$this->result = $result;
	}

	/**
	 * Update the result data.
	 *
	 * @param mixed $key
	 * @param mixed $value
	 */
	public function updateResult($key, $value)
	{
		$this->result[$key] = $value;
	}

	/**
	 * Get the result data.
	 */
	public function getResult()
	{
		return $this->result;
	}

	/**
	 * Prepare the response wrapper.
	 */
	protected function prepareResponse()
	{
		$response = [];
		if (null !== $this->error) {
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
	public function emit()
	{
		$contentTypeSent = false;
		foreach ($this->headers as $header) {
			if (!$contentTypeSent && 0 === stripos($header, 'content-type')) {
				$contentTypeSent = true;
			}
			header($header);
		}

		// Set right charset (UTF-8) to avoid IE complaining about c00ce56e error
		if ($this->emitType == self::$EMIT_JSON) {
			if (!$contentTypeSent) {
				header('content-type: text/json; charset=UTF-8');
			}
			$this->emitJSON();
		} elseif ($this->emitType == self::$EMIT_JSONTEXT) {
			if (!$contentTypeSent) {
				header('content-type: text/json; charset=UTF-8');
			}
			$this->emitText();
		} elseif ($this->emitType == self::$EMIT_HTML) {
			if (!$contentTypeSent) {
				header('content-type: text/html; charset=UTF-8');
			}
			$this->emitRaw();
		} elseif ($this->emitType == self::$EMIT_RAW) {
			if (!$contentTypeSent) {
				header('content-type: text/plain; charset=UTF-8');
			}
			$this->emitRaw();
		} elseif ($this->emitType == self::$EMIT_JSONP) {
			if (!$contentTypeSent) {
				header('content-type: application/javascript; charset=UTF-8');
			}
			echo $this->emitJSONPFn . '(';
			$this->emitJSON();
			echo ')';
		}
	}

	/**
	 * Emit response wrapper as JSONString.
	 */
	protected function emitJSON()
	{
		echo \App\Json::encode($this->prepareResponse());
	}

	/**
	 * Emit response wrapper as String/JSONString.
	 */
	protected function emitText()
	{
		if (null === $this->result) {
			if (\is_string($this->error)) {
				echo $this->error;
			} else {
				echo \App\Json::encode($this->prepareResponse());
			}
		} else {
			if (\is_string($this->result)) {
				echo $this->result;
			} else {
				echo \App\Json::encode($this->prepareResponse());
			}
		}
	}

	/**
	 * Emit response wrapper as String.
	 */
	protected function emitRaw()
	{
		if (null === $this->result) {
			echo (\is_string($this->error)) ? $this->error : var_export($this->error, true);
		}
		echo $this->result;
	}
}
