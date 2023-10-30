<?php
/**
 * Companies SaveAjax action model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_Companies_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * Function to save and register company info.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \App\Exceptions\Security
	 * @throws \App\Exceptions\DbException
	 */
	public function process(App\Request $request): void
	{
		try {
			$recordModel = Settings_Companies_Record_Model::getInstance();
			$fields = $recordModel->getModule()->getFormFields();
			foreach (array_keys($fields) as $fieldName) {
				$fieldModel = $recordModel->getFieldInstanceByName($fieldName);
				if ($request->has($fieldName) && !$fieldModel->isEditableReadOnly()) {
					$value = $request->getRaw($fieldName);
					if ($value && preg_match('/[^A-Za-zÀ-ž\W\d\s]+/u', (string) $value, $mas)) {
						throw new \App\Exceptions\AppException('ERR_PLEASE_USE_LATIN_CHARACTERS');
					}

					$value = $request->getByType($fieldName, $fieldModel->get('purifyType'));
					$fieldModel->getUITypeModel()->validate($value, true);
					$recordModel->set($fieldName, $fieldModel->getDBValue($value));
				}
			}
			$recordId = $recordModel->getId();
			$recordModel->save();

			\Settings_Vtiger_Tracker_Model::addDetail($recordModel->getPreviousValue(), $recordId ? array_intersect_key($recordModel->getData(), $recordModel->getPreviousValue()) : $recordModel->getData());
			$result = ['success' => true, 'url' => $recordModel->getEditViewUrl()];
		} catch (\App\Exceptions\AppException $e) {
			$result = ['success' => false, 'message' => $e->getDisplayMessage()];
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
