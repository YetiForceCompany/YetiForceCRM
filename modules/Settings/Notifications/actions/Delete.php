<?php

/**
 * Save notification
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_Notifications_Delete_Action extends Settings_Vtiger_Index_Action
{

	public function process(Vtiger_Request $request)
	{
		$db = PearDatabase::getInstance();
		$id = $request->get('id');
		$db->delete('a_yf_notification_type', 'id = ?', [$id]);
	}
}
