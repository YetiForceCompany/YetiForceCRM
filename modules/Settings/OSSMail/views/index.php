<?php

/**
 * Settings OSSMail index view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_OSSMail_Index_View extends Settings_Vtiger_Index_View
{
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$OSSMail_Record_Model = Vtiger_Record_Model::getCleanInstance('OSSMail');
		if (\App\Module::getModuleId('OSSMailScanner')) {
			$OSSMailScanner_Record_Model = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
			$WidgetCfg = $OSSMailScanner_Record_Model->getConfig(false);
		}
		$viewer->assign('QUALIFIED_MODULE', $request->getModule(false));
		$viewer->assign('RecordModel', $OSSMail_Record_Model);
		$viewer->assign('WIDGET_CFG', $WidgetCfg);
		$viewer->assign('MODULENAME', $moduleName);
		echo $viewer->view('config.tpl', $moduleName, true);
	}
}
