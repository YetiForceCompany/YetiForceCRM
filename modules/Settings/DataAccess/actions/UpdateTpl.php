<?php

/**
 * Settings DataAccess UpdateTpl action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_DataAccess_UpdateTpl_Action extends Settings_Vtiger_Index_Action
{

	public function checkPermission(\App\Request $request)
	{
		return;
	}

	public function process(\App\Request $request)
	{
		$baseModule = $request->get('base_module');
		$summary = $request->get('summary');
		$tplId = $request->get('tpl_id');
		$conditionAll = $request->getRaw('condition_all_json');
		$conditionOption = $request->getRaw('condition_option_json');
		$db = \App\Db::getInstance();
		$db->createCommand()->update('vtiger_dataaccess', [
				'module_name' => $baseModule,
				'summary' => $summary
				], ['dataaccessid' => $tplId])
			->execute();
		Settings_DataAccess_Module_Model::updateConditions($conditionAll, $tplId);
		Settings_DataAccess_Module_Model::updateConditions($conditionOption, $tplId, false);
		header("Location: index.php?module=DataAccess&parent=Settings&view=Index");
	}
}
