<?php
namespace Api\Core;

/**
 * API Authorization class
 * @package YetiForce.WebserviceAuth
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Auth
{

	static protected $realm = 'YetiForceApi';

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
