<?php
/**
 * Developer tools for installer.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Installer;

/**
 * Developer class tools for installer.
 */
class Developer
{
	/**
	 * Generate interactive OpenAPI documentation for your RESTful API using doctrine annotations.
	 *
	 * @return string
	 */
	public static function generateSwagger(): string
	{
		set_error_handler(function ($errNo, $errStr, $errFile, $errLine) {
			$errorString = \App\ErrorHandler::error2string($errNo);
			$msg = reset($errorString) . ": $errStr in $errFile, line $errLine" . PHP_EOL;
			echo "<pre>$msg</pre><hr>";
		}, E_ALL);
		$json = '';
		foreach (['Portal', 'Mail'] as $type) {
			$json .= self::generateSwaggerByType($type, false);
		}
		return $json;
	}

	/**
	 * Generate interactive OpenAPI documentation for your RESTful API using doctrine annotations by type.
	 *
	 * @param string $type
	 * @param bool   $errorHandler
	 *
	 * @return string
	 */
	public static function generateSwaggerByType(string $type, $errorHandler = true): string
	{
		if ($errorHandler) {
			set_error_handler(function ($errNo, $errStr, $errFile, $errLine) {
				$errorString = \App\ErrorHandler::error2string($errNo);
				$msg = reset($errorString) . ": $errStr in $errFile, line $errLine" . PHP_EOL;
				echo "<pre>$msg</pre><hr>";
			}, E_ALL);
		}
		$openApi = \OpenApi\scan(ROOT_DIRECTORY . '/api/webservice/' . $type);
		$openApi->saveAs(ROOT_DIRECTORY . "/public_html/api/{$type}.json");
		$openApi->saveAs(ROOT_DIRECTORY . "/public_html/api/{$type}.yaml");
		return $openApi->toJson();
	}
}
