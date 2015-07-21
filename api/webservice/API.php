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
	protected $modulesPath = 'api/webservice/modules/';


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

	public function process()
	{
		$filePath = $this->modulesPath . $this->request->get('module') . '.php';
		if (!file_exists($filePath)) {
			throw new APIException('File does not exist: ' . $filePath);
		}
		require_once $filePath;
		$handlerClass = 'API_' . $this->request->get('module');
		if (!class_exists($handlerClass)) {
			throw new APIException('HANDLER_NOT_FOUND: ' . $handlerClass);
		}

		$handler = new $handlerClass();
		$function = 'process';
		if ($this->request->get('action') != '') {
			$function = $this->request->get('action');
		}

		$response = call_user_func_array([$handler, $function], $this->data->getAll());
		$this->response($response);
	}
}
