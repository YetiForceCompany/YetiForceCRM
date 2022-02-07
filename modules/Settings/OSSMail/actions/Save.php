<?php

/**
 * OSSMail Save action class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
	public function checkPermission(App\Request $request)
	{
		parent::checkPermission($request);
		if (!\App\Module::isModuleActive('OSSMail')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (Settings_ModuleManager_Library_Model::checkLibrary('roundcube')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$recordModel = Settings_OSSMail_Config_Model::getCleanInstance();
		$configFile = new \App\ConfigFile('module', $request->getModule(true));
		foreach ($recordModel->getForm() as $fieldName => $fieldInfo) {
			if (1 === $fieldInfo['required'] && $request->isEmpty($fieldName)) {
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
			}
			$configFile->set($fieldName, $request->getRaw($fieldName));
		}
		$configFile->create();
		\App\Db::getInstance()->createCommand()->update('roundcube_users', ['language' => \Config\Modules\OSSMail::$language])->execute();
		$result = ['success' => true, 'data' => \App\Language::translate('JS_save_config_info', 'OSSMailScanner')];
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
