<?php
/**
 * Web service response file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Core;

/**
 * Web service response class.
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
	/**
	 * @var int Response status code.
	 */
	protected $status = 200;
	/**
	 * @var string Response data type.
	 */
	protected $responseType;
	/**
	 * @var string Reason phrase.
	 */
	protected $reasonPhrase;

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

	/**
	 * Add header.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return void
	 */
	public function addHeader(string $key, $value): void
	{
		$this->headers[$key] = $value;
	}

	/**
	 * Set status code.
	 *
	 * @param int $status
	 *
	 * @return void
	 */
	public function setStatus(int $status): void
	{
		$this->status = $status;
	}

	/**
	 * Set reason phrase.
	 *
	 * @param string $reasonPhrase
	 *
	 * @return void
	 */
	public function setReasonPhrase(string $reasonPhrase): void
	{
		$this->reasonPhrase = $reasonPhrase;
	}

	/**
	 * Set body data.
	 *
	 * @param array $body
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

	/**
	 * Set request.
	 *
	 * @param \Api\Core\Request $request
	 *
	 * @return void
	 */
	public function setRequest(Request $request): void
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

	/**
	 * Get reason phrase.
	 *
	 * @return string
	 */
	private function getReasonPhrase(): string
	{
		if (isset($this->reasonPhrase)) {
			return str_ireplace(["\r\n", "\r", "\n"], ' ', $this->reasonPhrase);
		}
		$statusCodes = [
			200 => 'OK',
			401 => 'Unauthorized',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			500 => 'Internal Server Error',
		];
		return $statusCodes[$this->status] ?? $statusCodes[500];
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
		$headersSent = headers_sent();
		if (!$headersSent) {
			header('Access-Control-Allow-Origin: *');
			header('Access-Control-Allow-Methods: ' . implode(', ', $this->acceptableMethods));
			header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, ' . implode(', ', $this->acceptableHeaders));
			header(\App\Request::_getServer('SERVER_PROTOCOL') . ' ' . $this->status . ' ' . $this->getReasonPhrase());
			header('Encrypted: ' . $encryptDataTransfer);
			foreach ($this->headers as $key => $header) {
				header(\strtolower($key) . ': ' . $header);
			}
		}
		if ($encryptDataTransfer) {
			header('Content-disposition: attachment; filename="api.json"');
			if (!empty($this->body)) {
				echo $this->encryptData($this->body);
			}
		} else {
			switch ($this->responseType) {
				case 'data':
					if (!empty($this->body)) {
						if (!$headersSent) {
							header("Content-type: $requestContentType");
						}
						if (false !== strpos($requestContentType, 'application/xml')) {
							if (!$headersSent) {
								header('Content-disposition: attachment; filename="api.xml"');
							}
							echo $this->encodeXml($this->body);
						} else {
							if (!$headersSent) {
								header('Content-disposition: attachment; filename="api.json"');
							}
							echo $this->encodeJson($this->body);
						}
					}
					break;
				case 'file':
					if (isset($this->file) && file_exists($this->file->getPath())) {
						header('Content-type: ' . $this->file->getMimeType());
						header('Content-transfer-encoding: binary');
						header('Content-length: ' . $this->file->getSize());
						header('Content-disposition: attachment; filename="' . $this->file->getName() . '"');
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

	/**
	 * Debug response function.
	 *
	 * @return void
	 */
	public function debugResponse()
	{
		if (\App\Config::debug('apiLogAllRequests')) {
			$log = '============ Request ' . \App\RequestUtil::requestId() . ' (Response) ======  ' . date('Y-m-d H:i:s') . "  ======\n";
			$log .= 'REQUEST_METHOD: ' . \App\Request::getRequestMethod() . PHP_EOL;
			$log .= 'REQUEST_URI: ' . $_SERVER['REQUEST_URI'] . PHP_EOL;
			$log .= 'QUERY_STRING: ' . $_SERVER['QUERY_STRING'] . PHP_EOL;
			$log .= 'PATH_INFO: ' . ($_SERVER['PATH_INFO'] ?? '') . PHP_EOL;
			$log .= 'IP: ' . $_SERVER['REMOTE_ADDR'] . PHP_EOL;
			if ($this->body) {
				$log .= "----------- Response data -----------\n";
				$log .= print_r($this->body, true) . PHP_EOL;
			}
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
