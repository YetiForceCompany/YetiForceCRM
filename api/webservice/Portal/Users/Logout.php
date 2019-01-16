<?php

namespace Api\Portal\Users;

/**
 * Users logout action class.
 *
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Logout extends \Api\Core\BaseAction
{
	/** @var string[] Allowed request methods */
	public $allowedMethod = ['PUT'];

	/**
	 * Check permission to module.
	 *
	 * @throws \Api\Core\Exception
	 */
	public function checkPermissionToModule()
	{
		return true;
	}

	/**
	 * Put method.
	 *
	 * @return array
	 */
	public function put()
	{
		$db = \App\Db::getInstance('webservice');
		$db->createCommand()->delete('w_#__portal_session', [
			'id' => $this->controller->headers['x-token'],
		])->execute();
		$db->createCommand()
			->update('w_#__portal_user', [
				'logout_time' => date('Y-m-d H:i:s'),
				], ['id' => $this->session->get('id')])
				->execute();

		return true;
	}
}
