<?php

/**
 * Save Inventory Action Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_LayoutEditor_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('setInventory');
		$this->exposeMethod('saveInventoryField');
		$this->exposeMethod('saveSequence');
		$this->exposeMethod('delete');
		$this->exposeMethod('contextHelp');
	}

	public function setInventory(\App\Request $request)
	{
		$param = $request->get('param');
		$moduleName = $param['module'];
		$status = false;
		$inventoryInstance = Vtiger_Inventory_Model::getInstance($moduleName);
		$status = $inventoryInstance->setMode($param['status']);
		if ($status) {
			$status = true;
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => $status, ]
		);
		$response->emit();
	}

	/**
	 * Function is used to create and edit fields in advanced block.
	 *
	 * @param \App\Request $request
	 */
	public function saveInventoryField(\App\Request $request)
	{
		$param = $request->get('param');
		$moduleName = $param['module'];
		$name = $param['name'];
		$id = (int) $param['id'];
		$edit = false;
		$inventoryField = Vtiger_InventoryField_Model::getInstance($moduleName);
		if (!empty($id)) {
			$inventoryField->saveField($name, $param);
			$edit = true;
		} else {
			$id = $inventoryField->addField($name, $param);
		}
		$arrayInstane = $inventoryField->getFields(false, [$id], 'Settings');
		$data = [];
		if (current($arrayInstane)) {
			$data = current($arrayInstane)->getData();
			$data['translate'] = \App\Language::translate($data['label'], $moduleName);
		}
		$response = new Vtiger_Response();
		$response->setResult(['data' => $data, 'edit' => $edit]);
		$response->emit();
	}

	public function saveSequence(\App\Request $request)
	{
		$param = $request->get('param');
		$moduleName = $param['module'];
		$inventoryField = Vtiger_InventoryField_Model::getInstance($moduleName);
		$status = $inventoryField->saveSequence($param['ids']);
		if ($status) {
			$status = true;
		}
		$response = new Vtiger_Response();
		$response->setResult(['success' => $status]);
		$response->emit();
	}

	public function delete(\App\Request $request)
	{
		$param = $request->get('param');
		$moduleName = $param['module'];
		$inventoryField = Vtiger_InventoryField_Model::getInstance($moduleName);
		$status = $inventoryField->delete($param);
		if ($status) {
			$status = true;
		}
		$response = new Vtiger_Response();
		$response->setResult(['success' => $status]);
		$response->emit();
	}

	/**
	 * Set context help.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedForAdmin
	 * @throws \App\Exceptions\Security
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function contextHelp(\App\Request $request)
	{
		$fieldModel = \Vtiger_Field_Model::getInstanceFromFieldId($request->getInteger('field'));
		if (!\App\Privilege::isPermitted($fieldModel->getModuleName())) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
		if (!isset(App\Language::getAll()[$request->getByType('lang')])) {
			throw new \App\Exceptions\Security('ERR_LANGUAGE_DOES_NOT_EXIST');
		}
		$views = $request->getArray('views', 'Standard');
		if ($views && array_diff($views, \App\Field::HELP_INFO_VIEWS)) {
			throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE', 406);
		}
		$fieldModel->set('helpinfo', implode(',', $views));
		$fieldModel->save();
		$label = $fieldModel->getModuleName() . '|' . $fieldModel->getFieldLabel();
		\App\Language::translationModify($request->getByType('lang'), 'HelpInfo', 'php', $label, $request->getForHtml('context'));
		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}
}
