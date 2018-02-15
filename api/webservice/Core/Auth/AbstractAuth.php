<?php

namespace Api\Core\Auth;

/**
 * Base Abstract Authorization class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
abstract class AbstractAuth
{
	protected $currentServer;
	protected $api;

	public function setApi($api)
	{
		$this->api = $api;
	}

	abstract protected function authenticate($realm);

	abstract protected function validatePass($username, $password);

	public function getCurrentServer()
	{
		return $this->currentServer;
	}
}
