<?php
/**
 * Settings layout editor webservice apps action field.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Settings layout editor webservice apps action class.
 */
class Settings_LayoutEditor_WebserviceApps_Action extends Settings_Vtiger_Index_Action
{
	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('update');
		Settings_Vtiger_Tracker_Model::addBasic('save');
	}

	/**
	 * Update active status.
	 *
	 * @param \App\Request $request
	 *
	 * @return void
	 */
	public function update(App\Request $request): void
	{
		$response = new Vtiger_Response();
		try {
			$fieldInstance = Settings_LayoutEditor_Field_Model::getInstance($request->getInteger('fieldId'));
			$uitypeModel = $fieldInstance->getUITypeModel();
			$defaultValue = '';
			if ($request->getBoolean('is_default')) {
				$list = \App\Field::getCustomListForDefaultValue($fieldInstance);
				if ($list && $request->has('customDefaultValue')) {
					$customDefaultValue = $request->getByType('customDefaultValue', \App\Purifier::ALNUM);
					if ('-' !== $customDefaultValue && isset($list[$customDefaultValue])) {
						$defaultValue = $customDefaultValue;
					}
				}
				if ('' === $defaultValue) {
					$uitypeModel->setDefaultValueFromRequest($request);
					$defaultValue = $fieldInstance->get('defaultvalue');
				}
			}
			$fieldInstance->updateWebserviceData(
				[
					'visibility' => $request->getInteger('visibility'),
					'is_default' => $request->getInteger('is_default'),
					'default_value' => $defaultValue,
				],
				$request->getInteger('wa')
			);
			$response->setResult(['success' => true, 'notify' => ['text' => \App\Language::translate('LBL_CHANGES_SAVED')], 'closeModal' => true]);
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}
}
