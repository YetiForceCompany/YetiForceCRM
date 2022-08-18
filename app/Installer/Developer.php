<?php
/**
 * Developer tools for installer.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Installer;

/**
 * Developer class tools for installer.
 */
class Developer
{
	/** @var string Default path */
	public const PATH = '/api/doc';

	/**
	 * Generate interactive OpenAPI documentation for your RESTful API using doctrine annotations.
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public static function generateSwagger(string $path = self::PATH): string
	{
		set_error_handler(function ($errNo, $errStr, $errFile, $errLine) {
			$errorString = \App\ErrorHandler::error2string($errNo);
			$msg = reset($errorString) . ": {$errStr}\nFile: {$errFile}\nLine: $errLine" . PHP_EOL;
			echo "<pre>$msg</pre><hr>";
		}, E_ALL);
		$json = '';
		foreach (\Api\Core\Containers::$list as $type) {
			$json .= self::generateSwaggerByType($type, $path, false);
		}
		return $json;
	}

	/**
	 * Generate interactive OpenAPI documentation for your RESTful API using doctrine annotations by type.
	 *
	 * @param string $type
	 * @param bool   $errorHandler
	 * @param string $path
	 *
	 * @return string
	 */
	public static function generateSwaggerByType(string $type, string $path = self::PATH, $errorHandler = true): string
	{
		if ($errorHandler) {
			set_error_handler(function ($errNo, $errStr, $errFile, $errLine) {
				$errorString = \App\ErrorHandler::error2string($errNo);
				$msg = reset($errorString) . ": {$errStr}\nFile: {$errFile}\nLine: $errLine" . PHP_EOL;
				echo "<pre>$msg</pre><hr>";
			}, E_ALL);
		}
		$openApi = \OpenApi\Generator::scan([ROOT_DIRECTORY . '/api/webservice/' . $type]);
		$openApi->saveAs(ROOT_DIRECTORY . "{$path}/{$type}.json");
		$openApi->saveAs(ROOT_DIRECTORY . "{$path}/{$type}.yaml");
		return $openApi->toJson();
	}
}
