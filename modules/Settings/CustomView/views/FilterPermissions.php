<?php

/**
 * FilterPermissions View Class for CustomView.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_CustomView_FilterPermissions_View extends Settings_Vtiger_BasicModal_View
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule(false);
		$sourceModuleId = $request->getInteger('sourceModule');
		$type = $request->getByType('type', \App\Purifier::STANDARD);
		$moduleModel = Settings_LangManagement_Module_Model::getInstance($moduleName);
		$recordModel = CustomView_Record_Model::getInstanceById($request->getInteger('cvid'));
		$viewer = $this->getViewer($request);
		$viewer->assign('IS_DEFAULT', $request->getBoolean('isDefault'));
		$viewer->assign('TYPE', $type);
		$viewer->assign('TITLE_LABEL', ['default' => 'SetDefault', 'featured' => 'LBL_FEATURED_LABELS', 'permissions' => 'LBL_PRIVILEGES_TO_VIEW'][$type]);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('SOURCE_MODULE', $sourceModuleId);
		$viewer->assign('CVID', $recordModel->getId());
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$this->preProcess($request);
		$viewer->view('FilterPermissions.tpl', $moduleName);
		$this->postProcess($request);
	}
}
