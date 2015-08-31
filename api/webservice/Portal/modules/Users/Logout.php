<?php

/**
 * Users Login action class
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class API_Users_Logout extends BaseAction
{

	protected $requestMethod = 'GET';

	public function logout()
	{
		
		return true;
	}
}
