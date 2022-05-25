<?php
/**
 * SabreDav debug plugin file.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Dav;

use Sabre\DAV;
use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\ResponseInterface;

/**
 * SabreDav debug plugin class.
 */
class Debug extends DAV\ServerPlugin
{
	/**
	 * Reference to server object.
	 *
	 * @var DAV\Server
	 */
	protected $server;
	/**
	 * Response body.
	 *
	 * @var string
	 */
	protected $response;
	/**
	 * Request body.
	 *
	 * @var string
	 */
	protected $request;
	/**
	 * Debug file path.
	 *
	 * @var string
	 */
	const DEBUG_FILE = 'cache/logs/davRequests.log';
	/**
	 * Exception file path.
	 *
	 * @var string
	 */
	const EXCEPTION_FILE = 'cache/logs/davExceptions.log';

	/**
	 * Initializes selected functions.
	 *
	 * @param \Sabre\DAV\Server $server
	 */
	public function initialize(DAV\Server $server)
	{
		$this->server = $server;
		$this->server->on('beforeMethod:*', [$this, 'beforeMethod'], 50);
		$this->server->on('exception', [$this, 'exception']);
		$this->server->on('afterResponse', [$this, 'afterResponse']);
		$this->server->setLogger((new Logger()));
	}

	/**
	 * Force user authentication.
	 *
	 * @param RequestInterface  $request
	 * @param ResponseInterface $response
	 *
	 * @return bool
	 */
	public function beforeMethod(RequestInterface $request, ResponseInterface $response)
	{
		file_put_contents(self::DEBUG_FILE, '============ ' . date('Y-m-d H:i:s') . ' ====== Request ======' . PHP_EOL, FILE_APPEND);
		if (\in_array($request->getMethod(), ['PROPFIND', 'REPORT', 'PUT'])) {
			$content = $request->getMethod() . ' ' . $request->getUrl() . ' HTTP/' . $request->getHTTPVersion() . "\r\n";
			foreach ($request->getHeaders() as $key => $value) {
				foreach ($value as $v) {
					$content .= $key . ': ' . $v . "\r\n";
				}
			}
			$content .= PHP_EOL . file_get_contents('php://input');
		} else {
			$content = $request->__toString();
		}
		$this->request = $content;
		file_put_contents(self::DEBUG_FILE, $content . PHP_EOL, FILE_APPEND);
		return true;
	}

	/**
	 * Places a list of headers.
	 *
	 * @param RequestInterface  $request
	 * @param ResponseInterface $response
	 *
	 * @return bool
	 */
	public function afterResponse(RequestInterface $request, ResponseInterface $response)
	{
		$contentType = explode(';', $response->getHeader('Content-Type'));
		$content = reset($contentType);
		if (\in_array($content, ['text/html', 'application/xml'])) {
			$content = $response->__toString();
		}
		$this->response = $content;
		file_put_contents(self::DEBUG_FILE, '============ ' . date('Y-m-d H:i:s') . ' ====== Response ======'
			. PHP_EOL . $content . PHP_EOL, FILE_APPEND);
		return true;
	}

	/**
	 * This function will cause the "exception" event
	 * to occur as soon as the error document is returned.
	 *
	 * @param \Exception $e
	 *
	 * @return bool
	 */
	public function exception(\Exception $e)
	{
		$error = 'exception: ' . \get_class($e) . ' [code: ' . $e->getCode() . ']' . PHP_EOL;
		$error .= 'message: ' . $e->getMessage() . PHP_EOL;
		$error .= 'file: ' . rtrim(str_replace(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR, '', $e->getFile()), PHP_EOL) . ':' . $e->getLine() . PHP_EOL;
		$error .= rtrim(str_replace(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR, '', $e->getTraceAsString()), PHP_EOL) . PHP_EOL . PHP_EOL;
		$error .= 'request: ' . PHP_EOL . ($this->request ?? $this->server->httpRequest) . PHP_EOL;
		$error .= 'response: ' . PHP_EOL . ($this->response ?? $this->server->httpResponse);
		file_put_contents(self::EXCEPTION_FILE, '============ ' . date('Y-m-d H:i:s') . PHP_EOL . $error . PHP_EOL, FILE_APPEND);
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

	/** @codeCoverageIgnoreEnd */

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

	/**
	 * Mapping PHP errors to exceptions.
	 *
	 * While this is not strictly needed, it makes a lot of sense to do so. If an
	 * E_NOTICE or anything appears in your code, this allows SabreDAV to intercept
	 * the issue and send a proper response back to the client (HTTP/1.1 500).
	 *
	 * @param int    $no
	 * @param string $str
	 * @param string $file
	 * @param int    $line
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public static function exceptionErrorHandler(int $no, string $str, string $file, int $line): void
	{
		throw new \App\Exceptions\AppException($str, 0, new \ErrorException($str, 0, $no, $file, $line));
	}
}
