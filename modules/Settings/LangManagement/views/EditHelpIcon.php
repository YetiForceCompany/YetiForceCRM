<?php

/**
 * Settings LangManagement EditHelpIcon view class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_LangManagement_EditHelpIcon_View extends Settings_Vtiger_Index_View
{

	public function preProcess(\App\Request $request, $display = true)
	{
		
	}

	public function postProcess(\App\Request $request)
	{
		
	}

	/**
	 * Process function
	 * @param App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$lang = $request->get('lang');
		$mod = $request->get('mod');
		$ShowDifferences = $request->get('sd');
		$moduleModel = Settings_LangManagement_Module_Model::getInstance($qualifiedModuleName);
		if ($lang != '' && $mod != '') {
			$data = $moduleModel->loadAllFieldsFromModule($lang, $mod, $ShowDifferences);
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('REQUEST', $request);
		$viewer->assign('LANGS', App\Language::getAll());
		$viewer->assign('DATA', $data);
		$viewer->assign('SD', $ShowDifferences);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('EditHelpIcon.tpl', $qualifiedModuleName);
	}
}
