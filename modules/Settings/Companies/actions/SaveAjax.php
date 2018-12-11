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
		$exists = $recordModel->isCompanyDuplicated($request);
		if (!$exists) {
			$logoDetails = $recordModel->saveCompanyLogos();
			$columns = Settings_Companies_Module_Model::getColumnNames();
			if ($columns) {
				foreach ($columns as $fieldName) {
					$fieldValue = $request->getByType($fieldName, 'Text');
					if ('logo' === $fieldName) {
						if (!empty($logoDetails[$fieldName]['name'])) {
							$fieldValue = ltrim(basename(' ' . \App\Fields\File::sanitizeUploadFileName($logoDetails[$fieldName]['name'])));
						} else {
							$fieldValue = $recordModel->get($fieldName);
						}
					}
					$recordModel->set($fieldName, $fieldValue);
				}
				$recordModel->save();
			}
			$result = ['success' => true, 'url' => $recordModel->getDetailViewUrl()];
		} else {
			$result = ['success' => false, 'message' => \App\Language::translate('LBL_COMPANY_NAMES_EXIST', $request->getModule(false))];
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
