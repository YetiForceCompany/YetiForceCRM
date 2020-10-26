<?php

namespace Api\Core;

/**
 * Web service response class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Response
{
	/**
	 * Access control allow headers.
	 *
	 * @var string[]
	 */
	protected $acceptableHeaders = ['x-api-key', 'x-encrypted', 'x-token'];
	/**
	 * Access control allow methods.
	 *
	 * @var string[]
	 */
	protected $acceptableMethods = [];
	/**
	 * Request instance.
	 *
	 * @var \Api\Core\Request
	 */
	protected $request;
	protected static $instance = false;
	protected $body;
	/**
	 * File instance.
	 *
	 * @var \App\Fields\File
	 */
	protected $file;
	/**
	 * Headers.
	 *
	 * @var array
	 */
	protected $headers = [];
	protected $status = 200;
	/**
	 * Response data type.
	 *
	 * @var string
	 */
	protected $responseType;

	/**
	 * Get instance.
	 *
	 * @return self
	 */
	public static function getInstance(): self
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

	/**
	 * Set body data.
	 *
	 * @param array|\App\Fields\File $body
	 *
	 * @return void
	 */
	public function setBody(array $body): void
	{
		$this->body = $body;
		$this->responseType = 'data';
	}

	/**
	 * Set file instance.
	 *
	 * @param \App\Fields\File $file
	 *
	 * @return void
	 */
	public function setFile(\App\Fields\File $file): void
	{
		$this->file = $file;
		$this->responseType = 'file';
	}

	public function setRequest(Request $request)
	{
		$this->request = $request;
	}

	/**
	 * Set acceptable methods.
	 *
	 * @param string[] $methods
	 *
	 * @return void
	 */
	public function setAcceptableMethods(array $methods)
	{
		$this->acceptableMethods = array_merge($this->acceptableMethods, $methods);
	}

	/**
	 * Set acceptable headers.
	 *
	 * @param string[] $headers
	 *
	 * @return void
	 */
	public function setAcceptableHeaders(array $headers)
	{
		$this->acceptableHeaders = array_merge($this->acceptableHeaders, $headers);
	}

	private function requestStatus()
	{
		$statusCodes = [
			200 => 'OK',
			401 => 'Unauthorized',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			500 => 'Internal Server Error',
		];
		return ($statusCodes[$this->status]) ? $statusCodes[$this->status] : $statusCodes[500];
	}

	public function send()
	{
		$encryptDataTransfer = \App\Config::api('ENCRYPT_DATA_TRANSFER') ? 1 : 0;
		if (200 !== $this->status || 'data' !== $this->responseType) {
			$encryptDataTransfer = 0;
		}
		$requestContentType = strtolower(\App\Request::_getServer('HTTP_ACCEPT'));
		if (empty($requestContentType) || '*/*' === $requestContentType) {
			$requestContentType = $this->request->contentType;
		}
		if (!headers_sent()) {
			header('access-control-allow-origin: *');
			header('access-control-allow-methods: ' . implode(', ', $this->acceptableMethods));
			header('access-control-allow-headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, ' . implode(', ', $this->acceptableHeaders));
			header(\App\Request::_getServer('SERVER_PROTOCOL') . ' ' . $this->status . ' ' . $this->requestStatus());
			header('encrypted: ' . $encryptDataTransfer);
			foreach ($this->headers as $key => $header) {
				header(\strtolower($key) . ': ' . $header);
			}
		}
		if ($encryptDataTransfer) {
			header('content-disposition: attachment; filename="api.json"');
			if (!empty($this->body)) {
				echo $this->encryptData($this->body);
			}
		} else {
			switch ($this->responseType) {
				case 'data':
					if (!empty($this->body)) {
						header("content-type: $requestContentType");
						if (false !== strpos($requestContentType, 'application/xml')) {
							header('content-disposition: attachment; filename="api.xml"');
							echo $this->encodeXml($this->body);
						} else {
							header('content-disposition: attachment; filename="api.json"');
							echo $this->encodeJson($this->body);
						}
					}
					break;
				case 'file':
					if (isset($this->file) && file_exists($this->file->getPath())) {
						header('content-type: ' . $this->file->getMimeType());
						header('content-transfer-encoding: binary');
						header('content-length: ' . $this->file->getSize());
						header('content-disposition: attachment; filename="' . $this->file->getName() . '"');
						readfile($this->file->getPath());
					}
					break;
			}
		}
		$this->debugResponse();
	}

	public function encryptData($data)
	{
		openssl_public_encrypt($data, $encrypted, 'file://' . ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . \App\Config::api('PUBLIC_KEY'), OPENSSL_PKCS1_OAEP_PADDING);
		return $encrypted;
	}

	public function debugResponse()
	{
		if (\App\Config::debug('WEBSERVICE_DEBUG')) {
			$request = Request::init();
			$log = '-------------  Response  -----  ' . date('Y-m-d H:i:s') . "  ------\n";
			$log .= "Status: {$this->status}\n";
			$log .= 'REQUEST_METHOD: ' . $request->getRequestMethod() . PHP_EOL;
			$log .= 'REQUEST_URI: ' . $_SERVER['REQUEST_URI'] . PHP_EOL;
			$log .= 'QUERY_STRING: ' . $_SERVER['QUERY_STRING'] . PHP_EOL;
			$log .= 'PATH_INFO: ' . $_SERVER['PATH_INFO'] . PHP_EOL;
			if ($this->headers) {
				$log .= "----------- Response Headers -----------\n";
				foreach ($this->headers as $key => $header) {
					$log .= "$key : $header\n";
				}
			}
			$log .= "----------- Response data -----------\n";
			$log .= print_r($this->body, true) . PHP_EOL;
			file_put_contents('cache/logs/webserviceDebug.log', $log, FILE_APPEND);
		}
	}

	/**
	 * Encode json data output.
	 *
	 * @param array $responseData
	 *
	 * @return string
	 */
	public function encodeJson($responseData): string
	{
		return json_encode($responseData, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);
	}

	public function encodeXml($responseData)
	{
		$xml = new \SimpleXMLElement('<?xml version="1.0"?><data></data>');
		$this->toXml($responseData, $xml);

		return $xml->asXML();
	}

	public function toXml($data, \SimpleXMLElement &$xmlData)
	{
		foreach ($data as $key => $value) {
			if (is_numeric($key)) {
				$key = 'item' . $key;
			}
			if (\is_array($value)) {
				$subnode = $xmlData->addChild($key);
				$this->toXml($value, $subnode);
			} else {
				$xmlData->addChild("$key", htmlspecialchars("$value"));
			}
		}
	}
}
