<?php

/**
 * Record allocation
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_RecordAllocation_Index_View extends Settings_Vtiger_Index_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getPanel');
	}

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		parent::preProcess($request, $display);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('IndexPreProcess.tpl', $qualifiedModuleName);
	}

	public function process(Vtiger_Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$type = $request->get('type');
		if (empty($type)) {
			$type = 'owner';
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('TYPE', $type);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}

	public function getPanel(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$index = (int) $request->get('index');
		$type = $request->get('type');
		if (empty($type)) {
			$type = 'owner';
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('TYPE', $type);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODULE_NAME', $request->get('sourceModule'));
		$viewer->assign('MODULE_ID', vtlib\Functions::getModuleId($request->get('sourceModule')));
		$viewer->assign('INDEX', ++$index);
		$viewer->assign('DATA', Settings_RecordAllocation_Module_Model::getRecordAllocationByModule($type, $request->get('sourceModule')));
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('AddPanel.tpl', $qualifiedModuleName);
	}

	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = [
			'~libraries/jquery/datatables/media/js/jquery.dataTables.min.js',
			'~libraries/jquery/datatables/plugins/integration/bootstrap/3/dataTables.bootstrap.min.js'
		];
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	public function getHeaderCss(Vtiger_Request $request)
	{
		$headerCssInstances = parent::getHeaderCss($request);
		$cssFileNames = [
			'~libraries/jquery/datatables/media/css/jquery.dataTables_themeroller.css',
			'~libraries/jquery/datatables/plugins/integration/bootstrap/3/dataTables.bootstrap.css',
		];
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($headerCssInstances, $cssInstances);

		return $headerCssInstances;
	}
}
