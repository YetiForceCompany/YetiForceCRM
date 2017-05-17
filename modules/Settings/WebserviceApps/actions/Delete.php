<?php

/**
 * Delete Application
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_WebserviceApps_Delete_Action extends Settings_Vtiger_Index_Action
{

	public function process(\App\Request $request)
	{
		$db = PearDatabase::getInstance();
		$db->delete('w_yf_servers', 'id = ?', [$request->get('id')]);
	}
}
