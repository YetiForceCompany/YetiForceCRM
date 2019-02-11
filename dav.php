<?php
/**
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
require __DIR__ . '/include/ConfigUtils.php';
if (!in_array('dav', \App\Config::api('enabledServices', []))) {
	require __DIR__ . '/include/main/WebUI.php';
	$apiLog = new \App\Exceptions\NoPermittedToApi();
	$apiLog->stop('Dav - Service is not active');
	return;
}
// Database
$dbConfig = \App\Config::db('base');
$pdo = new PDO($dbConfig['dsn'] . ';charset=' . $dbConfig['charset'], $dbConfig['username'], $dbConfig['password']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$enableCalDAV = \App\Config::api('enableCalDAV');
$enableCardDAV = \App\Config::api('enableCardDAV');
$enableBrowser = \App\Config::api('enableBrowser');
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
	$carddavBackend = new App\Dav\CardDavBackendPdo($pdo);
	$nodes[] = new Sabre\CardDAV\AddressBookRoot($principalBackend, $carddavBackend);
}
// The object tree needs in turn to be passed to the server class
$server = new App\Dav\DavServer($nodes);
$server->setBaseUri($_SERVER['SCRIPT_NAME']);
$server->debugExceptions = \App\Config::debug('DAV_DEBUG_EXCEPTIONS');
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
if (\App\Config::debug('DAV_DEBUG_PLUGIN')) {
	$server->addPlugin(new App\Dav\Debug());
}
// And off we go!
$server->exec();
