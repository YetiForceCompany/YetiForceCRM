<?php

abstract class AbstractAuth
{

	protected $currentServer;
	protected $api;

	function setApi($api)
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
