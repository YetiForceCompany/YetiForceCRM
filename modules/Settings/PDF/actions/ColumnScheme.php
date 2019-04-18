<?php

/**
 * SaveColumnScheme Action Class for PDF Settings.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Settings_PDF_ColumnScheme_Action extends Settings_Vtiger_Index_Action
{
	public function __construct()
	{
		$this->exposeMethod('save');
	}

	/**
	 * Save column scheme.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \yii\db\Exception
	 */
	public function save(App\Request $request)
	{
		$moduleName = $request->get('target', 'String');
		$records = $request->getArray('records', 'Integer');
		$columns = $request->getArray('columns', 'String');

		foreach ($records as $crmId) {
			Vtiger_PDF_Model::saveColumnsForRecord($crmId, $moduleName, $columns);
		}

		$output = [
			'message' => \App\Language::translate('LBL_SCHEME_SAVED', 'Settings:PDF'),
			'records' => $records
		];
		$response = new Vtiger_Response();
		$response->setResult($output);
		$response->emit();
	}
}
