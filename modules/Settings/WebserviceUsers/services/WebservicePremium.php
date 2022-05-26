<?php

/**
 * WebservicePremium Record Model.
 *
 * @package Settings
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author  Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Settings_WebserviceUsers_WebservicePremium_Service class.
 */
class Settings_WebserviceUsers_WebservicePremium_Service extends Settings_WebserviceUsers_WebserviceStandard_Service
{
	/** {@inheritdoc} */
	public $baseTable = 'w_#__portal_user';

	/** {@inheritdoc} */
	public $editFields = [
		'server_id' => 'FL_SERVER',
		'status' => 'FL_STATUS',
		'password' => 'FL_PASSWORD',
		'type' => 'FL_TYPE',
		'crmid' => 'FL_RECORD_NAME',
		'user_id' => 'FL_USER',
		'login_method' => 'FL_LOGIN_METHOD',
		'authy_methods' => 'FL_AUTHY_METHODS',
		'istorage' => 'FL_STORAGE',
		'language' => 'FL_LANGUAGE',
	];

	/** {@inheritdoc} */
	public $listFields = [
		'server_id' => 'FL_SERVER',
		'user_name' => 'FL_LOGIN',
		'crmid' => 'FL_RECORD_NAME',
		'type' => 'FL_TYPE',
		'user_id' => 'FL_USER',
		'status' => 'FL_STATUS',
		'istorage' => 'FL_STORAGE',
		'login_method' => 'FL_LOGIN_METHOD',
		'login_time' => 'FL_LOGIN_TIME',
		'custom_params' => 'FL_CUSTOM_PARAMS',
	];

	/** @var array Columns to show on the list session. */
	public $columnsToShow = [
		'time' => 'FL_LOGIN_TIME',
		'status' => 'FL_STATUS',
		'agent' => 'LBL_USER_AGENT',
		'ip' => 'LBL_IP_ADDRESS',
		'device_id' => 'LBL_DEVICE_ID',
	];

	/** {@inheritdoc} */
	public function getFieldInstanceByName($name)
	{
		$moduleName = $this->getModule()->getName(true);
		$fieldsLabel = $this->getEditFields();
		$params = ['uitype' => 1, 'column' => $name, 'name' => $name, 'label' => $fieldsLabel[$name], 'displaytype' => 1, 'typeofdata' => 'V~M', 'presence' => 0, 'isEditableReadOnly' => false];
		switch ($name) {
			case 'crmid':
				$params['uitype'] = 10;
				$params['referenceList'] = ['Contacts'];
				$params['fieldparams'] = [
					'searchParams' => '[[["email","ny",""]]]',
				];
				break;
			case 'istorage':
				$params['uitype'] = 10;
				$params['referenceList'] = ['IStorages'];
				$params['typeofdata'] = 'V~O';
				break;
			case 'status':
				$params['uitype'] = 16;
				$params['picklistValues'] = [1 => \App\Language::translate('FL_ACTIVE'), 0 => \App\Language::translate('FL_INACTIVE')];
				break;
			case 'server_id':
				$servers = Settings_WebserviceApps_Module_Model::getActiveServers($this->getModule()->typeApi);
				$params['uitype'] = 16;
				$params['picklistValues'] = [];
				foreach ($servers as $key => $value) {
					$params['picklistValues'][$key] = $value['name'];
				}
				break;
			case 'type':
				$params['uitype'] = 16;
				$params['picklistValues'] = [];
				foreach ($this->getTypeValues() as $key => $value) {
					$params['picklistValues'][$key] = \App\Language::translate($value, $moduleName);
				}
				break;
			case 'language':
				$params['typeofdata'] = 'V~O';
				$params['uitype'] = 32;
				$params['picklistValues'] = \App\Language::getAll();
				break;
			case 'user_id':
				$params['uitype'] = 16;
				$params['picklistValues'] = \App\Fields\Owner::getInstance($moduleName)->getAccessibleUsers('', 'owner');
				break;
			case 'login_method':
				$params['uitype'] = 16;
				$params['picklistValues'] = [
					'PLL_PASSWORD' => \App\Language::translate('PLL_PASSWORD', 'Users'),
					'PLL_PASSWORD_2FA' => \App\Language::translate('PLL_PASSWORD_2FA', 'Users'),
				];
				break;
			case 'authy_methods':
				$params['uitype'] = 16;
				$params['typeofdata'] = 'V~O';
				$params['picklistValues'] = [
					'-' => \App\Language::translate('LBL_NONE'),
					'PLL_AUTHY_TOTP' => \App\Language::translate('PLL_AUTHY_TOTP', 'Users'),
				];
				break;
			case 'password':
				$params['uitype'] = 99;
				$params['typeApi'] = $this->getModule()->typeApi;
				$params['fieldparams'] = '{"validate":["pwned","config"],"auto-generate":true,"strengthMeter":true}';
				$params['maximumlength'] = '100';
				$params['typeofdata'] = 'V~O';
				if ($this->has('id')) {
					$params = null;
				}
				break;
			default: break;
		}
		return $params ? Settings_Vtiger_Field_Model::init($moduleName, $params) : null;
	}

	/** {@inheritdoc} */
	public function setDataFromRequest(App\Request $request)
	{
		foreach (array_keys($this->getEditFields()) as $field) {
			if ($request->has($field)) {
				switch ($field) {
					case 'server_id':
					case 'status':
					case 'type':
					case 'user_id':
					case 'istorage':
						$value = $request->getInteger($field);
						break;
					case 'crmid':
						$value = $request->isEmpty('crmid') ? '' : $request->getInteger('crmid');
						break;
					case 'user_name':
					case 'language':
					case 'login_method':
					case 'authy_methods':
						$value = $request->getByType($field, 'Text');
						break;
					case 'password':
						if (!$this->isNew()) {
							throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||{$field}", 406);
						}
						$value = $request->getRaw($field, null);
						parent::set($field, $value);
						break;
					default:
					throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||{$field}", 406);
						break;
				}
				$this->set($field, $this->getValueToSave($field, $value));
			}
		}
	}

	/** {@inheritdoc} */
	public function getValueToSave($key, $value)
	{
		switch ($key) {
			case 'server_id':
			case 'istorage':
			case 'status':
			case 'type':
			case 'crmid':
			case 'user_id':
				$value = (int) $value;
				break;
			case 'password':
				$value = App\Encryption::createPasswordHash($value, 'WebservicePremium');
				break;
			default: break;
		}
		return $value;
	}
}
