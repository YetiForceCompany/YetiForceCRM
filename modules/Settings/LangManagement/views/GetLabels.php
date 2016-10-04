<?php

/**
 * GetLabels View Class for LangManagement
 * @package YetiForce.ModalView
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_LangManagement_GetLabels_View extends Settings_Vtiger_BasicModal_View
{

	public function getSize(Vtiger_Request $request)
	{
		return 'modal-lg';
	}

	public function process(Vtiger_Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$langBase = $request->get('langBase');
		$lang = $request->get('lang');
		$sourceModule = $request->get('sourceModule');
		$data = [];
		if (!empty($lang) && $lang !== $langBase && !empty($sourceModule)) {
			$moduleModel = Settings_LangManagement_Module_Model::getInstance($qualifiedModuleName);
			$data = $moduleModel->getStatsData($langBase, $lang, $sourceModule);
			if (isset($data[$sourceModule])) {
				$data = $data[$sourceModule];
			}
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('LANG', $lang);
		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->assign('DATA', $data);
		$this->preProcess($request);
		$viewer->view('GetLabels.tpl', $qualifiedModuleName);
		$this->postProcess($request);
	}
}
