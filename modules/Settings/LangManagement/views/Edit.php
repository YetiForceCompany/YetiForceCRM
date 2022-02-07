<?php

/**
 * Settings LangManagement edit view class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_LangManagement_Edit_View extends Settings_Vtiger_Index_View
{
	use App\Controller\ClearProcess;

	/**
	 * Process function.
	 *
	 * @param App\Request $request
	 */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$lang = $request->getByType('lang', 1);
		if (empty($lang)) {
			$lang = [];
		}
		$mod = $request->getByType('mod', 1);
		$showDifferences = $request->getInteger('sd');
		$moduleModel = Settings_LangManagement_Module_Model::getInstance($qualifiedModuleName);
		$data = null;
		if (!empty($lang) && !empty($mod)) {
			$data = $moduleModel->loadLangTranslation($lang, $mod);
		}
		$mods = $moduleModel->getModFromLang(reset($lang));
		unset($mods['mods']['HelpInfo']);
		$viewer->assign('MODS', $mods);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('SELECTED_LANGS', $lang);
		$viewer->assign('SELECTED_MODE', $mod);
		$viewer->assign('LANGS', App\Language::getAll());
		$viewer->assign('DATA', $data);
		$viewer->assign('CUSTOM_DATA', $moduleModel->loadCustomLanguageFile($lang, $mod));
		$viewer->assign('SD', $showDifferences);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('Edit.tpl', $qualifiedModuleName);
	}
}
