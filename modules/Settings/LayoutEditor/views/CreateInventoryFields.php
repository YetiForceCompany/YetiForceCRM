<?php

/**
 * Inventory Field View Class.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_LayoutEditor_CreateInventoryFields_View extends Settings_Vtiger_IndexAjax_View
{
	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('step1');
		$this->exposeMethod('step2');
	}

	public function step1(App\Request $request)
	{
		$instance = Vtiger_Inventory_Model::getInstance($request->getByType('sourceModule', 'Standard'));
		$viewer = $this->getViewer($request);
		$viewer->assign('FIELDS_EXISTS', $instance->getFields());
		$viewer->assign('MODULE_MODELS', $instance->getFieldsTypes());
		$viewer->assign('BLOCK', $request->getInteger('block'));
		$viewer->view('CreateInventoryFieldsStep1.tpl', $request->getModule(false));
	}

	public function step2(App\Request $request)
	{
		$inventory = Vtiger_Inventory_Model::getInstance($request->getByType('sourceModule', 'Standard'));
		if ($request->has('fieldName')) {
			$fieldInstance = $inventory->getField($request->getByType('fieldName', 'Alnum'));
		} else {
			$fieldInstance = $inventory->getFieldCleanInstance($request->getByType('type', 'Standard'));
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('FIELD_INSTANCE', $fieldInstance);
		$viewer->assign('INVENTORY_MODEL', $inventory);
		$viewer->view('CreateInventoryFieldsStep2.tpl', $request->getModule(false));
	}
}
