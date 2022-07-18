<?php
/**
 * MailIntegration SaveConfigForm action file.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * MailIntegration SaveConfigForm action class.
 */
class Settings_MailIntegration_SaveConfigForm_Action extends Settings_Vtiger_Basic_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$fields = Settings_MailIntegration_ConfigForm_Model::getFields($request->getModule(false));
		$field = $request->getByType('updateField');
		if (!isset($fields[$field])) {
			throw new \App\Exceptions\IllegalValue('ERR_ILLEGAL_VALUE');
		}
		$fieldModel = $fields[$field];
		$value = $fieldModel->get('isArray') ? $request->getArray($field, $fieldModel->get('purifyType')) : $request->getByType($field, $fieldModel->get('purifyType'));
		$fieldModel->getUITypeModel()->validate($value, true);
		if ('outlookUrls' === $field) {
			$oldValue = \Config\Modules\MailIntegration::$outlookUrls;
			$toRemove = array_diff($oldValue, $value);
		}

		$configFile = new \App\ConfigFile('module', 'MailIntegration');
		$configFile->set($field, $value);
		$configFile->create();

		$security = new \App\ConfigFile('security');
		if ('outlookUrls' === $field) {
			$updateValue = array_unique(array_merge((\Config\Security::$allowedFrameDomains), $value));
			if (isset($toRemove)) {
				foreach ($toRemove as $value) {
					if (false !== ($key = array_search($value, $updateValue))) {
						unset($updateValue[$key]);
					}
				}
			}
			$security->set('allowedFrameDomains', array_values($updateValue));
		}
		$security->create();

		$response = new Vtiger_Response();
		$response->setResult(['notify' => ['type' => 'success', 'text' => \App\Language::translate('LBL_CHANGES_SAVED')]]);
		$response->emit();
	}
}
