<?php
/**
 * YetiForce CLI.
 *
 * @package Cli
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
chdir(__DIR__);
try {
	require __DIR__ . '/include/RequirementsValidation.php';
} catch (\Exception $th) {
	echo str_repeat('--', 40) . PHP_EOL;
	echo $th->getMessage() . PHP_EOL;
	echo str_repeat('--', 40) . PHP_EOL;
	echo "Examples:\nphp74 cli.php\n/usr/local/php74/bin/php74 cli.php\n/usr/local/php80/bin/php80 cli.php\n";
	echo str_repeat('--', 40) . PHP_EOL;
	return;
}

require __DIR__ . '/include/main/WebUI.php';

\App\Process::$requestMode = 'Cli';

set_error_handler(function ($errNo, $errStr, $errFile, $errLine) {
	if (\in_array($errNo, [E_ERROR, E_WARNING, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
		throw new \Exception($errNo . ': ' . $errStr . ' in ' . $errFile . ', line ' . $errLine);
	}
}, E_ALL);

try {
	if (PHP_SAPI !== 'cli') {
		throw new \Exception('Console access only from the operating system CLI.');
	}
	new \App\Cli();
} catch (Throwable $e) {
	echo rtrim(str_replace(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR, '', $e->__toString()), PHP_EOL);
	if ('test' === \App\Config::main('systemMode')) {
		throw $e;
	}
}
