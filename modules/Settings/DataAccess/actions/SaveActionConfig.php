<?php

/**
 * Settings DataAccess SaveActionConfig action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_DataAccess_SaveActionConfig_Action extends Settings_Vtiger_Index_Action
{

	public function checkPermission(\App\Request $request)
	{
		return;
	}

	public function process(\App\Request $request)
	{
		$tpl_id = $request->get('tpl_id');
		$base_module = $request->get('base_module');
		Settings_DataAccess_Module_Model::saveActionConfig($tpl_id, $request->get('an'), $request->get('data'), $request->get('aid'));
		header("Location: index.php?module=DataAccess&parent=Settings&view=Step3&tpl_id=$tpl_id&base_module=$base_module&s=false");
	}
}
