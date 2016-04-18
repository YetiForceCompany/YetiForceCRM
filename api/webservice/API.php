<?php
require_once 'api/webservice/APIException.php';

class API
{

	/**
	 * Property: method
	 * The HTTP method this request was made in, either GET, POST, PUT or DELETE
	 */
	protected $method = '';
	protected $acceptableMethods = ['GET', 'POST', 'PUT', 'DELETE'];
	protected $acceptableHeaders = ['Apikey', 'Encrypted', 'Sessionid'];
	protected $modulesPath = 'api/webservice/';
	protected $data = [];
	protected $request = [];
	protected $headers = [];
	protected $panel = '';

	public function __construct()
	{
		header("Access-Control-Allow-Orgin: *");
		header("Access-Control-Allow-Methods: *");
		header("Content-Type: application/json");

		$this->method = $_SERVER['REQUEST_METHOD'];
		if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
			if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
				$this->method = 'DELETE';
			} else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
				$this->method = 'PUT';
			} else {
				throw new APIException('Unexpected Header');
			}
		}

		if (!in_array($this->method, $this->acceptableMethods)) {
			throw new APIException('Invalid Method', 405);
		}

		$this->request = new Vtiger_Request($_REQUEST, $_REQUEST);
		$this->initHeaders();

		if (isset($this->headers['Encrypted']) && $this->headers['Encrypted'] == 1) {
			$requestData = $this->decryptData(file_get_contents('php://input'));
		} else {
			$requestData = $_POST;
		}

		$this->data = new Vtiger_Request($requestData, $requestData);
	}

	public function preProcess()
	{
		if (!$this->validateApiKey($this->headers['Apikey'])) {
			throw new APIException('Invalid api key', 401);
		}
	}

	public function process()
	{
		$handlerClass = $this->getModuleClassName();
		$handler = new $handlerClass();
		$handler->api = $this;
		if ($handler->getRequestMethod() != $this->method) {
			throw new APIException('Invalid request type');
		}

		if ($this->request->get('action') != '') {
			$function = $this->request->get('action');
		}

		$data = [];
		if (is_a($this->data, 'Vtiger_Request')) {
			$data = $this->data->getAll();
		}
		if(count($data) == 0 && $this->request->has('record')){
			$data['record'] = $this->request->get('record');
		}

		if (is_array($data)) {
			$response = call_user_func_array([$handler, $function], $data);
		} else {
			$response = call_user_func([$handler, $function], $data);
		}

		$response = [
			'status' => 1,
			'result' => $response
		];
		if (vglobal('encryptDataTransfer')) {
			$response = $this->encryptData($response);
		}

		$this->response($response);
	}

	public function postProcess()
	{
		
	}

	public function encryptData($data)
	{
		$publicKey = 'file://' . vglobal('root_directory') . vglobal('publicKey');
		openssl_public_encrypt(json_encode($data), $encrypted, $publicKey);
		return $encrypted;
	}

	public function decryptData($data)
	{
		$privateKey = 'file://' . vglobal('root_directory') . vglobal('privateKey');
		if (!$privateKey = openssl_pkey_get_private($privateKey)) {
			throw new AppException('Private Key failed');
		}
		$privateKey = openssl_pkey_get_private($privateKey);
		openssl_private_decrypt($data, $decrypted, $privateKey);

		return json_decode($decrypted, 1);
	}

	public function initHeaders()
	{
		$headers = $this->request->getHeaders();
		foreach ($this->acceptableHeaders as $value) {
			if (!isset($headers[$value])) {
				throw new APIException('No parameter: ' . $value, 401);
			}
			$this->headers[$value] = $headers[$value];
		}
	}

	private function response($data, $status = 200)
	{
		header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));
		header('Encrypted: ' . (string) vglobal('encryptDataTransfer'));
		if (vglobal('encryptDataTransfer')) {
			$response = $data;
		} else {
			$response = json_encode($data);
		}
		echo $response;
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

	private function validateApiKey($key)
	{
		$this->panel = 'Portal';
		if ($key != 'n8erhg39rbn48nb438bn') {
			return false;
		}
		return true;
	}

	private function validateFromUrl($url)
	{
		if ($url != 'http://portal2') {
			return false;
		}
		return true;
	}

	public function getModuleName()
	{
		return $this->request->get('module');
	}

	private function getModuleClassName()
	{
		$mainFilePath = $filePath = $this->modulesPath . $this->panel . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $this->request->get('module') . DIRECTORY_SEPARATOR . $this->request->get('action') . '.php';
		if (file_exists($filePath)) {
			require_once $filePath;
			return 'API_' . $this->request->get('module') . '_' . $this->request->get('action');
		}

		$filePath = $this->modulesPath . $this->panel . '/modules/Base/' . $this->request->get('action') . '.php';
		if (file_exists($filePath)) {
			require_once $filePath;
			return 'API_Base_' . $this->request->get('action');
		}

		throw new APIException('No action found: ' . $mainFilePath, 405);
	}
}
