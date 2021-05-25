<?php

/**
 * Portal Record Model.
 *
 * @package Settings
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author  Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Settings_WebserviceUsers_Portal_Service class.
 */
class Settings_WebserviceUsers_Portal_Service extends Settings_WebserviceUsers_RestApi_Service
{
	/** {@inheritdoc} */
	public $baseTable = 'w_#__portal_user';

	/** {@inheritdoc} */
	public $editFields = [
		'server_id' => 'FL_SERVER', 'status' => 'FL_STATUS', 'password' => 'FL_PASSWORD', 'type' => 'FL_TYPE', 'language' => 'FL_LANGUAGE', 'crmid' => 'FL_RECORD_NAME', 'user_id' => 'FL_USER', 'istorage' => 'FL_STORAGE'
	];

	/** {@inheritdoc} */
	public $listFields = [
		'server_id' => 'FL_SERVER', 'user_name' => 'FL_LOGIN', 'crmid' => 'FL_RECORD_NAME', 'type' => 'FL_TYPE', 'user_id' => 'FL_USER', 'status' => 'FL_STATUS', 'istorage' => 'FL_STORAGE', 'language' => 'FL_LANGUAGE', 'login_time' => 'FL_LOGIN_TIME', 'logout_time' => 'FL_LOGOUT_TIME'
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
					'searchParams' => '[[["email","ny",""]]]'
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
			case 'password':
				$params['typeofdata'] = 'P~M';
				break;
			default:
				break;
		}
		return Settings_Vtiger_Field_Model::init($moduleName, $params);
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
						$value = $request->getByType($field, 'Text');
						break;
					case 'password':
						$value = $request->getRaw($field, null);
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
				$value = App\Encryption::getInstance()->encrypt($value);
				break;
			default:
				break;
		}
		return $value;
	}
}
