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
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT 1 FROM w_yf_pos_users WHERE user_id = ?', [$userId]);
		if ($db->num_rows($result) > 0) {
			return true;
		}
		return false;
	}

	public function process(Vtiger_Request $request)
	{
		$keyLength = 32;
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

			$db = PearDatabase::getInstance();
			if (empty($id)) {
				$db->insert('w_yf_pos_users', [
					'user_name' => $userName,
					'user_id' => $userId,
					'pass' => $pass,
					'action' => empty($actionsPos) ? '' : implode(',', $actionsPos),
					'status' => $status == 'true' ? 1 : 0,
					'server_id' => $serverId,
					'first_name' => $firstName,
					'last_name' => $lastName,
					'email' => $email,
				]);
			} else {
				if (!is_array($actionsPos)) {
					$actionsPos = [];
				}
				$updates = [
					'action' => implode(',', $actionsPos),
					'status' => $status == 'true' ? 1 : 0,
					'server_id' => $serverId,
					'first_name' => $firstName,
					'last_name' => $lastName,
					'email' => $email,
				];
				$db->update('w_yf_pos_users', $updates, 'id = ?', [$id]);
			}
		}
		$responceToEmit = new Vtiger_Response();
		$responceToEmit->setResult($response);
		$responceToEmit->emit();
	}
}
