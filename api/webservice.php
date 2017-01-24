<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
chdir(__DIR__ . '/../');

require_once 'include/main/WebUI.php';

if (!in_array('webservice', $enabledServices)) {
	(new Exception\NoPermittedToApi())->stop([
		'status' => 0,
		'Encrypted' => 0,
		'error' => [
			'message' => 'Webservice - Service is not active'
		]
	]);
}
AppConfig::iniSet('error_log', ROOT_DIRECTORY . '/cache/logs/webservice.log');
define('REQUEST_MODE', 'API');

function exceptionErrorHandler($errno, $errstr, $errfile, $errline, $errcontext)
{
	switch ($errno) {
		case E_ERROR:
		case E_WARNING:
		case E_CORE_ERROR:
		case E_COMPILE_ERROR:
		case E_USER_ERROR:
			$msg = $errno . ': ' . $errstr . ' in ' . $errfile . ', line ' . $errline;
			throw new Api\Core\Exception($msg);
			break;
	}
}
set_error_handler('exceptionErrorHandler');

try {
	$api = new Api\Controller();
	$process = $api->preProcess();
	if ($process) {
		$api->process();
	}
	$api->postProcess();
} catch (\Api\Core\Exception $e) {
	$e->handleError();
}
