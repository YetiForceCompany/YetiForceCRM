<?php
/**
 * @package YetiForce.Webservice
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
require_once 'include/main/WebUI.php';
AppConfig::iniSet('error_log', ROOT_DIRECTORY . '/cache/logs/webservice.log');
\App\Config::$requestMode = 'API';
try {
	if (!in_array('webservice', $enabledServices)) {
		throw new \App\Exceptions\NoPermittedToApi('Webservice - Service is not active', 403);
	}
	$controller = Api\Controller::getInstance();
	$process = $controller->preProcess();
	if ($process) {
		$controller->process();
	}
	$controller->postProcess();
} catch (\Api\Core\Exception $e) {
	$e->handleError();
} catch (\App\Exceptions\NoPermittedToApi $e) {
	echo json_encode([
		'status' => 0,
		'error' => [
			'message' => $e->getMessage()
		]
	]);
}
