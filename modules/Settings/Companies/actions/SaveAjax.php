<?php

/**
 * Companies SaveAjax action model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
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
	public function updateCompany(\App\Request $request)
	{
		if (!$request->isEmpty('record')) {
			$recordModel = Settings_Companies_Record_Model::getInstance($request->getInteger('record'));
		} else {
			$recordModel = new Settings_Companies_Record_Model();
		}
		if ($columns = Settings_Companies_Module_Model::getColumnNames()) {
			foreach ($columns as $fieldName) {
				$recordModel->set($fieldName, $request->getByType($fieldName, 'Text'));
			}
			$recordModel->save();
			$recordModel->saveCompanyLogos();
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'url' => $recordModel->getDetailViewUrl()
		]);
		$response->emit();
	}
}
