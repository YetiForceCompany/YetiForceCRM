<?php
/**
 * SMSAPI - sms provider.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * SMSAPI - sms provider.
 */
class SMSNotifier_SMSAPI_Provider extends SMSNotifier_Basic_Provider
{
	/**
	 * Provider name.
	 *
	 * @var string
	 */
	protected $name = 'SMSAPI';

	/**
	 * Address URL.
	 *
	 * @var string
	 */
	protected $url = 'https://api.smsapi.pl/sms.do?';

	/**
	 * Encoding.
	 *
	 * @var string
	 */
	public $encoding = 'utf-8';

	/**
	 * Format.
	 *
	 * @var string
	 */
	public $format = 'json';

	/**
	 * Required fields.
	 *
	 * @return string[]
	 */
	public function getRequiredParams()
	{
		return ['from', 'encoding', 'format'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getResponse($request)
	{
		$response = \App\Json::decode($request->getBody());
		return 200 === $request->getStatusCode() && empty($response['error']);
	}

	/**
	 * Fields to edit in settings.
	 *
	 * @return \Settings_Vtiger_Field_Model[]
	 */
	public function getSettingsEditFieldsModel()
	{
		$fields = [];
		$moduleName = 'Settings:SMSNotifier';
		foreach ($this->getRequiredParams() as $name) {
			$field = ['uitype' => 16, 'column' => $name, 'name' => $name, 'displaytype' => 1, 'typeofdata' => 'V~M', 'presence' => 0, 'isEditableReadOnly' => false];
			if ('from' === $name) {
				$field['uitype'] = 1;
				$field['label'] = 'FL_SMSAPI_FROM';
				$field['fieldvalue'] = $this->has($name) ? $this->get($name) : '';
				$fields[] = $field;
			}
		}
		foreach ($fields as &$field) {
			$field = Settings_Vtiger_Field_Model::init($moduleName, $field);
		}
		return $fields;
	}
}
