<?php

/**
 * Settings DataAccess DeleteTemplate action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_DataAccess_DeleteTemplate_Action extends Settings_Vtiger_Index_Action
{

	public function checkPermission(\App\Request $request)
	{
		return;
	}

	public function process(\App\Request $request)
	{
		$tplId = $request->get('tpl_id');
		$db = \App\Db::getInstance();
		$db->createCommand()->delete('vtiger_dataaccess_cnd', ['dataaccessid' => $tplId])->execute();
		$db->createCommand()->delete('vtiger_dataaccess', ['dataaccessid' => $tplId])->execute();
		header("Location: index.php?module=DataAccess&parent=Settings&view=Index");
	}
}
