<?php

/**
 * Web service response class 
 * @package YetiForce.Webservice
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class APIResponse
{

	static protected $instance = false;
	protected $acceptableHeaders = false;
	protected $body = false;
	protected $headers = [];
	protected $status = 200;

	public function getInstance($acceptableHeaders = '')
	{
		if (!self::$instance) {
			self::$instance = new self();
			self::$instance->acceptableHeaders = $acceptableHeaders;
		}
		return self::$instance;
	}

	public function addHeader($key, $value)
	{
		$this->headers[$key] = $value;
	}

	public function setStatus($status)
	{
		$this->status = $status;
	}

	public function setBody($body)
	{
		$this->body = $body;
	}

	private function _requestStatus()
	{
		$status = [
			200 => 'OK',
			401 => 'Unauthorized',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			500 => 'Internal Server Error',
		];
		return ($status[$this->status]) ? $status[$this->status] : $status[500];
	}

	public function send()
	{
		$encryptDataTransfer = AppConfig::api('ENCRYPT_DATA_TRANSFER') ? 1 : 0;
		if ($this->status != 200) {
			$encryptDataTransfer = 0;
		}

		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: *');
		header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, ' . implode(',', $this->acceptableHeaders));
		header('Content-Type: application/json');
		header('HTTP/1.1 ' . $this->status . ' ' . $this->_requestStatus());
		header('Encrypted: ' . $encryptDataTransfer);

		foreach ($this->headers as $key => $header) {
			header($key . ': ' . $header);
		}

		if (!empty($this->body)) {
			if ($encryptDataTransfer) {
				$response = $this->body;
			} else {
				$response = json_encode($this->body);
			}
			echo $response;
		}
		$this->debugResponse($encryptDataTransfer);
	}

	public function debugResponse($encryptDataTransfer)
	{
		if (AppConfig::debug('WEBSERVICE_DEBUG')) {
			$log .= 'Body: ' . PHP_EOL . print_r($this->body, true) . PHP_EOL;
			$log .= 'Headers: ' . PHP_EOL;
			foreach ($this->headers as $key => $header) {
				$log .= $key . ': ' . $header . PHP_EOL;
			}
			$log .= '============ input : ' . PHP_EOL . file_get_contents('php://input') . PHP_EOL;
			file_put_contents('cache/logs/webserviceDebug.log', '============ Response ====== ' . date('Y-m-d H:i:s') . ' ======'
				. PHP_EOL . $log . PHP_EOL, FILE_APPEND);
		}
	}
}
