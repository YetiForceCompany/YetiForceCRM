<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
chdir(__DIR__ . '/../');

require_once 'include/main/WebUI.php';
require_once 'api/webservice/Core/BaseAction.php';
require_once 'api/webservice/Core/APISession.php';
require_once 'api/webservice/Core/APIAuth.php';
require_once 'api/webservice/API.php';
require_once 'api/webservice/APIException.php';
require_once 'api/webservice/APIResponse.php';


if (!in_array('webservice', $enabledServices)) {
	$apiLog = new \Exception\NoPermittedToApi();
	$apiLog->stop(['status' => 0, 'Encrypted' => 0, 'error' => ['message' => 'Webservice - Service is not active']]);
}
AppConfig::iniSet('error_log', ROOT_DIRECTORY . '/cache/logs/webservice.log');

define('REQUEST_MODE', 'API');

try {
	$api = new API();
	$process = $api->preProcess();
	if ($process) {
		$api->process();
	}
	$api->postProcess();
} catch (APIException $e) {
	$e->handleError();
}
