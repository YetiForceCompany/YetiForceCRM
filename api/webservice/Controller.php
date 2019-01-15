<?php

namespace Api;

/**
 * Base class to handle communication via web services.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Controller
{
	/**
	 * Property: method
	 * The HTTP method this request was made in, either GET, POST, PUT or DELETE.
	 */
	protected static $acceptableMethods = ['GET', 'POST', 'PUT', 'DELETE'];

	/** @var \self */
	private static $instance;

	/** @var Core\BaseAction */
	private static $action;

	/** @var \Api\Core\Request */
	public $request;
	public $response;
	public $method;
	public $headers;
	public $app;

	/**
	 * Construct.
	 */
	public function __construct()
	{
		$this->request = Core\Request::init();
		$this->response = Core\Response::getInstance();
		$this->method = strtoupper($this->request->getRequestMethod());
	}

	/**
	 * Get controller instance.
	 *
	 * @return \self
	 */
	public static function getInstance()
	{
		if (isset(self::$instance)) {
			return self::$instance;
		}
		return self::$instance = new self();
	}

	public static function getAction()
	{
		return self::$action;
	}

	public function preProcess()
	{
		set_error_handler([$this, 'exceptionErrorHandler']);
		if ($this->method === 'OPTIONS') {
			$this->response->addHeader('Allow', strtoupper(implode(', ', static::$acceptableMethods)));

			return false;
		}
		$this->app = Core\Auth::init($this);
		$this->headers = $this->request->getHeaders();
		if ($this->headers['x-api-key'] !== \App\Encryption::getInstance()->decrypt($this->app['api_key'])) {
			throw new Core\Exception('Invalid api key', 401);
		}
		if (empty($this->request->get('action'))) {
			throw new Core\Exception('No action', 404);
		}
		return true;
	}

	public function process()
	{
		$handlerClass = $this->getModuleClassName();
		$this->request->getData();
		$this->debugRequest();
		self::$action = $handler = new $handlerClass();
		$handler->controller = $this;
		if ($handler->checkAction()) {
			$handler->preProcess();
			$return = call_user_func([$handler, strtolower($this->method)]);
		}
		if (!empty($return)) {
			$return = [
				'status' => 1,
				'result' => $return,
			];
			$this->response->setBody($return);
		}
	}

	public function postProcess()
	{
		$this->response->send();
	}

	private function getModuleClassName()
	{
		$type = $this->app['type'];
		$actionName = $this->request->get('action');
		$module = $this->request->get('module');
		if ($module) {
			$className = "Api\\$type\\$module\\$actionName";
			if (class_exists($className)) {
				return $className;
			}
			$className = "Api\\$type\\BaseModule\\$actionName";
			if (class_exists($className)) {
				return $className;
			}
		}
		$className = "Api\\$type\\BaseAction\\$actionName";
		if (!$module && class_exists($className)) {
			return $className;
		}
		throw new Core\Exception('No action found', 405);
	}

	public function debugRequest()
	{
		if (\AppConfig::debug('WEBSERVICE_DEBUG')) {
			$log = '============ Request ======  ' . date('Y-m-d H:i:s') . "  ======\n";
			$log .= 'REQUEST_METHOD: ' . $this->request->getRequestMethod() . PHP_EOL;
			$log .= "Headers: \n";
			foreach ($this->request->getHeaders() as $key => $header) {
				$log .= "$key : $header\n";
			}
			$log .= "----------- Request data -----------\n";
			$log .= print_r($this->request->getAllRaw(), true) . PHP_EOL;
			file_put_contents('cache/logs/webserviceDebug.log', $log, FILE_APPEND);
		}
	}

	public function exceptionErrorHandler($errno, $errstr, $errfile, $errline)
	{
		if (\in_array($errno, [E_ERROR, E_WARNING, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
			throw new Core\Exception($errno . ': ' . $errstr . ' in ' . $errfile . ', line ' . $errline);
		}
	}
}
