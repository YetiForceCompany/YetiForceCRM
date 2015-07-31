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
	protected $modulesPath = 'api/webservice/';
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

		if (isset($_REQUEST['head']['encrypted']) && $_REQUEST['head']['encrypted']) {
			$_REQUEST['data'] = $this->decryptData($_REQUEST['data']);
		}

		$this->request = new Vtiger_Request($_REQUEST, $_REQUEST);
		$this->data = new Vtiger_Request($_REQUEST['data'], $_REQUEST['data']);
	}

	private function response($data, $status = 200)
	{
		header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));
		echo json_encode($data);
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

	public function preProcess()
	{
		$head = $this->request->get('head');
		$apiKey = $head['apiKey'];
		if ($apiKey == '') {
			throw new APIException('Invalid api key');
		}
		$this->panel = 'Portal';
	}

	public function process()
	{
		$filePath = $this->modulesPath . $this->panel . '/modules/' . $this->request->get('module') . '/' . $this->request->get('action') . '.php';
		if (!file_exists($filePath)) {
			throw new APIException('No action found: ' . $filePath);
		}
		require_once $filePath;
		$handlerClass = 'API_' . $this->request->get('module') . '_' . $this->request->get('action');
		if (!class_exists($handlerClass)) {
			throw new APIException('HANDLER_NOT_FOUND: ' . $handlerClass);
		}

		$handler = new $handlerClass();
		if ($handler->getRequestMethod() != $this->method) {
			throw new APIException('Invalid request type');
		}

		if ($this->request->get('action') != '') {
			$function = $this->request->get('action');
		}
		
		$response = call_user_func_array([$handler, $function], $this->data->getAll());
		if (vglobal('encryptDataTransfer')) {
			$response = $this->encryptData($response);
		}
		$this->response([
			'status' => 1,
			'encrypted' => vglobal('encryptDataTransfer'),
			'result' => $response
		]);
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
}
