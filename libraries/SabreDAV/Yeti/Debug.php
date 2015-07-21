<?php
namespace Yeti;
use
    Sabre\DAV,
    Sabre\HTTP\RequestInterface,
    Sabre\HTTP\ResponseInterface;

class Debug extends DAV\ServerPlugin {

	/**
	 * Reference to server object
	 *
	 * @var DAV\Server
	 */
	protected $server;

	const FILE = 'cache/logs/davDebug.log';

	function initialize(DAV\Server $server) {
		$this->server = $server;
		$server->on('beforeMethod', [$this, 'beforeMethod']);
		$server->on('afterMethod', [$this, 'afterMethod']);
	}

	function beforeMethod(RequestInterface $request, ResponseInterface $response) {
		$log = print_r($request, true);
		file_put_contents(self::FILE, ' --- '.date('Y-m-d H:i:s').' --- RequestInterface --- '.PHP_EOL.$log, FILE_APPEND);
		return true;
	}

	function afterMethod(RequestInterface $request, ResponseInterface $response) {
		$log = print_r($response, true);
		file_put_contents(self::FILE, ' --- '.date('Y-m-d H:i:s').' --- ResponseInterface --- '.PHP_EOL.$log, FILE_APPEND);
		return true;
	}
}
