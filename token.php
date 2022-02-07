<?php
/**
 * Token file.
 *
 * @package Token
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
chdir(__DIR__);
require __DIR__ . '/include/main/WebUI.php';
require __DIR__ . '/include/RequirementsValidation.php';

\App\Controller\Headers::getInstance()->send();
\App\Process::$startTime = microtime(true);
\App\Process::$requestMode = 'Token';

if (!\App\Config::main('application_unique_key', false)) {
	header('location: install/Install.php');
} else {
	try {
		$request = \App\Request::init();
		if ($request->isEmpty('token')) {
			throw new \App\Exceptions\AppException('ERR_NO_TOKEN', 405);
		}
		$token = $request->getByType('token', \App\Purifier::ALNUM);
		$tokenData = \App\Utils\Tokens::get($token);
		if (empty($tokenData)) {
			throw new \App\Exceptions\Security('ERR_TOKEN_DOES_NOT_EXIST', 405);
		}
		$result = \App\Utils\Tokens::execute($token, $tokenData);
		if (isset($result['redirect'])) {
			header("location: {$result['redirect']}");
		}
	} catch (\Throwable $th) {
		$message = $th->getMessage();
		if ($th instanceof \App\Exceptions\AppException) {
			$message = $th->getDisplayMessage();
		}
		echo $message;
		\App\Log::info($th->getMessage() . PHP_EOL . $th->__toString());
	}
}
