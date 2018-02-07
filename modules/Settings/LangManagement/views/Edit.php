<?php

/**
 * Settings LangManagement edit view class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_LangManagement_Edit_View extends Settings_Vtiger_Index_View
{

	use App\Controller\ClearProcess;

	/**
	 * Process function
	 * @param App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$lang = $request->getByType('lang', 1);
		if (empty($lang)) {
			$lang = [];
		}
		$mod = $request->getByType('mod', 1);
		$tpl = $request->getByType('tpl', 1);
		$showDifferences = $request->getInteger('sd');
		$moduleModel = Settings_LangManagement_Module_Model::getInstance($qualifiedModuleName);
		$data = null;
		if (!empty($lang) && !empty($mod)) {
			if ($tpl === 'editLang') {
				$data = $moduleModel->loadLangTranslation($lang, $mod, $showDifferences);
			} else {
				$data = $moduleModel->loadAllFieldsFromModule($lang, $mod, $showDifferences);
			}
		}
		$mods = $moduleModel->getModFromLang($lang);
		unset($mods['mods']['HelpInfo']);
		$viewer->assign('MODS', $mods);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('SELECTED_LANGS', $lang);
		$viewer->assign('SELECTED_MODE', $mod);
		$viewer->assign('LANGS', App\Language::getAll());
		$viewer->assign('DATA', $data);
		$viewer->assign('SD', $showDifferences);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
		if ($tpl === 'editLang') {
			$viewer->view('Edit.tpl', $qualifiedModuleName);
		} else {
			$viewer->view('EditHelpIcon.tpl', $qualifiedModuleName);
		}
	}
}
