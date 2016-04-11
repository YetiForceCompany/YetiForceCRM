<?php

/**
 * Save Key
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_POS_SaveAjax_Action extends Settings_Vtiger_Index_Action
{

	function checkIfUsersExists($userId)
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT 1 FROM p_yf_users_pos WHERE user_id = ?', [$userId]);
		if ($db->num_rows($result) > 0) {
			return true;
		}
		return false;
	}

	function process(Vtiger_Request $request)
	{

		$keyLength = 32;
		$userId = $request->get('user');
		$id = $request->get('id');
		if (empty($id) && $this->checkIfUsersExists($userId)) {
			$response['success'] = false;
			$response['error'] = 'JS_THIS_USER_IS_EXIST';
		} else {
			$response['success'] = true;
			$userModel = Users_Record_Model::getInstanceById($userId, 'Users');
			$actionsPos = $request->get('actionPos');
			
			$db = PearDatabase::getInstance();
			if (empty($id)) {
				$key = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $keyLength);
				$db->insert('p_yf_users_pos', [
					'user_name' => $userModel->get('user_name'),
					'user_id' => $userId,
					'key' => $key,
					'action' => implode(',', $actionsPos)
				]);
			} else {
				if (!is_array($actionsPos)) {
					$actionsPos = [];
				}
				$updates = [
					'action' => implode(',', $actionsPos),
				];
				$db->update('p_yf_users_pos', $updates, 'id = ?', [$id]);
			}
		}
		$responceToEmit = new Vtiger_Response();
		$responceToEmit->setResult($response);
		$responceToEmit->emit();
	}
}
