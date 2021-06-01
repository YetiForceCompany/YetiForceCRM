<?php
/**
 * Save admin access.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Settings_AdminAccess_SaveAjax_Action class.
 */
class Settings_AdminAccess_SaveAjax_Action extends Settings_Vtiger_Index_Action
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$moduleModel = Settings_Vtiger_Module_Model::getInstance($request->getModule(false));
		$response = new Vtiger_Response();
		try {
			$modules = $moduleModel->getValueFromRequest('name', $request);
			foreach ($modules as $module) {
				$recordModel = Settings_AdminAccess_Record_Model::getInstance($module, $moduleModel);
				foreach ($moduleModel->getFieldsForSave() as $fieldName) {
					$recordModel->set($fieldName, $moduleModel->getValueFromRequest($fieldName, $request));
				}
				$recordModel->save();
				unset($recordModel);
			}
			$response->setResult(true);
		} catch (Throwable $e) {
			$response->setError($e->getMessage());
		}
		$response->emit();
	}

	/**
	 * {@inheritdoc}
	 */
	public function validateRequest(App\Request $request)
	{
		$request->validateWriteAccess();
	}
}
