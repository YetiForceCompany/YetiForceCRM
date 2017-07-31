<?php

/**
 * Settings DataAccess condition view  class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_DataAccess_Condition_View extends Settings_Vtiger_Index_View
{

	public function process(\App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$baseModule = $request->get('base_module');
		$num = $request->get('num');
		if ("" == $num) {
			$num = 0;
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('NUM', ++$num);
		$viewer->assign('BASE_MODULE', $baseModule);
		$viewer->assign('FIELD_LIST', Settings_DataAccess_Module_Model::getListBaseModuleField($baseModule));
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		echo $viewer->view('Condition.tpl', $qualifiedModuleName, true);
	}
}
