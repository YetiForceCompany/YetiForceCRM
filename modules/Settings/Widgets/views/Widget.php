<?php

/**
 * Settings OSSMailView index view class.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Widgets_Widget_View extends Settings_Vtiger_Index_View
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$mode = $request->getMode();
		if ($mode) {
			$this->{$mode}($request);
		} else {
			$this->createStep1($request);
		}
	}

	/**
	 * Create widget - first step.
	 *
	 * @param \App\Request $request
	 */
	public function createStep1(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$sourceModule = $request->getInteger('mod');
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$moduleModel = Settings_Widgets_Module_Model::getInstance($qualifiedModuleName);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('SOUNRCE_MODULE', $sourceModule);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('WidgetList.tpl', $qualifiedModuleName);
	}

	/**
	 * Create widget - second step.
	 *
	 * @param \App\Request $request
	 */
	public function createStep2(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$type = $request->getByType('type', 'Alnum');
		$tabId = $request->getInteger('tabId');
		$moduleModel = Settings_Widgets_Module_Model::getInstance($qualifiedModuleName);
		$widgetModuleName = \App\Module::getModuleName($tabId);
		$viewer->assign('TYPE', $type);
		$viewer->assign('SOURCE', $tabId);
		$viewer->assign('WID', '');
		$viewer->assign('WIDGETINFO', [
			'data' => [
				'limit' => 5, 'relatedmodule' => '',  'columns' => '',
				'action' => '', 'switchHeader' => '', 'filter' => '',
				'checkbox' => '', 'customView' => '[]',
			],
			'label' => '',
		]);
		$viewer->assign('SOURCEMODULE', $widgetModuleName);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('RELATEDMODULES', \App\Relation::getByModule($widgetModuleName));
		$className = Vtiger_Loader::getComponentClassName('Widget', $type, $widgetModuleName);
		if (class_exists($className)) {
			$widgetInstance = new $className($widgetModuleName, null, null, []);
			$tplName = $widgetInstance->getConfigTplName();
			$viewer->assign('WIDGET', $widgetInstance);
			$viewer->view("Detail/Widget/$tplName.tpl", $widgetModuleName);
		}
	}

	/**
	 * Edit widget.
	 *
	 * @param \App\Request $request
	 */
	public function edit(App\Request $request)
	{
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$wid = $request->getInteger('id');
		$moduleModel = Settings_Widgets_Module_Model::getInstance($qualifiedModuleName);
		$widgetInfo = $moduleModel->getWidgetInfo($wid);
		$widgetModuleName = \App\Module::getModuleName($widgetInfo['tabid']);
		$type = $widgetInfo['type'];
		$viewer = $this->getViewer($request);
		$viewer->assign('SOURCE', $widgetInfo['tabid']);
		$viewer->assign('SOURCEMODULE', $widgetModuleName);
		$viewer->assign('WID', $wid);
		$viewer->assign('WIDGETINFO', $widgetInfo);
		$viewer->assign('TYPE', $type);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('RELATEDMODULES', \App\Relation::getByModule($widgetModuleName));

		$className = Vtiger_Loader::getComponentClassName('Widget', $type, $widgetModuleName);
		if (class_exists($className)) {
			$widgetInstance = new $className($widgetModuleName, null, null, $widgetInfo);
			$tplName = $widgetInstance->getConfigTplName();
			$viewer->assign('WIDGET', $widgetInstance);
			$viewer->view("Detail/Widget/$tplName.tpl", $widgetModuleName);
		}
	}
}
