<?php
/**
 * SabreDav init file.
 *
 * @package   Integrations
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
require __DIR__ . '/include/ConfigUtils.php';

if (!\in_array('dav', \App\Config::api('enabledServices', []))) {
	require __DIR__ . '/include/main/WebUI.php';
	$apiLog = new \App\Exceptions\NoPermittedToApi();
	$apiLog->stop('Dav - Service is not active');
	return;
}

// DataBase
$dbConfig = \App\Config::db('base');
$pdo = new PDO($dbConfig['dsn'] . ';charset=' . $dbConfig['charset'], $dbConfig['username'], $dbConfig['password']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$enableCalDAV = \App\Config::api('enableCalDAV');
$enableCardDAV = \App\Config::api('enableCardDAV');
set_error_handler(['App\Integrations\Dav\Debug', 'exceptionErrorHandler']);
$enableWebDAV = false;

// Backends
$authBackend = new \App\Integrations\Dav\Backend\Auth($pdo);
$principalBackend = new \App\Integrations\Dav\Backend\AclPrincipal($pdo);
$nodes = [
	new Sabre\DAVACL\PrincipalCollection($principalBackend),
];
if ($enableCalDAV) {
	$calendarBackend = new \App\Integrations\Dav\Backend\Calendar($pdo);
	$nodes[] = new Sabre\CalDAV\Principal\Collection($principalBackend);
	$nodes[] = new Sabre\CalDAV\CalendarRoot($principalBackend, $calendarBackend);
}
if ($enableCardDAV) {
	$cardBackend = new \App\Integrations\Dav\Backend\Card($pdo);
	$nodes[] = new Sabre\CardDAV\AddressBookRoot($principalBackend, $cardBackend);
}

// The object tree needs in turn to be passed to the server class
\App\Integrations\Dav\Server::$exposeVersion = false;
$server = new \App\Integrations\Dav\Server($nodes);
$server->setBaseUri($_SERVER['SCRIPT_NAME']);
$server->debugExceptions = \App\Config::debug('davDebugExceptions', false);

// Plugins
$server->addPlugin(new Sabre\DAV\Auth\Plugin($authBackend));
$aclPlugin = new Sabre\DAVACL\Plugin();
$aclPlugin->adminPrincipals = [];
$server->addPlugin($aclPlugin);
if (\App\Config::api('enableBrowser')) {
	// Web/Browser interface for exploring DAV
	$server->addPlugin(new Sabre\DAV\Browser\Plugin());
}
if ($enableCardDAV) {
	// CardDav integration
	$server->addPlugin(new Sabre\CardDAV\Plugin());
}
if ($enableCalDAV) {
	// CalDAV integration
	$server->addPlugin(new Sabre\CalDAV\Plugin());
	$server->addPlugin(new Sabre\CalDAV\Subscriptions\Plugin());
	$server->addPlugin(new Sabre\CalDAV\Schedule\Plugin());
}
if ($enableWebDAV) {
	// WebDAV integration
	$server->addPlugin(new Sabre\DAV\Sync\Plugin());
}
if (\App\Config::debug('davDebugPlugin')) {
	$server->addPlugin(new \App\Integrations\Dav\Debug());
}

// Starts the DAV Server.
$server->start();
