<?php

/**
 * Companies SaveAjax action model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_Companies_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('updateCompany');
	}

	/**
	 * Function to save company info.
	 *
	 * @param \App\Request $request
	 *
	 * @return array
	 */
	public function updateCompany(App\Request $request)
	{
		if (!$request->isEmpty('record')) {
			$recordModel = Settings_Companies_Record_Model::getInstance($request->getInteger('record'));
		} else {
			$recordModel = new Settings_Companies_Record_Model();
		}
		$response = new Vtiger_Response();
		if (!$recordModel->isCompanyDuplicated($request)) {
			$field = $recordModel->getModule()->getFormFields();
			foreach (array_keys($field) as $fieldName) {
				if ($request->has($fieldName)) {
					$uiTypeModel = $recordModel->getFieldInstanceByName($fieldName)->getUITypeModel();
					$value = $request->getByType($fieldName, 'Text');
					$uiTypeModel->validate($value, true);
					$recordModel->set($fieldName, $uiTypeModel->getDBValue($value));
				}
			}
			$recordModel->saveCompanyLogos();
			$recordModel->save();
			$response->setResult([
				'success' => true,
				'url' => $recordModel->getDetailViewUrl(),
			]);
		} else {
			$response->setResult(['success' => false, 'message' => \App\Language::translate('LBL_ENTITY_NAMES_EXIST', $request->getModule(false))]);
		}
		$response->emit();
	}
}
