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

	/** @var string[] Allowed request methods */
	public $allowedMethod = ['POST'];

	/**
	 * Check permission to method
	 * @return boolean
	 */
	public function checkPermission()
	{
		return true;
	}

	/**
	 * Check permission to module
	 * @throws \Api\Core\Exception
	 */
	public function checkPermissionToModule()
	{
		return true;
	}

	/**
	 * Post method
	 * @return array
	 */
	public function post()
	{
		$db = \App\Db::getInstance('webservice');
		$row = (new \App\Db\Query())
				->from('w_#__portal_user')
				->where(['user_name' => $this->controller->request->get('userName'), 'status' => 1])
				->limit(1)->one($db);
		if (!$row) {
			throw new \Api\Core\Exception('Invalid data access', 401);
		}
		if ($row['password_t'] !== $this->controller->request->get('password')) {
			throw new \Api\Core\Exception('Invalid user password', 401);
		}
		$db->createCommand()
			->update('w_#__portal_user', [
				'login_time' => date('Y-m-d H:i:s')
				], ['id' => $row['id']])
			->execute();
		$row = $this->updateSession($row);
		return [
			'token' => $row['token'],
			'name' => \App\Record::getLabel($row['crmid']),
			'parentName' => \App\Record::getLabel(\App\Record::getParentRecord($row['crmid'])),
			'lastLoginTime' => $row['login_time'],
			'lastLogoutTime' => $row['logout_time'],
			'language' => $row['language'],
			'type' => $row['type'],
			'logged' => true,
		];
	}

	/**
	 * Update session
	 * @param array $row
	 * @return array
	 */
	public function updateSession($row)
	{
		$db = \App\Db::getInstance('webservice');
		$token = md5(time() . rand());
		$params = $this->controller->request->get('params');
		$language = !empty($params['language']) ? $params['language'] : (empty($row['language']) ? $this->getLanguage() : $row['language']);
		$db->createCommand()->insert("w_#__portal_session", [
			'id' => $token,
			'user_id' => $row['id'],
			'created' => date('Y-m-d H:i:s'),
			'changed' => date('Y-m-d H:i:s'),
			'language' => $language,
			'params' => $this->controller->request->get('params')
		])->execute();
		$row['token'] = $token;
		$row['language'] = $language;
		$db->createCommand()->delete("w_#__portal_session", ['<', 'changed', date('Y-m-d H:i:s', strtotime('-1 day'))])->execute();
		return $row;
	}
}
