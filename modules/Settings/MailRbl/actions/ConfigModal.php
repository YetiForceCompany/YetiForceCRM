<?php

/**
 * Settings MailRbl save config modal action file.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Settings MailRbl save config modal action class.
 */
class Settings_MailRbl_ConfigModal_Action extends Settings_Vtiger_Basic_Action
{
	use \App\Controller\Traits\SettingsPermission;

	/** {@inheritdoc} */
	public function process(App\Request $request): void
	{
		$fields = Settings_MailRbl_ConfigModal_Model::getFields();
		$field = $request->getByType('updateField');
		if (!isset($fields[$field])) {
			throw new \App\Exceptions\IllegalValue('ERR_FIELD_NOT_FOUND||' . $field);
		}
		$fieldModel = $fields[$field];
		$value = $request->getByType($field, $fieldModel->get('purifyType'));

		$configFile = new \App\ConfigFile('component', 'Mail');
		$configFile->set($field, $value);
		$configFile->create();

		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'notify' => [
				'type' => 'success',
				'title' => App\Language::translate('LBL_CHANGES_SAVED'),
			],
		]);
		$response->emit();
	}
}
