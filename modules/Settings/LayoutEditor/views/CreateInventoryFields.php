<?php

/**
 * Inventory Field View Class
 * @package YetiForce.Views
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_LayoutEditor_CreateInventoryFields_View extends Settings_Vtiger_IndexAjax_View
{

	function __construct()
	{
		parent::__construct();
		$this->exposeMethod('step1');
		$this->exposeMethod('step2');
	}

	public function step1(Vtiger_Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		//echo '<pre>', print_r($request); echo '</pre>'; exit;
		$moduleName = $request->get('type');
		$models = Vtiger_InventoryField_Model::getAllFields($moduleName);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_MODELS', $models);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('CreateInventoryFieldsStep1.tpl', $qualifiedModuleName);
	}

	public function step2(Vtiger_Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$type = $request->get('mtype');
		$moduleName = $request->get('type');
		$id = $request->get('id');
		if ($id) {
			$instance = Vtiger_InventoryField_Model::getInstance($moduleName);
			$fieldInstance = $instance->getFields(false, [$id]);
		} else {
			$models = Vtiger_InventoryField_Model::getAllFields($moduleName);
			$fieldInstance = $models[$type];
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_MODEL', $fieldInstance);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('ID', $request->get('id'));
		$viewer->view('CreateInventoryFieldsStep2.tpl', $qualifiedModuleName);
	}
}
