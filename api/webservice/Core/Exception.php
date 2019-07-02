<?php

namespace Api\Core;

/**
 * Web service exception class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Exception extends \Exception
{
	/**
	 * {@inheritdoc}
	 */
	public function __construct($message, $code = 200, \Throwable $previous = null)
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
		if (!\App\Config::debug('WEBSERVICE_SHOW_ERROR') && 200 === $code) {
			$message = 'Internal Server Error';
			$code = 500;
		}
		$body = [
			'status' => 0,
			'error' => [
				'message' => $message,
				'code' => $code,
			],
		];
		if (\App\Config::debug('DISPLAY_EXCEPTION_BACKTRACE')) {
			$body['error']['backtrace'] = \App\Debuger::getBacktrace();
		}
		$response = Response::getInstance();
		$response->setBody($body);
		$response->setStatus($code);
		$response->send();
	}

	public function handleError()
	{
		if (\App\Config::debug('WEBSERVICE_DEBUG')) {
			$request = \App\Request::init();
			$error = "code: {$this->getCode()} | message: {$this->getMessage()}\n";
			$error .= "file: {$this->getFile()} ({$this->getLine()})\n";
			$error .= '============ stacktrace: ' . PHP_EOL . $this->getTraceAsString() . PHP_EOL;
			$error .= '============ Headers: ' . PHP_EOL;
			$error .= 'REQUEST_METHOD : ' . $request->getRequestMethod() . PHP_EOL;
			foreach ($request->getHeaders() as $key => $header) {
				$error .= $key . ': ' . $header . PHP_EOL;
			}
			$error .= '============ Request data : ' . PHP_EOL . file_get_contents('php://input') . PHP_EOL;
			file_put_contents('cache/logs/webserviceErrors.log', '============ Error exception ====== ' . date('Y-m-d H:i:s') . ' ======'
				. PHP_EOL . $error . PHP_EOL, FILE_APPEND);
		}
	}
}
