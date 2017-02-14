<?php
namespace Api;

/**
 * Base class to handle communication via web services
 * @package YetiForce.Webservice
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Controller
{

	/**
	 * Property: method
	 * The HTTP method this request was made in, either GET, POST, PUT or DELETE
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
	 * Construct
	 */
	public function __construct()
	{
		$this->request = Core\Request::init();
		$this->response = Core\Response::getInstance();
		$this->method = strtoupper($this->request->getRequestMetod());
	}

	/**
	 * Get controller instance
	 * @return \self
	 */
	public static function getInstance()
	{
		if (isset(static::$instance)) {
			return static::$instance;
		}
		return static::$instance = new self();
	}

	public static function getAction()
	{
		return static::$action;
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
		if ($this->headers['X-API-KEY'] !== $this->app['api_key']) {
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
		static::$action = $handler = new $handlerClass();
		$handler->controller = $this;
		if ($handler->checkAction()) {
			$handler->preProcess();
			$return = call_user_func([$handler, strtolower($this->method)]);
		}
		if (!empty($return)) {
			$return = [
				'status' => 1,
				'result' => $return
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
		$action = $this->request->get('action');
		$module = $this->request->get('module');
		if ($module) {
			$className = "Api\\$type\\$module\\$action";
			if (class_exists($className)) {
				return $className;
			};
			$className = "Api\\$type\\BaseModule\\$action";
			if (class_exists($className)) {
				return $className;
			}
		}
		$className = "Api\\$type\\BaseAction\\$action";
		if (!$module && class_exists($className)) {
			return $className;
		}
		throw new Core\Exception("No action found", 405);
	}

	public function debugRequest()
	{
		if (\AppConfig::debug('WEBSERVICE_DEBUG')) {
			$log = '============ Request ======  ' . date('Y-m-d H:i:s') . "  ======\n";
			$log .= 'REQUEST_METHOD: ' . $this->request->getRequestMetod() . PHP_EOL;
			$log .= "Headers: \n";
			foreach ($this->request->getHeaders() as $key => $header) {
				$log .= "$key : $header\n";
			}
			$log .= "----------- Request data -----------\n";
			$log .= print_r($this->request->getAllRaw(), true) . PHP_EOL;
			file_put_contents('cache/logs/webserviceDebug.log', $log, FILE_APPEND);
		}
	}

	public function exceptionErrorHandler($errno, $errstr, $errfile, $errline, $errcontext)
	{
		switch ($errno) {
			case E_ERROR:
			case E_WARNING:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_USER_ERROR:
				$msg = $errno . ': ' . $errstr . ' in ' . $errfile . ', line ' . $errline;
				throw new Core\Exception($msg);
		}
	}
}
