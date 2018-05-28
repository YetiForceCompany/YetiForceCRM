<?php
/**
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
require __DIR__ . '/include/ConfigUtils.php';
if (!in_array('dav', $enabledServices)) {
	require __DIR__ . '/include/main/WebUI.php';
	$apiLog = new \App\Exceptions\NoPermittedToApi();
	$apiLog->stop('Dav - Service is not active');
}
// Database
$pdo = new PDO('mysql:host=' . $dbconfig['db_server'] . ';dbname=' . $dbconfig['db_name'] . ';charset=utf8', $dbconfig['db_username'], $dbconfig['db_password']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

set_error_handler(['App\Dav\Debug', 'exceptionErrorHandler']);
$enableWebDAV = false;
// Backends
$authBackend = new App\Dav\DavAuthBackendPdo($pdo);
$principalBackend = new App\Dav\DavaclPrincipalBackendPdo($pdo);
$nodes = [
	new Sabre\DAVACL\PrincipalCollection($principalBackend),
];
if ($enableCalDAV) {
	$calendarBackend = new App\Dav\CalDavBackendPdo($pdo);
	$nodes[] = new Sabre\CalDAV\Principal\Collection($principalBackend);
	$nodes[] = new Sabre\CalDAV\CalendarRoot($principalBackend, $calendarBackend);
}
if ($enableCardDAV) {
	$carddavBackend = new App\Dav\CardDavBackendPdoCardDAV_Backend_PDO($pdo);
	$nodes[] = new Sabre\CardDAV\AddressBookRoot($principalBackend, $carddavBackend);
}
if ($enableWebDAV) {
	$exData = new stdClass();
	$exData->pdo = $pdo;
	$exData->storageDir = $davStorageDir;
	$exData->historyDir = $davHistoryDir;
	$exData->localStorageDir = ROOT_DIRECTORY . $exData->storageDir;
	$exData->localHistoryDir = ROOT_DIRECTORY . $exData->historyDir;
	$directory = new App\Dav\WebDavDirectory('files', $exData);
	$directory->getRootChild();
	$nodes[] = $directory;
}
// The object tree needs in turn to be passed to the server class
$server = new App\Dav\DavServer($nodes);
$server->setBaseUri($_SERVER['SCRIPT_NAME']);
$server->debugExceptions = AppConfig::debug('DAV_DEBUG_EXCEPTIONS');
// Plugins
$server->addPlugin(new Sabre\DAV\Auth\Plugin($authBackend));
$aclPlugin = new Sabre\DAVACL\Plugin();
$aclPlugin->adminPrincipals = [];
$server->addPlugin($aclPlugin);
if ($enableBrowser) {
	$server->addPlugin(new Sabre\DAV\Browser\Plugin());
}
if ($enableCardDAV) {
	//CardDav integration
	$server->addPlugin(new Sabre\CardDAV\Plugin());
}
if ($enableCalDAV) {
	//CalDAV integration
	$server->addPlugin(new Sabre\CalDAV\Plugin());
	$server->addPlugin(new Sabre\CalDAV\Subscriptions\Plugin());
	$server->addPlugin(new Sabre\CalDAV\Schedule\Plugin());
}
if ($enableWebDAV) {
	//WebDAV integration
	$server->addPlugin(new Sabre\DAV\Sync\Plugin());
}
if (AppConfig::debug('DAV_DEBUG_PLUGIN')) {
	$server->addPlugin(new App\Dav\Debug());
}
// And off we go!
$server->exec();
