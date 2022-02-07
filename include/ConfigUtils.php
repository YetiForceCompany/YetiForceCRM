<?php

/**
 * Basis of environmental initiation.
 *
 * @copyright  YetiForce S.A.
 * @license    YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
if (!\defined('ROOT_DIRECTORY')) {
	\define('ROOT_DIRECTORY', str_replace(DIRECTORY_SEPARATOR . 'include', '', __DIR__));
}
require_once ROOT_DIRECTORY . '/vendor/autoload.php';
if (!headers_sent()) {
	session_save_path(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'session');
}
if (!\defined('IS_PUBLIC_DIR')) {
	\define('IS_PUBLIC_DIR', false);
}
if (\App\Config::debug('EXCEPTION_ERROR_HANDLER')) {
	\App\ErrorHandler::init();
}
if (($timeZone = \App\Config::main('default_timezone')) && \function_exists('date_default_timezone_set')) {
	date_default_timezone_set($timeZone);
}
