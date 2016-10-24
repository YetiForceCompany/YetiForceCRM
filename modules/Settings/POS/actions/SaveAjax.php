<?php

/**
 * Save Key
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_POS_SaveAjax_Action extends Settings_Vtiger_Index_Action
{

	public function checkIfUsersExists($userId)
	{
		return (new \App\Db\Query())->from('w_#__pos_users')
			->where(['user_id' => $userId])
			->exists();
	}

	public function process(Vtiger_Request $request)
	{
		$userId = $request->get('user');
		$id = $request->get('id');
		$status = $request->get('status');
		$serverId = $request->get('server');
		$userName = $request->get('userName');
		$pass = $request->get('pass');
		$firstName = $request->get('firstName');
		$lastName = $request->get('lastName');
		$email = $request->get('email');
		if (empty($id) && $this->checkIfUsersExists($userId)) {
			$response['success'] = false;
			$response['error'] = 'JS_THIS_USER_IS_EXIST';
		} else {
			$response['success'] = true;
			$actionsPos = $request->get('actionPos');
			$db = App\Db::getInstance();
			if (empty($id)) {
				$db->createCommand()->insert('w_#__pos_users', [
					'user_name' => $userName,
					'user_id' => $userId,
					'pass' => $pass,
					'action' => empty($actionsPos) ? '' : implode(',', $actionsPos),
					'status' => $status == 'true' ? 1 : 0,
					'server_id' => $serverId,
					'first_name' => $firstName,
					'last_name' => $lastName,
					'email' => $email,
				])->execute();
			} else {
				if (!is_array($actionsPos)) {
					$actionsPos = [];
				}
				$db->createCommand()->update('w_#__pos_users', [
					'action' => implode(',', $actionsPos),
					'status' => $status == 'true' ? 1 : 0,
					'server_id' => $serverId,
					'first_name' => $firstName,
					'last_name' => $lastName,
					'email' => $email,
				], ['id' => $id])->execute();
			}
		}
		$responceToEmit = new Vtiger_Response();
		$responceToEmit->setResult($response);
		$responceToEmit->emit();
	}
}
