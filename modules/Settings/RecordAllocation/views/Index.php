<?php

/**
 * Record allocation.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_RecordAllocation_Index_View extends Settings_Vtiger_Index_View
{
	use \App\Controller\ExposeMethod;

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getPanel');
		$this->exposeMethod('sharedOwner');
		$this->exposeMethod('owner');
	}

	public function preProcess(App\Request $request, $display = true)
	{
		parent::preProcess($request, $display);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('IndexPreProcess.tpl', $qualifiedModuleName);
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
		}
	}

	/**
	 * Owner view.
	 *
	 * @param App\Request $request
	 */
	public function owner(App\Request $request)
	{
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('TYPE', $request->getMode());
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}

	/**
	 * Shared owner view.
	 *
	 * @param App\Request $request
	 */
	public function sharedOwner(App\Request $request)
	{
		$this->owner($request);
	}

	public function getPanel(App\Request $request)
	{
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$index = $request->getInteger('index');
		$type = $request->isEmpty('type', true) ? 'owner' : $request->getByType('type', 'Alnum');
		$viewer = $this->getViewer($request);
		$viewer->assign('TYPE', $type);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODULE_NAME', $request->getByType('sourceModule', 2));
		$viewer->assign('MODULE_ID', \App\Module::getModuleId($request->getByType('sourceModule', 2)));
		$viewer->assign('INDEX', ++$index);
		$viewer->assign('DATA', Settings_RecordAllocation_Module_Model::getRecordAllocationByModule($type, $request->getByType('sourceModule', 2)));
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('AddPanel.tpl', $qualifiedModuleName);
	}

	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'~libraries/datatables.net/js/jquery.dataTables.js',
			'~libraries/datatables.net-bs4/js/dataTables.bootstrap4.js',
			'~libraries/datatables.net-responsive/js/dataTables.responsive.js',
			'~libraries/datatables.net-responsive-bs4/js/responsive.bootstrap4.js'
		]));
	}

	public function getHeaderCss(App\Request $request)
	{
		return array_merge(parent::getHeaderCss($request), $this->checkAndConvertCssStyles([
			'~libraries/datatables.net-bs4/css/dataTables.bootstrap4.css',
			'~libraries/datatables.net-responsive-bs4/css/responsive.bootstrap4.css'
		]));
	}
}
