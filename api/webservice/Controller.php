<?php
/**
 * Base file to handle communication via web services.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api;

/**
 * Base class to handle communication via web services.
 */
class Controller
{
	/** @var \self */
	private static $instance;

	/** @var Core\BaseAction */
	private $actionHandler;

	/** @var \Api\Core\Request Request instance. */
	public $request;

	/** @var \Api\Core\Response Response instance. */
	public $response;

	/** @var string Request method. */
	public $method;

	/** @var array Headers. */
	public $headers;

	/** @var array Current server details (w_#__servers). */
	public $app;

	/**
	 * Construct.
	 */
	public function __construct()
	{
		$this->request = Core\Request::init();
		$this->response = Core\Response::getInstance();
		$this->response->setRequest($this->request);
		$this->method = \App\Request::getRequestMethod();
	}

	/**
	 * Get controller instance.
	 *
	 * @return \self
	 */
	public static function getInstance(): self
	{
		if (isset(self::$instance)) {
			return self::$instance;
		}
		return self::$instance = new self();
	}

	/**
	 * Pre process function.
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return bool
	 */
	public function preProcess(): bool
	{
		register_shutdown_function(function () {
			if ($error = error_get_last()) {
				$this->errorHandler($error['type'], $error['message'], $error['file'], $error['line']);
			}
		});
		set_error_handler([$this, 'errorHandler']);
		if ('OPTIONS' === $this->method) {
			$handlerClass = $this->getActionClassName();
			$handler = new $handlerClass();
			$this->response->setAcceptableHeaders($handler->allowedHeaders);
			$this->response->setAcceptableMethods($handler->allowedMethod);
			return false;
		}

		$this->headers = $this->request->getHeaders();
		Core\Auth::init($this);
		if (empty($this->app)) {
			throw new Core\Exception('Web service - Applications: Unauthorized', 401);
		}
		$this->app['tables'] = Core\Containers::$listTables[$this->app['type']] ?? [];
		if (!empty($this->app['ips']) && !\in_array(\App\RequestUtil::getRemoteIP(true), array_map('trim', explode(',', $this->app['ips'])))) {
			throw new Core\Exception('Illegal IP address', 401);
		}
		if ($this->request->isEmpty('action', true)) {
			throw new Core\Exception('No action', 404);
		}
		\App\Process::$processName = $this->request->getByType('action', \App\Purifier::ALNUM);
		\App\Process::$processType = $this->app['type'];
		return true;
	}

	/**
	 * Process function.
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return void
	 */
	public function process(): void
	{
		$handlerClass = $this->getActionClassName();
		$this->request->loadData();
		$this->debugRequest();
		$this->actionHandler = new $handlerClass();
		$this->actionHandler->controller = $this;
		$this->actionHandler->checkAction();
		$this->response->setAcceptableHeaders($this->actionHandler->allowedHeaders);
		$this->response->setAcceptableMethods($this->actionHandler->allowedMethod);
		$this->actionHandler->preProcess();
		$return = \call_user_func([$this->actionHandler, strtolower($this->method)]);
		if (null !== $return) {
			switch ($this->actionHandler->responseType) {
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
					throw new Core\Exception('Unsupported response type: ' . $this->actionHandler->responseType, 400);
					break;
			}
		}
	}

	/**
	 * Post process function.
	 *
	 * @return void
	 */
	public function postProcess(): void
	{
		$this->response->send();
	}

	/**
	 * Get action class name.
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return string
	 */
	private function getActionClassName(): string
	{
		$type = $this->request->getByType('_container', 'Standard');
		$this->request->delete('_container');
		$actionName = $this->request->getByType('action', 'Alnum');
		$module = $this->request->getModule('module');
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

	/**
	 * Debug request function.
	 *
	 * @return void
	 */
	public function debugRequest(): void
	{
		if (\App\Config::debug('apiLogAllRequests')) {
			$log = '============ Request ' . \App\RequestUtil::requestId() . ' (Controller) ======  ' . date('Y-m-d H:i:s') . "  ======\n";
			$log .= 'REQUEST_METHOD: ' . \App\Request::getRequestMethod() . PHP_EOL;
			$log .= 'REQUEST_URI: ' . $_SERVER['REQUEST_URI'] . PHP_EOL;
			$log .= 'QUERY_STRING: ' . $_SERVER['QUERY_STRING'] . PHP_EOL;
			$log .= 'PATH_INFO: ' . ($_SERVER['PATH_INFO'] ?? '') . PHP_EOL;
			$log .= 'IP: ' . $_SERVER['REMOTE_ADDR'] . PHP_EOL;
			$log .= '----------- Headers -----------' . PHP_EOL;
			foreach ($this->request->getHeaders() as $key => $header) {
				$log .= "$key : $header\n";
			}
			$log .= '----------- Request data -----------' . PHP_EOL;
			$log .= print_r($this->request->getAllRaw(), true) . PHP_EOL;
			if ($_GET) {
				$log .= "----------- _GET -----------\n";
				$log .= print_r($_GET, true) . PHP_EOL;
			}
			if ($_POST) {
				$log .= "----------- _POST -----------\n";
				$log .= print_r($_POST, true) . PHP_EOL;
			}
			if ($payload = file_get_contents('php://input')) {
				$log .= "----------- Request payload -----------\n";
				$log .= print_r($payload, true) . PHP_EOL;
			}
			file_put_contents(ROOT_DIRECTORY . '/cache/logs/webserviceDebug.log', $log, FILE_APPEND);
		}
	}

	/**
	 * Handle error function.
	 *
	 * @param \Throwable $e
	 *
	 * @return void
	 */
	public function handleError(\Throwable $e): void
	{
		if (isset($this->actionHandler)) {
			$this->actionHandler->updateSession();
			$this->actionHandler->updateUser([
				'custom_params' => [
					'last_error' => $e->getMessage(),
					'error_time' => date('Y-m-d H:i:s'),
					'error_method' => $this->request->getServer('REQUEST_URI'),
				],
			]);
		}
	}

	/**
	 * Exception error handler function..
	 *
	 * @see https://secure.php.net/manual/en/function.set-error-handler.php
	 *
	 * @param int    $no
	 * @param string $str
	 * @param string $file
	 * @param int    $line
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return void
	 */
	public static function errorHandler(int $no, string $str, string $file, int $line): void
	{
		if (\in_array($no, [E_ERROR, E_WARNING, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
			\App\Log::error($no . ': ' . $str . ' in ' . $file . ', line ' . $line);
		}
	}
}
