<?php
namespace Api\Portal\Users;

/**
 * Users Login action class
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Login extends \Api\Core\BaseAction
{

	protected $allowedMethod = ['POST'];

	public function checkPermission()
	{
		return true;
	}

	public function post()
	{
		$db = \App\Db::getInstance('webservice');
		$row = (new \App\Db\Query())
				->from('w_#__portal_users')
				->where(['user_name' => $this->controller->request->get('userName'), 'status' => 1])
				->limit(1)->one($db);
		if (!$row) {
			throw new \Api\Core\Exception('Invalid data access', 401);
		}
		if ($row['password_t'] !== $this->controller->request->get('password')) {
			throw new \Api\Core\Exception('Invalid user password', 401);
		}
		$params = $this->controller->request->get('params');
		$db->createCommand()
			->update('w_#__portal_users', [
				'login_time' => date('Y-m-d H:i:s')
				], ['id' => $row['id']])
			->execute();
		$token = md5(time() . rand());
		$language = !empty($params['language']) ? $params['language'] : (empty($row['language']) ? $this->getLanguage() : $row['language']);
		$db->createCommand()->insert("w_#__portal_session", [
			'id' => $token,
			'user_id' => $row['id'],
			'created' => date('Y-m-d H:i:s'),
			'changed' => date('Y-m-d H:i:s'),
			'language' => $language,
			'params' => $params
		])->execute();
		return [
			'token' => $token,
			'firstName' => $row['first_name'],
			'lastName' => $row['last_name'],
			'company' => \App\Record::getLabel($row['parent_id']),
			'lastLoginTime' => $row['login_time'],
			'lastLogoutTime' => $row['logout_time'],
			'language' => $language,
			'logged' => true,
		];
	}
}
