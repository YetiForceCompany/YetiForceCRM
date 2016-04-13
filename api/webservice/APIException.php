<?php

class APIException extends Exception
{

	public function __construct($message, $code = 200, Exception $previous = null)
	{
		if (!empty($previous)) {
			parent::__construct($message, $code, $previous);
		}
		header("Access-Control-Allow-Orgin: *");
		header("Access-Control-Allow-Methods: *");
		header("Content-Type: application/json");
		if (!isset($data)) {
			$data = $this->message;
			$code = $this->code;
		}

		header("HTTP/1.1 " . $code . " " . $this->_requestStatus($code));
		header('Encrypted: 0');
		global $showWebserviceError;
		if (!$showWebserviceError && $code === 200) {
			$message = 'Internal Server Error';
			$code = 500;
		}
		echo json_encode(['status' => 0, 'error' => ['message' => $message, 'code' => $code]]);
	}

	private function _requestStatus($code)
	{
		$status = [
			200 => 'OK',
			401 => 'Unauthorized',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			500 => 'Internal Server Error',
		];
		return ($status[$code]) ? $status[$code] : $status[500];
	}
}

function exceptionErrorHandler($errno, $errstr, $errfile, $errline, $errcontext)
{
	switch ($errno) {
		case E_ERROR:
		case E_WARNING:
		case E_CORE_ERROR:
		case E_COMPILE_ERROR:
		case E_USER_ERROR:
			$msg = $errno . ': ' . $errstr . ' in ' . $errfile . ', line ' . $errline;
			throw new APIException($msg);
			break;
	}
}
set_error_handler('exceptionErrorHandler');
