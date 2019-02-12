<?php

/**
 * Settings OSSMailView index view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Widgets_Edit_View extends Settings_Vtiger_Index_View
{
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$wid = $request->getInteger('id');
		$moduleModel = Settings_Widgets_Module_Model::getInstance($qualifiedModuleName);
		$WidgetInfo = $moduleModel->getWidgetInfo($wid);
		$RelatedModule = $moduleModel->getRelatedModule($WidgetInfo['tabid']);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('SOURCE', $WidgetInfo['tabid']);
		$viewer->assign('SOURCEMODULE', \App\Module::getModuleName($WidgetInfo['tabid']));
		$viewer->assign('WID', $wid);
		$viewer->assign('WIDGETINFO', $WidgetInfo);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('RELATEDMODULES', $RelatedModule);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('Edit.tpl', $qualifiedModuleName);
	}
}
