<?php
/**
 * Magento save action file.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Magento save action class.
 */
class Settings_Magento_SaveAjax_Action extends Settings_Vtiger_Save_Action
{
	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule(false);
		if ($request->isEmpty('record')) {
			$recordModel = Settings_Magento_Record_Model::getCleanInstance();
		} else {
			$recordModel = Settings_Magento_Record_Model::getInstanceById($request->getInteger('record'));
		}
		try {
			foreach ($recordModel->getModule()->getFormFields() as $fieldName => $fieldInfo) {
				$value = $request->isEmpty($fieldName) ? '' : $request->getByType($fieldName, $fieldInfo['purifyType']);
				if ('' === $value && $fieldInfo['required']) {
					throw new \App\Exceptions\IllegalValue('ERR_NO_VALUE||' . \App\Language::translate('LBL_' . \strtoupper($fieldName), $moduleName), 406);
				}
				$recordModel->set($fieldName, $value);
			}
			$recordModel->save();
			$result = ['success' => true, 'url' => $recordModel->getModule()->getDefaultUrl()];
		} catch (\App\Exceptions\AppException $e) {
			$result = ['success' => false, 'message' => $e->getDisplayMessage()];
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
