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

	const DEBUG_FILE = 'cache/logs/davDebug.log';
	const EXCEPTION_FILE = 'cache/logs/davException.log';

	public function initialize(DAV\Server $server)
	{
		$this->server = $server;
		$server->on('beforeMethod', [$this, 'beforeMethod']);
		$server->on('exception', [$this, 'exception']);
		$server->on('afterResponse', [$this, 'afterResponse']);
	}

	public function beforeMethod(RequestInterface $request, ResponseInterface $response)
	{
		file_put_contents(self::DEBUG_FILE, '============ ' . date('Y-m-d H:i:s') . ' ====== Request ======' . PHP_EOL, FILE_APPEND);
		if (in_array($request->getMethod(), ['PROPFIND', 'REPORT', 'PUT'])) {
			$content = $request->getMethod() . ' ' . $request->getUrl() . ' HTTP/' . $request->getHTTPVersion() . "\r\n";
			foreach ($request->getHeaders() as $key => $value) {
				foreach ($value as $v) {
					if ($key === 'Authorization') {
						list($v) = explode(' ', $v, 2);
						$v .= ' REDACTED';
					}
					$content .= $key . ": " . $v . "\r\n";
				}
			}
			$content .= PHP_EOL . file_get_contents('php://input');
		} else {
			$content = $request->__toString();
		}
		file_put_contents(self::DEBUG_FILE, $content . PHP_EOL, FILE_APPEND);
		return true;
	}

	public function afterResponse(RequestInterface $request, ResponseInterface $response)
	{
		$contentType = explode(';', $response->getHeader('Content-Type'));
		$content = reset($contentType);
		if (in_array($content, ['text/html', 'application/xml'])) {
			$content = $response->__toString();
		}
		file_put_contents(self::DEBUG_FILE, '============ ' . date('Y-m-d H:i:s') . ' ====== Response ======'
			. PHP_EOL . $content . PHP_EOL, FILE_APPEND);
		return true;
	}

	public function exception($e)
	{
		$error = 'exception: ' . get_class($e) . PHP_EOL;
		$error .= 'message: ' . $e->getMessage() . PHP_EOL;
		$error .= 'file: ' . $e->getFile() . PHP_EOL;
		$error .= 'line: ' . $e->getLine() . PHP_EOL;
		$error .= 'code: ' . $e->getCode() . PHP_EOL;
		$error .= 'stacktrace: ' . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
		file_put_contents(self::EXCEPTION_FILE, '============ ' . date('Y-m-d H:i:s') . ' ====== Error exception ======'
			. PHP_EOL . $error . PHP_EOL, FILE_APPEND);
		return true;
	}

	/**
	 * Returns a plugin name.
	 *
	 * Using this name other plugins will be able to access other plugins
	 * using \Sabre\DAV\Server::getPlugin
	 *
	 * @return string
	 */
	public function getPluginName()
	{
		return 'Yeti debug';
	}
	// @codeCoverageIgnoreEnd

	/**
	 * Returns a bunch of meta-data about the plugin.
	 *
	 * Providing this information is optional, and is mainly displayed by the
	 * Browser plugin.
	 *
	 * The description key in the returned array may contain html and will not
	 * be sanitized.
	 *
	 * @return array
	 */
	public function getPluginInfo()
	{
		return [
			'name' => $this->getPluginName(),
			'description' => 'Utility saving log requests, response and exception.',
			'link' => 'https://yetiforce.com/',
		];
	}
}
