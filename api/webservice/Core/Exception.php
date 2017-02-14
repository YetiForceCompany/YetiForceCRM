<?php
namespace Api\Core;

/**
 * Web service exception class 
 * @package YetiForce.Webservice
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Exception extends \Exception
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
		if (!\AppConfig::debug('WEBSERVICE_SHOW_ERROR') && $code === 200) {
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
		if (\AppConfig::debug('DISPLAY_DEBUG_BACKTRACE')) {
			$body['error']['backtrace'] = \App\Debuger::getBacktrace();
		}
		$response = Response::getInstance();
		$response->setBody($body);
		$response->setStatus($code);
		$response->send();
	}

	public function handleError()
	{
		if (\AppConfig::debug('WEBSERVICE_DEBUG')) {
			$request = \AppRequest::init();
			$error .= "code: {$this->getCode()} | message: {$this->getMessage()}\n";
			$error .= "file: {$this->getFile()} ({$this->getLine()})\n";
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
