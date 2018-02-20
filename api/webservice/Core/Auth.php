<?php

namespace Api\Core;

/**
 * API Authorization class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Auth
{
	protected static $realm = 'YetiForceApi';

	public static function init($self)
	{
		$method = \AppConfig::api('AUTH_METHOD');
		$class = "Api\Core\Auth\\$method";
		$intance = new $class();
		$intance->setApi($self);
		$intance->authenticate(static::$realm);

		return $intance->getCurrentServer();
	}
}
