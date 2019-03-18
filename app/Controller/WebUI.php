<?php
/**
 * Web UI file.
 *
 * @package   Controller
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Controller;

/**
 * WebUi class.
 */
class WebUI extends Base
{
	/**
	 * Construct.
	 *
	 * @param WebApi $controller
	 */
	public function __construct()
	{
		$this->request = \App\Request::init();
	}

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission()
	{
	}

	/**
	 * Requirements validation.
	 *
	 * @throws \App\Exceptions\AppException
	 */
	protected function requirementsValidation()
	{
		if (version_compare(PHP_VERSION, '7.1', '<')) {
			throw new \App\Exceptions\AppException('Wrong PHP version, recommended version >= 7.1');
		}
	}

	/**
	 * Process.
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function process()
	{
		if (!\App\Config::main('application_unique_key', false)) {
			header('location: install/Install.php');
		}
		if (\App\Config::main('forceSSL') && !\App\RequestUtil::getBrowserInfo()->https) {
			header("location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}", true, 301);
		}
		if (\App\Config::main('forceRedirect')) {
			$requestUrl = (\App\RequestUtil::getBrowserInfo()->https ? 'https' : 'http') . '://' . $this->request->getServer('HTTP_HOST') . $this->request->getServer('REQUEST_URI');
			if (0 !== stripos($requestUrl, \App\Config::main('site_URL'))) {
				header('location: ' . \App\Config::main('site_URL'), true, 301);
			}
		}
		$this->init();
		$this->requirementsValidation();
	}

	/**
	 * Get environment variables.
	 *
	 * @return string
	 */
	public function getEnv(): string
	{
		$lang = \App\Language::getLanguage();

		return \App\Json::encode([
			'Env' => [
				'baseURL' => \App\Config::main('site_URL'),
				'publicDir' => '/dist',
				'routerMode' => 'hash',
			],
			'Language' => [
				'lang' => $lang,
				'translations' => \App\Language::getLanguageData($lang),
			],
			'Debug' => [
				'levels' => ['error']
			],
			'Users' => ['isLoggedIn' => \App\User::isLoggedIn()]
		]);
	}
}
