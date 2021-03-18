<?php
/**
 * Post-installation script for Docker.
 */
$path = '/etc/mysql/debian.cnf';
if (file_exists($path)) {
	$content = str_replace('password = ', 'password = ' . getenv('DB_ROOT_PASS'), file_get_contents($path));
	file_put_contents($path, $content);
}

$path = '/etc/php/' . getenv('PHP_VER') . '/fpm/pool.d/www.conf';
if (file_exists($path)) {
	$conf = PHP_EOL . ';####' . PHP_EOL . ';# Best FastCGI Process Manager configuration for YetiForceCRM' . PHP_EOL . ';####' . PHP_EOL . PHP_EOL .
	'env[PROVIDER] = docker' . PHP_EOL .
	'php_admin_value[open_basedir] = /var/www/html/:/tmp/:/var/tmp/:/etc/nginx/ssl/:/etc/ssl/' . PHP_EOL .
	'clear_env = no' . PHP_EOL .
	'request_terminate_timeout = 600' . PHP_EOL .
	'pm.max_requests = 5000' . PHP_EOL .
	'pm.process_idle_timeout = 600s;';
	file_put_contents($path, $conf, FILE_APPEND);
}

if ('TEST' === getenv('INSTALL_MODE')) {
	chdir(__DIR__ . '/../../');
	include_once 'include/main/WebUI.php';

	$configFile = new \App\ConfigFile('db');
	$configFile->set('db_server', 'localhost');
	$configFile->set('db_port', 3306);
	$configFile->set('db_username', 'yetiforce');
	$configFile->set('db_password', 'Q4WK2yRUpliyjMRivDJE');
	$configFile->set('db_type', 'mysql');
	$configFile->set('db_name', 'yetiforce');
	$configFile->create();

	$configFile = new \App\ConfigFile('main');
	$configFile->set('site_URL', 'http://localhost/');
	$configFile->set('default_language', 'en-US');
	$configFile->set('default_timezone', 'Europe/Warsaw');
	$configFile->set('systemMode', 'test');
	$configFile->set('langInLoginView', true);
	$configFile->set('layoutInLoginView', true);
	$configFile->create();

	$configFile = new \App\ConfigFile('debug');
	$configFile->set('LOG_TO_FILE', true);
	$configFile->set('LOG_LEVELS', ['error', 'warning']);
	$configFile->set('LOG_TRACE_LEVEL', 9);
	$configFile->set('EXCEPTION_ERROR_TO_SHOW', true);
	$configFile->set('DISPLAY_EXCEPTION_BACKTRACE', true);
	$configFile->set('DISPLAY_EXCEPTION_LOGS', true);
	$configFile->set('EXCEPTION_ERROR_TO_FILE', true);
	$configFile->set('DEBUG_CRON', true);
	$configFile->set('apiShowExceptionMessages', true);
	$configFile->set('apiShowExceptionReasonPhrase', true);
	$configFile->set('apiShowExceptionBacktrace', true);
	$configFile->set('apiLogAllRequests', true);
	$configFile->set('DAV_DEBUG_EXCEPTIONS', true);
	$configFile->set('DAV_DEBUG_PLUGIN', true);
	$configFile->set('SMARTY_ERROR_REPORTING', new \Nette\PhpGenerator\PhpLiteral('E_ALL'));
	$configFile->set('EXCEPTION_ERROR_LEVEL', new \Nette\PhpGenerator\PhpLiteral('E_ALL'));
	$configFile->create();

	$configFile = new \App\ConfigFile('developer');
	$configFile->set('CHANGE_GENERATEDTYPE', true);
	$configFile->set('MINIMIZE_JS', false);
	$configFile->set('MINIMIZE_CSS', false);
	$configFile->set('CHANGE_VISIBILITY', true);
	$configFile->set('CHANGE_RELATIONS', true);
	$configFile->set('MISSING_LIBRARY_DEV_MODE', true);
	$configFile->set('LANGUAGES_UPDATE_DEV_MODE', true);
	$configFile->set('updaterDevMode', true);
	$configFile->create();

	$configFile = new \App\ConfigFile('relation');
	$configFile->set('SHOW_RECORDS_COUNT', true);
	$configFile->create();

	$configFile = new \App\ConfigFile('security');
	$configFile->set('RESET_LOGIN_PASSWORD', true);
	$configFile->set('PERMITTED_BY_PRIVATE_FIELD', false);
	$configFile->set('loginSessionRegenerate', false);
	$configFile->set('cookieForceHttpOnly', false);
	$configFile->set('askAdminAboutVisitPurpose', false);
	$configFile->create();

	\App\Config::set('module', 'OSSMail', 'root_directory', new \Nette\PhpGenerator\PhpLiteral('ROOT_DIRECTORY . DIRECTORY_SEPARATOR'));
	$skip = ['db', 'main', 'debug', 'developer', 'security', 'module', 'component'];
	foreach (array_diff(\App\ConfigFile::TYPES, $skip) as $type) {
		(new \App\ConfigFile($type))->create();
	}
	$dirPath = \ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'config' . \DIRECTORY_SEPARATOR . 'Modules';
	if (!is_dir($dirPath)) {
		mkdir($dirPath);
	}
	foreach ((new \DirectoryIterator('modules/')) as $item) {
		if ($item->isDir() && !\in_array($item->getBasename(), ['.', '..'])) {
			$moduleName = $item->getBasename();
			$filePath = 'modules' . \DIRECTORY_SEPARATOR . $moduleName . \DIRECTORY_SEPARATOR . 'ConfigTemplate.php';
			if (file_exists($filePath)) {
				(new \App\ConfigFile('module', $moduleName))->create();
			}
		}
	}
	$path = \ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'config' . \DIRECTORY_SEPARATOR . 'Components' . \DIRECTORY_SEPARATOR . 'ConfigTemplates.php';
	$componentsData = require_once "$path";
	foreach ($componentsData as $component => $data) {
		(new \App\ConfigFile('component', $component))->create();
	}
}
