<?php
/**
 * YetiForce CLI.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
chdir(__DIR__);

require __DIR__ . '/include/RequirementsValidation.php';
require __DIR__ . '/include/main/WebUI.php';

\App\Process::$requestMode = 'Cli';

set_error_handler(function ($errNo, $errStr, $errFile, $errLine) {
	if (\in_array($errNo, [E_ERROR, E_WARNING, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
		throw new \Exception($errNo . ': ' . $errStr . ' in ' . $errFile . ', line ' . $errLine);
	}
}, E_ALL);

try {
	new \App\Cli();
} catch (Throwable $e) {
	echo $e->__toString();
	if ('test' === \App\Config::main('systemMode')) {
		throw $e;
	}
}
