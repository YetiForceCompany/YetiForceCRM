<?php
/**
 * API Authorization file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\Core;

/**
 * API Authorization class.
 */
class Auth
{
	/**
	 * Realm.
	 *
	 * @var string
	 */
	protected static $realm = 'YetiForceApi';

	/**
	 * Init.
	 *
	 * @param \Api\Controller $controller
	 *
	 * @return Auth\AbstractAuth
	 */
	public static function init(\Api\Controller $controller): Auth\AbstractAuth
	{
		$method = \App\Config::api('AUTH_METHOD');
		$container = $controller->request->getByType('_container', \App\Purifier::STANDARD);
		$class = "Api\\{$container}\\Auth\\{$method}";
		if (!class_exists($class)) {
			$class = "Api\\Core\\Auth\\{$method}";
		}
		$self = new $class();
		$self->setApi($controller);
		$self->setServer();
		$self->authenticate(static::$realm);
		return $self;
	}
}
