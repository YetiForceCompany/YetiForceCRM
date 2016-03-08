<?php

/**
 * Save notification
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_Notifications_SaveAjax_Action extends Settings_Vtiger_Index_Action
{

	function process(Vtiger_Request $request)
	{
		$db = PearDatabase::getInstance();
		$insertParams = [
			'name' => $request->get('name'),
			'role' => $request->get('roleId'),
			'width' => $request->get('width'),
			'height' => $request->get('height'),
		];
		if (($id = $request->get('id')) == 0) {
			$db->insert('a_yf_notification_type', $insertParams);
		} else {
			$db->update('a_yf_notification_type', $insertParams,'id = ?',[$id]);
		}
	}
}
