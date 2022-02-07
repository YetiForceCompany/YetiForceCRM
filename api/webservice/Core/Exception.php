<?php

/**
 * Web service exception file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Core;

/**
 * Web service exception class.
 */
class Exception extends \Exception
{
	/** {@inheritdoc}  */
	public function __construct($message, $code = 500, \Throwable $previous = null)
	{
		$message = rtrim(str_replace(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR, '', $message), PHP_EOL);
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
		if (!\App\Config::debug('apiShowExceptionMessages')) {
			$message = 'Internal Server Error';
		}
		$body = [
			'status' => 0,
			'error' => [
				'message' => $message,
				'code' => $code,
			],
		];
		if (\App\Config::debug('apiShowExceptionBacktrace')) {
			$body['error']['file'] = rtrim(str_replace(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR, '', $this->getFile()), PHP_EOL);
			$body['error']['line'] = $this->getLine();
			if (!empty($previous)) {
				$body['error']['previous'] = rtrim(str_replace(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR, '', $previous->__toString()), PHP_EOL);
			}
			$body['error']['backtrace'] = \App\Debuger::getBacktrace();
		}
		$response = Response::getInstance();
		$response->setRequest(Request::init());
		$response->setBody($body);
		$response->setStatus($code);
		if (\App\Config::debug('apiShowExceptionReasonPhrase')) {
			$response->setReasonPhrase($this->message);
		}
		$response->send();
	}

	/**
	 * Handle error function.
	 *
	 * @return void
	 */
	public function handleError(): void
	{
		if (\App\Config::debug('apiLogException')) {
			$request = Request::init();
			$error = "code: {$this->getCode()} | message: {$this->getMessage()}\n";
			$error .= "file: {$this->getFile()} ({$this->getLine()})\n";
			$error .= '============ stacktrace: ' . PHP_EOL . $this->getTraceAsString() . PHP_EOL;
			$error .= '============ Request ' . \App\RequestUtil::requestId() . ' ======  ' . date('Y-m-d H:i:s') . "  ======\n";
			$error .= 'REQUEST_METHOD: ' . \App\Request::getRequestMethod() . PHP_EOL;
			$error .= 'REQUEST_URI: ' . $_SERVER['REQUEST_URI'] . PHP_EOL;
			$error .= 'QUERY_STRING: ' . $_SERVER['QUERY_STRING'] . PHP_EOL;
			$error .= 'PATH_INFO: ' . ($_SERVER['PATH_INFO'] ?? '') . PHP_EOL;
			$error .= 'IP: ' . $_SERVER['REMOTE_ADDR'] . PHP_EOL;
			$error .= '----------- Headers -----------' . PHP_EOL;
			foreach ($request->getHeaders() as $key => $header) {
				$error .= $key . ': ' . $header . PHP_EOL;
			}
			$error .= '----------- Request data -----------' . PHP_EOL;
			$error .= print_r($request->getAllRaw(), true) . PHP_EOL;
			if ($_GET) {
				$error .= "----------- _GET -----------\n";
				$error .= print_r($_GET, true) . PHP_EOL;
			}
			if ($_POST) {
				$error .= "----------- _POST -----------\n";
				$error .= print_r($_POST, true) . PHP_EOL;
			}
			if ($payload = file_get_contents('php://input')) {
				$error .= "----------- Request payload -----------\n";
				$error .= print_r($payload, true) . PHP_EOL;
			}
			file_put_contents(ROOT_DIRECTORY . '/cache/logs/webserviceErrors.log', '============ Error exception ====== ' . date('Y-m-d H:i:s') . ' ======'
				. PHP_EOL . $error . PHP_EOL, FILE_APPEND);
		}
	}
}
