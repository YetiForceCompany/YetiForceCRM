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
			$this->file = $previous->getFile();
			$this->line = $previous->getLine();
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
		if (\App\Config::debug('WEBSERVICE_SHOW_EXCEPTION_BACKTRACE')) {
			$body['error']['file'] = rtrim(str_replace(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR, '', $this->getFile()), PHP_EOL);
			$body['error']['line'] = $this->getLine();
			if (!empty($previous)) {
				$body['error']['previous'] = rtrim(str_replace(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR, '', $previous->__toString()), PHP_EOL);
			}
			$body['error']['backtrace'] = \App\Debuger::getBacktrace();
		}
		$response = Response::getInstance();
		$response->setBody($body);
		$response->setStatus($code);
		$response->send();
	}

	public function handleError()
	{
		if (\App\Config::debug('WEBSERVICE_LOG_ERRORS')) {
			$request = Request::init();
			$error = "code: {$this->getCode()} | message: {$this->getMessage()}\n";
			$error .= "file: {$this->getFile()} ({$this->getLine()})\n";
			$error .= '============ stacktrace: ' . PHP_EOL . $this->getTraceAsString() . PHP_EOL;
			$error .= '============ Request ======  ' . date('Y-m-d H:i:s') . "  ======\n";
			$error .= 'REQUEST_METHOD: ' . $request->getRequestMethod() . PHP_EOL;
			$error .= 'REQUEST_URI: ' . $_SERVER['REQUEST_URI'] . PHP_EOL;
			$error .= 'QUERY_STRING: ' . $_SERVER['QUERY_STRING'] . PHP_EOL;
			$error .= 'PATH_INFO: ' . $_SERVER['PATH_INFO'] . PHP_EOL;
			$error .= '----------- Headers -----------' . PHP_EOL;
			foreach ($request->getHeaders() as $key => $header) {
				$error .= $key . ': ' . $header . PHP_EOL;
			}
			$error .= '----------- Request data -----------' . PHP_EOL;
			$error .= print_r($request->getAllRaw(), true) . PHP_EOL;
			$error .= "----------- _GET -----------\n";
			$error .= print_r($_GET, true) . PHP_EOL;
			$error .= "----------- _POST -----------\n";
			$error .= print_r($_POST, true) . PHP_EOL;
			$error .= "----------- Request payload -----------\n";
			$error .= print_r(file_get_contents('php://input'), true) . PHP_EOL;
			file_put_contents('cache/logs/webserviceErrors.log', '============ Error exception ====== ' . date('Y-m-d H:i:s') . ' ======'
				. PHP_EOL . $error . PHP_EOL, FILE_APPEND);
		}
	}
}
