<?php
/**
 * Settings admin access save config action file.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Sołek <a.solek@yetiforce.com>
 */
/**
 * Settings admin access save config action class.
 */
class Settings_AdminAccess_SaveConfigForm_Action extends Settings_Vtiger_Basic_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$response = new Vtiger_Response();
		$qualifiedModuleName = $request->getModule(false);
		$fields = Settings_AdminAccess_Module_Model::getFields($qualifiedModuleName);
		$field = $request->getByType('updateField');
		if (!isset($fields[$field])) {
			throw new \App\Exceptions\IllegalValue('ERR_ILLEGAL_VALUE');
		}
		$fieldModel = $fields[$field];
		$value = $request->getByType($field, $fieldModel->get('purifyType'));
		$configFile = new \App\ConfigFile('security');
		$configFile->set($field, $value);
		$configFile->create();
		$response->setResult(['notify' => ['type' => 'success', 'text' => \App\Language::translate('LBL_CHANGES_SAVED')]]);
		$response->emit();
	}
}
