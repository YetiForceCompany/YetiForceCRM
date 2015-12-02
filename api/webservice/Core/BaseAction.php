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

	/**
	 * Function to get the value for a given key
	 * @param $key
	 * @return Value for the given key
	 */
	public function getRequestMethod()
	{
		return $this->requestMethod;
	}
}
