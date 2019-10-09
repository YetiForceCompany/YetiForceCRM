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
	 * @return void
	 */
	public static function generateSwagger(): void
	{
		set_error_handler(function ($errNo, $errStr, $errFile, $errLine) {
			$errorString = \App\ErrorHandler::error2string($errNo);
			$msg = reset($errorString) . ": $errStr in $errFile, line $errLine" . PHP_EOL;
			echo "<pre>$msg</pre><hr>";
		}, E_ALL);

		foreach (['Portal', 'Mail'] as $type) {
			self::generateSwaggerByType($type, false);
		}
	}

	/**
	 * Generate interactive OpenAPI documentation for your RESTful API using doctrine annotations by type.
	 *
	 * @param string $type
	 * @param bool   $errorHandler
	 *
	 * @return void
	 */
	public static function generateSwaggerByType(string $type, $errorHandler = true): void
	{
		if ($errorHandler) {
			set_error_handler(function ($errNo, $errStr, $errFile, $errLine) {
				$errorString = \App\ErrorHandler::error2string($errNo);
				$msg = reset($errorString) . ": $errStr in $errFile, line $errLine" . PHP_EOL;
				echo "<pre>$msg</pre><hr>";
			}, E_ALL);
		}
		$openApi = \OpenApi\scan(ROOT_DIRECTORY . '/api/webservice/' . $type);
		if (!headers_sent()) {
			header('Content-Type: application/json');
			echo $openApi->toJson();
		}
		$openApi->saveAs(ROOT_DIRECTORY . "/public_html/api/{$type}.json");
		$openApi->saveAs(ROOT_DIRECTORY . "/public_html/api/{$type}.yaml");
	}
}
