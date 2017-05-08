<?php
/**
 * Basic file to handle files
 * @package YetiForce.Files
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
define('REQUEST_MODE', 'File');
define('ROOT_DIRECTORY', __DIR__ !== DIRECTORY_SEPARATOR ? __DIR__ : '');

require 'include/main/WebUI.php';

try {
	$webUI = new App\Main\File();
	$webUI->process(App\Request::init());
} catch (Exception $e) {
	\App\Log::error($e->getMessage() . ' => ' . $e->getFile() . ':' . $e->getLine());
	//var_dump($e->getMessage());
	header('HTTP/1.1 400 Bad Request');
}




