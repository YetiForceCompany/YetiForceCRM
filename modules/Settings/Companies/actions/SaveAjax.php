<?php

/**
 * Companies SaveAjax action model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_Companies_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View
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
		$recordId = $request->get('record');
		if (!empty($recordId)) {
			$recordModel = Settings_Companies_Record_Model::getInstance($recordId);
		} else {
			$recordModel = new Settings_Companies_Record_Model();
		}
		$exists = $recordModel->isCompanyDuplicated($request);
		if (!$exists) {
			$recordModel->setCompaniesNotDefault($request->get('default'));
			$logoDetails = $recordModel->saveCompanyLogos();
			$columns = Settings_Companies_Module_Model::getColumnNames();
			if ($columns) {
				if (empty(($request->get('default')))) {
					$columns = array_diff($columns, ['default']);
				}
				foreach ($columns as $fieldName) {
					$fieldValue = $request->get($fieldName);
					if ($fieldName === 'logo_login' || $fieldName === 'logo_main' || $fieldName === 'logo_mail') {
						if (!empty($logoDetails[$fieldName]['name'])) {
							$fieldValue = ltrim(basename(' ' . \App\Fields\File::sanitizeUploadFileName($logoDetails[$fieldName]['name'])));
						} else {
							$fieldValue = $recordModel->get($fieldName);
						}
					}
					if ('default' === $fieldName) {
						$fieldValue = $request->get('default');
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

	/**
	 * Validate Request.
	 *
	 * @param \App\Request $request
	 */
	public function validateRequest(\App\Request $request)
	{
		$request->validateWriteAccess();
	}
}
