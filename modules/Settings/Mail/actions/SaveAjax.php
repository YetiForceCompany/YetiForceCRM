<?php

/**
 * Settings mail SaveAjax action class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Mail_SaveAjax_Action extends Settings_Vtiger_Save_Action
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('acceptanceRecord');
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \ReflectionException
	 */
	public function process(App\Request $request)
	{
		if ($mode = $request->getMode()) {
			$this->invokeExposedMethod($mode, $request);
		} else {
			$this->updateConfig($request);
		}
	}

	/**
	 * Update configuration.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function updateConfig(App\Request $request)
	{
		$type = $request->getByType('type', \App\Purifier::STANDARD);
		try {
			$configModel = Settings_Mail_Config_Model::getInstance($type);
			foreach ($configModel->getFields() as $fieldName => $fieldModel) {
				if ($request->has($fieldName)) {
					$purifyType = $fieldModel->get('purifyType');
					$value = \is_array($purifyType) ? $request->getArray($fieldName, current($purifyType)) : $request->getByType($fieldName, $purifyType);
					$fieldUITypeModel = $fieldModel->getUITypeModel();
					$fieldUITypeModel->validate($value, true);
					$value = $fieldModel->getDBValue($value);
					$configModel->set($fieldName, $value);
				}
			}
			$configModel->save();
			\Settings_Vtiger_Tracker_Model::addDetail($configModel->getPreviousValue(), array_intersect_key($configModel->getData(), $configModel->getPreviousValue()));
			$result = ['notify' => ['type' => 'success', 'text' => \App\Language::translate('LBL_CHANGES_SAVED')]];
		} catch (\App\Exceptions\AppException $e) {
			$result = ['notify' => ['type' => 'error', 'text' => $e->getDisplayMessage()]];
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Action to accept mail.
	 *
	 * @param \App\Request $request
	 */
	public function acceptanceRecord(App\Request $request)
	{
		$result = Settings_Mail_Config_Model::acceptanceRecord($request->getInteger('record'));
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => (bool) $result,
			'message' => \App\Language::translate('LBL_RECORD_ACCEPTED', $request->getModule(false)),
		]);
		$response->emit();
	}
}
