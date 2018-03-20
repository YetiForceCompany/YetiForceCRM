<?php

/**
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_Inventory_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	use \App\Controller\ExposeMethod;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('checkDuplicateName');
		$this->exposeMethod('deleteInventory');
		$this->exposeMethod('saveConfig');
	}

	public function process(\App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);

			return;
		}
		$id = $request->get('id');
		$type = $request->getByType('view', 1);
		if (empty($id)) {
			$recordModel = new Settings_Inventory_Record_Model();
		} else {
			$recordModel = Settings_Inventory_Record_Model::getInstanceById($id, $type);
		}
		$fields = $request->getAll();
		foreach ($fields as $fieldName => $fieldValue) {
			if ($request->has($fieldName) && !in_array($fieldName, ['module', 'parent', 'view', '__vtrftk', 'action'])) {
				$recordModel->set($fieldName, $fieldValue);
			}
		}
		if ($type === 'Discounts') {
			$recordModel->set('value', CurrencyField::convertToDBFormat($recordModel->get('value')));
		}
		$recordModel->setType($type);

		$response = new Vtiger_Response();
		try {
			$id = $recordModel->save();
			$recordModel = Settings_Inventory_Record_Model::getInstanceById($id, $type);
			$response->setResult(array_merge(['_editurl' => $recordModel->getEditUrl(), 'row_type' => \App\User::getCurrentUserModel()->getDetail('rowheight')], $recordModel->getData()));
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	public function checkDuplicateName(\App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$id = $request->get('id');
		$name = $request->get('name');
		$type = $request->getByType('view', 1);

		$exists = Settings_Inventory_Record_Model::checkDuplicate($name, $id, $type);

		if (!$exists) {
			$result = ['success' => false];
		} else {
			$result = ['success' => true, 'message' => \App\Language::translate('LBL_NAME_EXIST', $qualifiedModuleName)];
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	public function deleteInventory(\App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$params = $request->get('param');
		$id = $params['id'];
		$type = $params['view'];

		$recordModel = Settings_Inventory_Record_Model::getInstanceById($id, $type);
		$status = $recordModel->delete();

		if (!$status) {
			$result = ['success' => false];
		} else {
			$result = ['success' => true, 'message' => \App\Language::translate('LBL_DELETE_OK', $qualifiedModuleName)];
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	public function saveConfig(\App\Request $request)
	{
		$params = $request->get('param');
		$type = $params['view'];

		$recordModel = Settings_Inventory_Module_Model::getCleanInstance();
		$status = $recordModel->setConfig($type, $params['param']);

		if (!$status) {
			$result = ['success' => false];
		} else {
			$result = ['success' => true];
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
