<?php

/**
 * Settings DataAccess DeleteAction action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_DataAccess_DeleteAction_Action extends Settings_Vtiger_Index_Action
{

	public function checkPermission(\App\Request $request)
	{
		return;
	}

	public function process(\App\Request $request)
	{
		$id = $request->get('id');
		$aid = $request->get('a');
		$baseModule = $request->get('m');
		Settings_DataAccess_Module_Model::deleteAction($id, $aid);
		header("Location: index.php?module=DataAccess&parent=Settings&view=Step3&tpl_id=$id&base_module=$baseModule&s=false");
	}
}
