<?php
namespace Api\Core;

/**
 * Web service response class 
 * @package YetiForce.Webservice
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Response
{

	protected static $acceptableHeaders = ['X-API-KEY', 'X-ENCRYPTED', 'X-TOKEN'];
	static protected $instance = false;
	protected $body;
	protected $headers = [];
	protected $status = 200;

	public static function getInstance()
	{
		if (!static::$instance) {
			static::$instance = new self();
		}
		return static::$instance;
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
		$encryptDataTransfer = \AppConfig::api('ENCRYPT_DATA_TRANSFER') ? 1 : 0;
		if ($this->status !== 200) {
			$encryptDataTransfer = 0;
		}
		$requestContentType = strtolower($_SERVER['HTTP_ACCEPT']);
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: *');
		header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, ' . implode(',', static::$acceptableHeaders));
		header("Content-Type: $requestContentType");
		header('HTTP/1.1 ' . $this->status . ' ' . $this->_requestStatus());
		header('Encrypted: ' . $encryptDataTransfer);
		foreach ($this->headers as $key => $header) {
			header($key . ': ' . $header);
		}
		if (!empty($this->body)) {
			if ($encryptDataTransfer) {
				$response = $this->encryptData($this->body);
			} else {
				if (strpos($requestContentType, 'text/html') !== false) {
					$response = $this->encodeHtml($this->body);
				} else if (strpos($requestContentType, 'application/xml') !== false) {
					$response = $this->encodeXml($this->body);
				} else {
					$response = $this->encodeJson($this->body);
				}
			}
			echo $response;
		}
		$this->debugResponse();
	}

	public function encryptData($data)
	{
		openssl_public_encrypt($data, $encrypted, 'file://' . ROOT_DIRECTORY . DIRECTORY_SEPARATOR . vglobal('publicKey'));
		return $encrypted;
	}

	public function debugResponse()
	{
		if (\AppConfig::debug('WEBSERVICE_DEBUG')) {
			$log = "-------------  Response  -----  " . date('Y-m-d H:i:s') . "  ------\n";
			$log .= "Status: {$this->status}\n";
			$log .= 'Headers: ' . PHP_EOL;
			foreach ($this->headers as $key => $header) {
				$log .= "$key : $header\n";
			}
			$log .= "----------- Response data -----------\n";
			$log .= print_r($this->body, true) . PHP_EOL;
			file_put_contents('cache/logs/webserviceDebug.log', $log, FILE_APPEND);
		}
	}

	public function encodeHtml($responseData)
	{
		$htmlResponse = "<table border='1'>";
		foreach ($responseData as $key => $value) {
			$htmlResponse .= "<tr><td>$key</td><td>" . (is_array($value) ? $this->encodeHtml($value) : nl2br($value)) . "</td></tr>";
		}
		$htmlResponse .= "</table>";
		return $htmlResponse;
	}

	public function encodeJson($responseData)
	{
		return json_encode($responseData);
	}

	public function encodeXml($responseData)
	{
		$xml = new \SimpleXMLElement('<?xml version="1.0"?><data></data>');
		$this->toXml($responseData, $xml);
		return $xml->asXML();
	}

	function toXml($data, &$xmlData)
	{
		foreach ($data as $key => $value) {
			if (is_numeric($key)) {
				$key = 'item' . $key;
			}
			if (is_array($value)) {
				$subnode = $xmlData->addChild($key);
				$this->toXml($value, $subnode);
			} else {
				$xmlData->addChild("$key", htmlspecialchars("$value"));
			}
		}
	}
}
