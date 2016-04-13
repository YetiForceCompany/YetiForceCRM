<?php

/**
 * Base action class
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class BaseAction
{

	public $api = [];
	protected $requestMethod = [];

	/**
	 * Function to get the value for a given key
	 * @param $key
	 * @return Value for the given key
	 */
	public function getRequestMethod()
	{
		return $this->requestMethod;
	}

	public function options()
	{
		header('Allow: ' . implode(',', $this->requestMethod));
		header('HTTP/1.1 200 OK');
	}
}
