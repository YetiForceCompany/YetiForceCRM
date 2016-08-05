<?php

/**
 * Base class to handle communication via web services
 * @package YetiForce.Webservice
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
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
	public $request = [];
	public $response = [];
	public $headers = [];
	public $app = [];

	public function __construct()
	{
		$this->request = AppRequest::init();
		$this->response = APIResponse::getInstance($this->acceptableHeaders);
		$this->db = PearDatabase::getInstance();
		$this->method = $this->request->getRequestMetod();
		$this->debugRequest();
	}

	public function preProcess()
	{
		if (strtolower($this->method) == 'options') {
			$this->response->addHeader('Allow', strtoupper(implode(', ', $this->acceptableMethods)));
			return false;
		}

		$this->app = APIAuth::init($this);

		$this->headers = $this->request->getHeaders();
		if (isset($this->headers['Encrypted']) && $this->headers['Encrypted'] == 1) {
			$requestData = $this->decryptData(file_get_contents('php://input'));
		} else {
			$requestData = json_decode(file_get_contents('php://input'), 1);
		}

		$this->data = new Vtiger_Request($requestData, $requestData);
		if ($this->headers['Apikey'] != $this->app['api_key']) {
			throw new APIException('Invalid api key', 401);
		}

		if (empty($this->request->get('module'))) {
			throw new APIException('No action', 404);
		}
		return true;
	}

	public function process()
	{
		$handlerClass = $this->getModuleClassName();
		$handler = new $handlerClass();
		$function = strtolower($this->method);

		if (!method_exists($handler, $function)) {
			throw new APIException('Invalid Method', 405);
		}
		$handler->api = $this;

		$data = [];
		if (is_a($this->data, 'Vtiger_Request')) {
			$data = $this->data->getAll();
		}
		if (count($data) == 0 && $this->request->has('record')) {
			$data['record'] = $this->request->get('record');
		}

		if (!($this->request->get('action') == 'Login' && $this->request->get('module') == 'Users')) {
			$session = APISession::checkSession($this->headers['Sessionid']);

			if ($session == false) {
				throw new APIException('Invalid Sessionid', 401);
			}
			if (!$handler->checkPermission($this->request->get('action'), $session->get('user_id'))) {
				throw new APIException('No permission to action', 405);
			}
		}

		if (is_array($data)) {
			$return = call_user_func_array([$handler, $function], $data);
		} else {
			$return = call_user_func([$handler, $function], $data);
		}

		if (!empty($return)) {
			$return = [
				'status' => 1,
				'result' => $return
			];
			if (AppConfig::api('ENCRYPT_DATA_TRANSFER')) {
				$return = $this->encryptData($return);
			}
			$this->response->setBody($return);
		}
	}

	public function postProcess()
	{
		$this->response->send();
	}

	public function encryptData($data)
	{
		$publicKey = 'file://' . ROOT_DIRECTORY . DIRECTORY_SEPARATOR . vglobal('publicKey');
		openssl_public_encrypt(json_encode($data), $encrypted, $publicKey);
		return $encrypted;
	}

	public function decryptData($data)
	{
		$privateKey = 'file://' . ROOT_DIRECTORY . DIRECTORY_SEPARATOR . vglobal('privateKey');
		if (!$privateKey = openssl_pkey_get_private($privateKey)) {
			throw new \Exception\AppException('Private Key failed');
		}
		$privateKey = openssl_pkey_get_private($privateKey);
		openssl_private_decrypt($data, $decrypted, $privateKey);

		return json_decode($decrypted, 1);
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
		$type = $this->app['type'];
		$filePath = $this->modulesPath . $type . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $this->request->get('module') . DIRECTORY_SEPARATOR . $this->request->get('action') . '.php';
		if (file_exists($filePath)) {
			require_once $filePath;
			return 'API_' . $this->request->get('module') . '_' . $this->request->get('action');
		}

		$filePath = $this->modulesPath . $type . '/modules/Base/' . $this->request->get('action') . '.php';
		if (file_exists($filePath)) {
			require_once $filePath;
			return 'API_Base_' . $this->request->get('action');
		}

		$mainFilePath = $this->modulesPath . $type . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $this->request->get('module') . '.php';
		if (file_exists($mainFilePath)) {
			require_once $mainFilePath;
			return 'API_' . $this->request->get('module');
		}
		throw new APIException('No action found: ' . $mainFilePath, 405);
	}

	public function debugRequest()
	{
		if (AppConfig::debug('WEBSERVICE_DEBUG')) {
			$log .= 'REQUEST_METHOD: ' . $this->request->getRequestMetod() . PHP_EOL;
			$log .= 'Headers: ' . PHP_EOL;
			foreach ($this->request->getHeaders() as $key => $header) {
				$log .= $key . ': ' . $header . PHP_EOL;
			}
			$log .= '============ Request data : ' . PHP_EOL . file_get_contents('php://input') . PHP_EOL;
			file_put_contents('cache/logs/webserviceDebug.log', '============ Request ====== ' . date('Y-m-d H:i:s') . ' ======'
				. PHP_EOL . $log . PHP_EOL, FILE_APPEND);
		}
	}
}
