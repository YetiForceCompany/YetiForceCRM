<?php

/**
 * OSSMail Save action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_OSSMail_Save_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		parent::checkPermission($request);
		if (!\App\Module::isModuleActive('OSSMail')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (Settings_ModuleManager_Library_Model::checkLibrary('roundcube')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$recordModel = Settings_OSSMail_Config_Model::getCleanIntance();
		foreach ($recordModel->getForm() as $fieldName => $fieldInfo) {
			if ($fieldInfo['required'] === 1) {
				if ($request->isEmpty($fieldName)) {
					throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
				}
			}
			if ($fieldInfo['fieldType'] === 'text') {
				$recordModel->set($fieldName, $request->getByType($fieldName, 'Text'));
			} elseif ($fieldInfo['fieldType'] === 'checkbox') {
				$recordModel->set($fieldName, $request->getBoolean($fieldName));
			} elseif ($fieldInfo['fieldType'] === 'multipicklist') {
				$recordModel->set($fieldName, $request->getArray($fieldName));
			} elseif ($fieldInfo['fieldType'] === 'int') {
				$recordModel->set($fieldName, $request->getInteger($fieldName));
			} elseif ($fieldInfo['fieldType'] === 'picklist') {
				$value = $request->getByType($fieldName, 'Alnum');
				if (!in_array($value, $fieldInfo['value'])) {
					throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
				}
				$recordModel->set($fieldName, $value);
			}
		}
		$recordModel->save();
		$result = ['success' => true, 'data' => \App\Language::translate('JS_save_config_info', 'OSSMailScanner')];
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
