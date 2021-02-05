<?php
/**
 * YetiForce CLI.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
\define('ROOT_DIRECTORY', __DIR__ !== DIRECTORY_SEPARATOR ? __DIR__ : '');

require __DIR__ . '/include/main/WebUI.php';
require __DIR__ . '/include/RequirementsValidation.php';

\App\Process::$requestMode = 'Cli';
try {
	new \App\Cli();
} catch (Throwable $e) {
	echo $e->__toString();
}
