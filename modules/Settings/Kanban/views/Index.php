<?php

/**
 * Settings kanban index view file.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Settings kanban index view class.
 */
class Settings_Kanban_Index_View extends Settings_Vtiger_Index_View
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$supportedModulesList = Settings_LayoutEditor_Module_Model::getSupportedModules();
		$sourceModuleName = $request->getByType('sourceModule', \App\Purifier::ALNUM);
		if (empty($sourceModuleName)) {
			$sourceModuleName = reset($supportedModulesList);
		}
		$moduleModel = Vtiger_Module_Model::getInstance($sourceModuleName);
		$viewer = $this->getViewer($request);
		$viewer->assign('SUPPORTED_MODULES', $supportedModulesList);
		$viewer->assign('SELECTED_MODULE_NAME', $sourceModuleName);
		$viewer->assign('FIELDS_MODELS', $moduleModel->getFieldsById());
		$viewer->assign('SUM_FIELDS_MODELS', $moduleModel->getFieldsByType(['totalTime', 'double', 'integer', 'currency']));
		$viewer->assign('BOARDS', \App\Utils\Kanban::getBoards($sourceModuleName));
		$viewer->view('Index.tpl', $request->getModule(false));
	}
}
