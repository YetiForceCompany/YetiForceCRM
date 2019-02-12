<?php

/**
 * Save Inventory Action Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
		$inventory = Vtiger_Inventory_Model::getInstance($request->getByType('sourceModule', 'Standard'));
		if ($isNew = $request->isEmpty('id')) {
			$fieldModel = $inventory->getFieldCleanInstance($request->getByType('type'));
			$fieldModel->setDefaultDataConfig();
		} else {
			$fieldModel = $inventory->getFieldById($request->getInteger('id'));
		}
		$fields = ['label', 'defaultValue', 'block', 'displayType', 'params', 'colSpan'];
		foreach ($fields as $name) {
			if ($request->has($name)) {
				switch ($name) {
					case 'label':
						$fieldModel->set($name, $request->getByType($name, 'Text'));
						break;
					case 'defaultValue':
						$fieldModel->set($name, $request->getByType($name, 'Text'));
						break;
					case 'block':
						$blockId = $request->getInteger($name);
						if (!in_array($blockId, $fieldModel->getBlocks())) {
							throw new \App\Exceptions\IllegalValue("ERR_NOT_ALLOWED_VALUE||{$name}||" . $blockId, 406);
						}
						$fieldModel->set($name, $blockId);
						break;
					case 'displayType':
						$displayType = $request->getInteger($name);
						if (!in_array($displayType, $fieldModel->displayTypeBase())) {
							throw new \App\Exceptions\IllegalValue("ERR_NOT_ALLOWED_VALUE||{$name}||" . $displayType, 406);
						}
						$fieldModel->set($name, $displayType);
						break;
					case 'params':
						$fieldModel->set($name, $request->getByType($name, 'Text'));
						break;
					case 'colSpan':
						$fieldModel->set($name, $request->getInteger($name));
						break;
					default:
						break;
				}
			}
		}
		$inventory->saveField($fieldModel);
		$data = $fieldModel->getData();
		$data['translate'] = \App\Language::translate($data['label'], $fieldModel->getModuleName());
		$response = new Vtiger_Response();
		$response->setResult(['data' => $data, 'edit' => !$isNew]);
		$response->emit();
	}

	public function saveSequence(\App\Request $request)
	{
		$inventoryField = Vtiger_Inventory_Model::getInstance($request->getByType('sourceModule', 'Standard'));
		$status = $inventoryField->saveSequence($request->getArray('ids', 'Integer'));
		$response = new Vtiger_Response();
		$response->setResult(['success' => (bool) $status]);
		$response->emit();
	}

	public function delete(\App\Request $request)
	{
		$inventory = Vtiger_Inventory_Model::getInstance($request->getByType('sourceModule', 'Standard'));
		$status = $inventory->deleteField($request->getByType('fieldName', 'Alnum'));
		$response = new Vtiger_Response();
		$response->setResult($status);
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
		\App\Language::translationModify($request->getByType('lang'), 'HelpInfo', 'php', $label, str_replace("\n", '', $request->getForHtml('context')));
		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}
}
