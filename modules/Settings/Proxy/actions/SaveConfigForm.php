<?php
/**
 * Settings proxy save config form action file.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Sołek <a.solek@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Settings proxy save config form action class.
 */
class Settings_Proxy_SaveConfigForm_Action extends Settings_Vtiger_Basic_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$fields = Settings_Proxy_ConfigForm_Model::getFields($qualifiedModuleName);
		$field = $request->getByType('updateField');
		if (!isset($fields[$field])) {
			throw new \App\Exceptions\IllegalValue('ERR_FIELD_NOT_FOUND||' . $field);
		}
		$fieldModel = $fields[$field];
		$value = $request->getByType($field, $fieldModel->get('purifyType'));

		$configFile = new \App\ConfigFile('security');
		$configFile->set($field, $value);
		$configFile->create();

		$response = new Vtiger_Response();
		$response->setResult(['notify' => ['type' => 'success', 'text' => \App\Language::translate('LBL_CHANGES_SAVED')]]);
		$response->emit();
	}
}
