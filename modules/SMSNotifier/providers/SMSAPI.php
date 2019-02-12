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
	 * Response.
	 *
	 * @param Requests_Response $request
	 *
	 * @return bool
	 */
	public function getResponse(Requests_Response $request)
	{
		$response = \App\Json::decode($request->body);

		return isset($response['error']) && !empty($response['error']) ? false : true;
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
			if ($name === 'from') {
				$field['picklistValues'] = ['Eco' => 'Eco'];
				$field['label'] = 'FL_SMSAPI_FROM';
				$fields[] = $field;
			}
		}
		foreach ($fields as &$field) {
			$field = Settings_Vtiger_Field_Model::init($moduleName, $field);
		}
		return $fields;
	}
}
