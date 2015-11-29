<?php

/**
 * GetChart Action Class for LangManagement Settings
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_LangManagement_GetChart_Action extends Settings_Vtiger_Basic_Action
{

	public function process(Vtiger_Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$langBase = $request->get('langBase');
		$langs = $request->get('langs');
		$tpl = $request->get('tpl');
		$modules = [];
		$data = [];
		if (!empty($langs) && $langs !== $langBase) {
			$moduleModel = Settings_LangManagement_Module_Model::getInstance($qualifiedModuleName);
			$modules = $moduleModel->getModFromLang($langBase);
			$data = $moduleModel->getStatsData($langBase, $langs);
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'data' => $data,
			'modules' => $modules
		]);
		$response->emit();
	}
}
