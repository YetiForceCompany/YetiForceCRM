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
	/** @var \self */
	private static $instance;
	/** @var Core\BaseAction */
	private static $action;
	/**
	 * Request instance.
	 *
	 * @var \Api\Core\Request
	 * */
	public $request;
	/**
	 * Response instance.
	 *
	 * @var \Api\Core\Response
	 */
	public $response;
	/**
	 * Request method.
	 *
	 * @var string
	 */
	public $method;
	/**
	 * Headers.
	 *
	 * @var array
	 */
	public $headers;
	public $app;

	/**
	 * Construct.
	 */
	public function __construct()
	{
		$this->request = Core\Request::init();
		$this->response = Core\Response::getInstance();
		$this->response->setRequest($this->request);
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
		$this->app = Core\Auth::init($this);
		$this->headers = $this->request->getHeaders();
		if ('OPTIONS' === $this->method) {
			$handlerClass = $this->getModuleClassName();
			$handler = new $handlerClass();
			$this->response->setAcceptableHeaders($handler->allowedHeaders);
			$this->response->setAcceptableMethods($handler->allowedMethod);
			return false;
		}
		if (!empty($this->app['acceptable_url'])) {
			if (!\in_array(\App\RequestUtil::getRemoteIP(true), array_map('trim', explode(',', $this->app['acceptable_url'])))) {
				throw new Core\Exception('Illegal IP address', 401);
			}
		}
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
			$this->response->setAcceptableHeaders($handler->allowedHeaders);
			$this->response->setAcceptableMethods($handler->allowedMethod);
			$handler->preProcess();
			$return = \call_user_func([$handler, strtolower($this->method)]);
		}
		if (null !== $return) {
			switch ($handler->responseType) {
				case 'data':
					$this->response->setBody([
						'status' => 1,
						'result' => $return,
					]);
					break;
				case 'file':
					$this->response->setFile($return);
					break;
				default:
					throw new Core\Exception('Unsupported response type: ' . $handler->responseType, 400);
					break;
			}
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
		if (\App\Config::debug('apiLogAllRequests')) {
			$log = '============ Request ======  ' . date('Y-m-d H:i:s') . "  ======\n";
			$log .= 'REQUEST_METHOD: ' . $this->request->getRequestMethod() . PHP_EOL;
			$log .= 'REQUEST_URI: ' . $_SERVER['REQUEST_URI'] . PHP_EOL;
			$log .= 'QUERY_STRING: ' . $_SERVER['QUERY_STRING'] . PHP_EOL;
			$log .= 'PATH_INFO: ' . $_SERVER['PATH_INFO'] . PHP_EOL;
			$log .= '----------- Headers -----------' . PHP_EOL;
			foreach ($this->request->getHeaders() as $key => $header) {
				$log .= "$key : $header\n";
			}
			$log .= '----------- Request data -----------' . PHP_EOL;
			$log .= print_r($this->request->getAllRaw(), true) . PHP_EOL;
			$log .= "----------- _GET -----------\n";
			$log .= print_r($_GET, true) . PHP_EOL;
			$log .= "----------- _POST -----------\n";
			$log .= print_r($_POST, true) . PHP_EOL;
			$log .= "----------- Request payload -----------\n";
			$log .= print_r(file_get_contents('php://input'), true) . PHP_EOL;
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
