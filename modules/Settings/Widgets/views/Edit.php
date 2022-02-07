<?php

/**
 * Settings OSSMailView index view class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Widgets_Edit_View extends Settings_Vtiger_Index_View
{
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$wid = $request->getInteger('id');
		$moduleModel = Settings_Widgets_Module_Model::getInstance($qualifiedModuleName);
		$widgetInfo = $moduleModel->getWidgetInfo($wid);
		$relatedModuleName = \App\Module::getModuleName($widgetInfo['tabid']);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('SOURCE', $widgetInfo['tabid']);
		$viewer->assign('SOURCEMODULE', $relatedModuleName);
		$viewer->assign('WID', $wid);
		$viewer->assign('WIDGETINFO', $widgetInfo);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('RELATEDMODULES', \App\Relation::getByModule($relatedModuleName));
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('Edit.tpl', $qualifiedModuleName);
	}
}
