<?php
/**
 * Basic file to handle files.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
define('ROOT_DIRECTORY', __DIR__ !== DIRECTORY_SEPARATOR ? __DIR__ : '');

require __DIR__ . '/include/main/WebUI.php';
\App\Config::$requestMode = 'File';
try {
	$webUI = new App\Main\File();
	$webUI->process(App\Request::init());
} catch (Exception $e) {
	\App\Log::error($e->getMessage() . ' => ' . $e->getFile() . ':' . $e->getLine());
	header('HTTP/1.1 ' . $e->getCode() . ' ' . $e->getMessage());
}
