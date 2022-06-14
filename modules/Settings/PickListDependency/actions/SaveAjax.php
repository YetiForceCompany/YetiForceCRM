<?php

/**
 * Settings pickList dependency save ajax action file.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Settings pickList dependency save ajax action class.
 */
class Settings_PickListDependency_SaveAjax_Action extends Settings_Vtiger_Save_Action
{
	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('save');
		$this->exposeMethod('preSaveValidation');
	}

	/**
	 * PreSave validation function.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function preSaveValidation(App\Request $request)
	{
		$recordModel = $this->getRecordModelFromRequest($request);
		$response = new Vtiger_Response();
		$response->setResult($recordModel->validate());
		$response->emit();
	}

	/**
	 * Function to get the record model based on the request parameters.
	 *
	 * @param \App\Request $request
	 *
	 * @return Settings_PickListDependency_Record_Model or Module specific Record Model instance
	 */
	protected function getRecordModelFromRequest(App\Request $request)
	{
		if ($request->isEmpty('record')) {
			$recordModel = Settings_PickListDependency_Record_Model::getCleanInstance();
			foreach (['tabid', 'source_field'] as $fieldName) {
				if ($request->has($fieldName)) {
					$recordModel->set($fieldName, $request->getByType($fieldName, $recordModel->getFieldInstanceByName($fieldName)->get('purifyType')));
				}
			}
		} else {
			$recordModel = Settings_PickListDependency_Record_Model::getInstanceById($request->getInteger('record'));
		}
		if ($request->has('conditions')) {
			$conditions = $request->getArray('conditions', \App\Purifier::TEXT, [], \App\Purifier::INTEGER);
			foreach ($conditions as &$condition) {
				$condition = \App\Json::encode(\App\Condition::getConditionsFromRequest(\App\Json::decode($condition)));
			}
			$recordModel->set('conditions', $conditions);
		}

		return $recordModel;
	}

	/**
	 * Process method.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function save(App\Request $request)
	{
		$recordModel = $this->getRecordModelFromRequest($request);
		$response = new Vtiger_Response();
		try {
			$result = $recordModel->save();
			$response->setResult(['success' => $result, 'url' => $recordModel->getListViewUrl()]);
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}
}
