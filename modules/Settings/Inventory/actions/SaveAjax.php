<?php

/**
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_Inventory_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	use \App\Controller\ExposeMethod;

	/**
	 * Settings_Inventory_SaveAjax_Action constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('checkDuplicateName');
		$this->exposeMethod('deleteInventory');
		$this->exposeMethod('saveConfig');
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
		$type = $request->getByType('view', 1);
		if ($request->isEmpty('id')) {
			$recordModel = new Settings_Inventory_Record_Model();
		} else {
			$recordModel = Settings_Inventory_Record_Model::getInstanceById($request->getInteger('id'), $type);
		}
		$fields = ['name', 'status', 'value', 'default'];
		foreach ($fields as $fieldName) {
			if ($request->has($fieldName)) {
				switch ($fieldName) {
					case 'default':
					case 'status':
						$recordModel->set($fieldName, (int) $request->getBoolean($fieldName));
						break;
					case 'name':
						$recordModel->set($fieldName, $request->getByType($fieldName, 'Text'));
						break;
					case 'value':
						$value = $request->getByType($fieldName, 'NumberInUserFormat');
						if ('Taxes' === $type && ($value < 0 || $value > 100)) {
							throw new \App\Exceptions\IllegalValue("ERR_NOT_ALLOWED_VALUE||value||{$value}", 406);
						}
						$recordModel->set($fieldName, $value);
						break;
					default:
						break;
				}
			}
		}
		$recordModel->setType($type);
		$response = new Vtiger_Response();
		try {
			$id = $recordModel->save();
			$recordModel = Settings_Inventory_Record_Model::getInstanceById($id, $type);
			$response->setResult(array_merge(['_editurl' => $recordModel->getEditUrl(), 'row_type' => \App\User::getCurrentUserModel()->getDetail('rowheight')], $recordModel->getData()));
		} catch (Throwable $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	public function checkDuplicateName(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$id = !$request->isEmpty('id') ? $request->getInteger('id') : '';
		$name = $request->getByType('name', 'Text');
		$type = $request->getByType('view', 'Standard');

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

	public function deleteInventory(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$params = $request->getMultiDimensionArray('param', [
			'id' => 'Integer',
			'view' => 'Standard'
		]);
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

	public function saveConfig(App\Request $request)
	{
		$params = $request->getMultiDimensionArray('param', [
			'view' => 'Standard',
			'param' => [
				'value' => 'Text',
				'param' => 'Standard'
			]
		]);
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
