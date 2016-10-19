<?php

/**
 * Delete key
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_POS_Delete_Action extends Settings_Vtiger_Index_Action
{

	public function process(Vtiger_Request $request)
	{
		App\Db::getInstance()->createCommand()->delete('w_#__pos_users', ['id' => $request->get('id')])
			->execute();
	}
}
