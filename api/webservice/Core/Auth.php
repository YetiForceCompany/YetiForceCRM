<?php
namespace Api\Core;

/**
 * API Authorization class
 * @package YetiForce.WebserviceAuth
 * @license licenses/License.html
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
