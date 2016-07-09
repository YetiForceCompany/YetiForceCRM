<?php

/**
 * Web service exception class 
 * @package YetiForce.Webservice
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class APIException extends Exception
{

	public function __construct($message, $code = 200, Exception $previous = null)
	{
		if (!empty($previous)) {
			parent::__construct($message, $code, $previous);
		}
		if (empty($this->message)) {
			$this->message = $message;
		}
		if (empty($this->code)) {
			$this->code = $code;
		}

		if (!AppConfig::debug('WEBSERVICE_SHOW_ERROR') && $code === 200) {
			$message = 'Internal Server Error';
			$code = 500;
		}

		$body = [
			'status' => 0,
			'error' => [
				'message' => $message,
				'code' => $code
			]
		];
		if (AppConfig::debug('DISPLAY_DEBUG_BACKTRACE')) {
			$body['error']['backtrace'] = vtlib\Functions::getBacktrace();
		}

		$response = APIResponse::getInstance();
		$response->setBody($body);
		$response->setStatus($code);
		$response->send();
	}

	public function handleError()
	{
		if (AppConfig::debug('WEBSERVICE_DEBUG')) {
			$request = AppRequest::init();

			$error .= 'message: ' . $this->getMessage() . PHP_EOL;
			$error .= 'file: ' . $this->getFile() . PHP_EOL;
			$error .= 'line: ' . $this->getLine() . PHP_EOL;
			$error .= 'code: ' . $this->getCode() . PHP_EOL;
			$error .= '============ stacktrace: ' . PHP_EOL . $this->getTraceAsString() . PHP_EOL;
			$error .= '============ Headers: ' . PHP_EOL;
			$error .= 'REQUEST_METHOD : ' . $request->getRequestMetod() . PHP_EOL;
			foreach ($request->getHeaders() as $key => $header) {
				$error .= $key . ': ' . $header . PHP_EOL;
			}
			$error .= '============ Request data : ' . PHP_EOL . file_get_contents('php://input') . PHP_EOL;
			file_put_contents('cache/logs/webserviceErrors.log', '============ Error exception ====== ' . date('Y-m-d H:i:s') . ' ======'
				. PHP_EOL . $error . PHP_EOL, FILE_APPEND);
		}
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
