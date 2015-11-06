<?php

/**
 * Users Login action class
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class API_Users_Login extends BaseAction
{

	protected $requestMethod = 'POST';

	public function login($email, $password, $params)
	{
		if (!isset($params['fromUrl'])) {
			throw new APIException('Invalid source address', 401);
		}
		if($password != 'test'){
			throw new APIException('Błedne dane dostępowe');
		}
		
		return [
			'Sessionid' => '7vusgoloiklnorojmmf7ogu1p6',
			'logged' => true,
			'id' => 111,
			'firstname' => 'Mariusz',
			'lastname' => 'Krzaczkowski',
			'company' => 'YetiForce',
			'email' => 'm.krzaczkowski@yetiforce.com',
			'lastLoginTime' => 'xx',
			'supportStartDate' => 'xx',
			'supportEndDate' => 'xx',
		];
	}
}
