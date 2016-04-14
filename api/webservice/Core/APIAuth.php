<?php

/**
 * API Authorization class
 * @package YetiForce.WebserviceAuth
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class APIAuth
{

	static protected $realm = 'YetiForceApi';

	public static function init($this)
	{
		$method = AppConfig::api('AUTH_METHOD');

		require_once 'api/webservice/Core/Auth/Abstract.php';
		require_once 'api/webservice/Core/Auth/' . $method . '.php';

		$class = $method . 'Auth';

		$intance = new $class();
		$intance->setApi($this);
		$intance->authenticate(self::$realm);
		return $intance->getCurrentServer();
	}
}
