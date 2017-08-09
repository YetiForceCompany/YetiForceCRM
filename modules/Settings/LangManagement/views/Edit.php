<?php

/**
 * Settings LangManagement edit view class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_LangManagement_Edit_View extends Settings_Vtiger_Index_View
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
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$lang = $request->get('lang');
		$mod = $request->get('mod');
		$tpl = $request->get('tpl');
		$ShowDifferences = $request->get('sd');
		$moduleModel = Settings_LangManagement_Module_Model::getInstance($qualifiedModuleName);
		$data = null;
		if ($lang != '' && $mod != '') {
			if ($tpl == 'editLang') {
				$data = $moduleModel->loadLangTranslation($lang, $mod, $ShowDifferences);
			} else {
				$data = $moduleModel->loadAllFieldsFromModule($lang, $mod, $ShowDifferences);
			}
		}
		$mods = $moduleModel->getModFromLang($lang);
		unset($mods['mods']['HelpInfo']);
		$viewer->assign('MODS', $mods);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('REQUEST', $request);
		$viewer->assign('LANGS', App\Language::getAll());
		$viewer->assign('DATA', $data);
		$viewer->assign('SD', $ShowDifferences);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
		if ($tpl == 'editLang') {
			$viewer->view('Edit.tpl', $qualifiedModuleName);
		} else {
			$viewer->view('EditHelpIcon.tpl', $qualifiedModuleName);
		}
	}
}
