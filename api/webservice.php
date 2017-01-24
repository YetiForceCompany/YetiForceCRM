<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
chdir(__DIR__ . '/../');

require_once 'include/main/WebUI.php';

AppConfig::iniSet('error_log', ROOT_DIRECTORY . '/cache/logs/webservice.log');
define('REQUEST_MODE', 'API');

try {
	if (!in_array('webservice', $enabledServices)) {
		throw new Exception\NoPermittedToApi('Webservice - Service is not active', 403);
	}
	$controller = Api\Controller::getInstance();
	$process = $controller->preProcess();
	if ($process) {
		$controller->process();
	}
	$controller->postProcess();
} catch (\Api\Core\Exception $e) {
	$e->handleError();
} catch (Exception\NoPermittedToApi $e) {
	echo json_encode([
		'status' => 0,
		'error' => [
			'message' => $e->getMessage()
		]
	]);
}
