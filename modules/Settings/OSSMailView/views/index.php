<?php

/**
 * Settings OSSMailView index view class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_OSSMailView_Index_View extends Settings_Vtiger_Index_View
{

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();

		$OSSMailScanner_Record_Model = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
		$WidgetCfg = $OSSMailScanner_Record_Model->getConfig(false);


		$viewer = $this->getViewer($request);
		$viewer->assign('MODULENAME', $moduleName);
		$viewer->assign('WIDGET_CFG', $WidgetCfg);
		echo $viewer->view('index.tpl', $moduleName, true);
	}
}
