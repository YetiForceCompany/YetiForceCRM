<?php namespace Yeti;

use Sabre\DAV,
	Sabre\HTTP\RequestInterface,
	Sabre\HTTP\ResponseInterface;

class Debug extends DAV\ServerPlugin
{

	/**
	 * Reference to server object
	 *
	 * @var DAV\Server
	 */
	protected $server;

	const FILE = 'cache/logs/davDebug.log';

	function initialize(DAV\Server $server)
	{
		$this->server = $server;
		$server->on('beforeMethod', [$this, 'beforeMethod']);
		$server->on('exception', [$this, 'exception']);
		$server->on('afterResponse', [$this, 'afterResponse']);
	}

	function beforeMethod(RequestInterface $request, ResponseInterface $response)
	{
		file_put_contents(self::FILE, '============ ' . date('Y-m-d H:i:s') . ' ====== Request ======'
			. PHP_EOL . $request->__toString() . PHP_EOL, FILE_APPEND);
		return true;
	}

	function afterResponse(RequestInterface $request, ResponseInterface $response)
	{
		file_put_contents(self::FILE, '============ ' . date('Y-m-d H:i:s') . ' ====== Response ======'
			. PHP_EOL . $response->__toString() . PHP_EOL, FILE_APPEND);
		return true;
	}

	function exception($e)
	{
		$error = 'exception: ' . get_class($e) . PHP_EOL;
		$error .= 'message: ' . $e->getMessage() . PHP_EOL;
		$error .= 'file: ' . $e->getFile() . PHP_EOL;
		$error .= 'line: ' . $e->getLine() . PHP_EOL;
		$error .= 'code: ' . $e->getCode() . PHP_EOL;
		$error .= 'stacktrace: ' . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
		file_put_contents(self::FILE, '============ ' . date('Y-m-d H:i:s') . ' ====== Error exception ======'
			. PHP_EOL . $error . PHP_EOL, FILE_APPEND);
		return true;
	}
}
