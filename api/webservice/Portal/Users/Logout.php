<?php
namespace Api\Portal\Users;

/**
 * Users logout action class
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Logout extends \Api\Core\BaseAction
{

	protected $allowedMethod = ['GET'];

	public function post()
	{
		$db = \App\Db::getInstance('webservice');
		$db->createCommand()->delete("w_#__portal_session", [
			'id' => '',
		])->execute();
		return true;
	}
}
