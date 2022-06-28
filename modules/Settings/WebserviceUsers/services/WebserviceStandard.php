<?php

/**
 * WebserviceStandard Record Model file.
 *
 * @package Settings
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * WebserviceStandard Record Model class.
 */
class Settings_WebserviceUsers_WebserviceStandard_Service extends Settings_WebserviceUsers_Record_Model
{
	/** @var string Table name. */
	public $baseTable = 'w_#__api_user';

	/** @var string Table name. */
	public $baseIndex = 'id';

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
		'language' => 'FL_LANGUAGE',
	];

	/** {@inheritdoc} */
	public $listFields = [
		'server_id' => 'FL_SERVER',
		'user_name' => 'FL_LOGIN',
		'type' => 'FL_TYPE',
		'user_id' => 'FL_USER',
		'status' => 'FL_STATUS',
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
	];

	/** {@inheritdoc} */
	public $paramsFields = ['language', 'logout_time'];

	/** {@inheritdoc} */
	public function init(array $data)
	{
		$data['password'] = App\Encryption::getInstance()->decrypt($data['password']);
		$this->setData($data);
		return $this;
	}

	/**
	 * Function determines fields available in edition view.
	 *
	 * @param mixed $name
	 *
	 * @return string[]
	 */
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

	/**
	 * Sets data from request.
	 *
	 * @param App\Request $request
	 */
	public function setDataFromRequest(App\Request $request)
	{
		foreach (array_keys($this->getEditFields()) as $field) {
			if ($request->has($field)) {
				switch ($field) {
					case 'server_id':
					case 'status':
					case 'type':
					case 'user_id':
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

	/**
	 * Check if the data is correct.
	 *
	 * @return bool|string
	 */
	public function checkData()
	{
		$result = parent::checkData();
		$query = (new \App\Db\Query())->from($this->baseTable)->where(['server_id' => $this->get('server_id'), 'user_name' => $this->get('user_name')]);
		if ($this->getId()) {
			$query->andWhere(['<>', 'id', $this->getId()]);
		}
		return !$result && $query->exists() ? 'LBL_DUPLICATE_LOGIN' : $result;
	}

	/**
	 * Function formats data for saving.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return int|string
	 */
	public function getValueToSave($key, $value)
	{
		switch ($key) {
			case 'server_id':
			case 'status':
			case 'type':
			case 'crmid':
			case 'user_id':
				$value = (int) $value;
				break;
			case 'password':
				$value = App\Encryption::createPasswordHash($value, 'WebserviceStandard');
				break;
		}
		return $value;
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public function getDisplayValue($name)
	{
		$value = $this->get($name);
		switch ($name) {
			case 'server_id':
				$servers = Settings_WebserviceApps_Record_Model::getInstanceById($value);
				$value = $servers ? $servers->getName() : '<span class="redColor">ERROR</span>';
				break;
			case 'crmid':
			case 'istorage':
				if ($value) {
					$moduleName = \App\Record::getType($value);
					$label = \App\Record::getLabel($value) ?: '';
					$url = "index.php?module={$moduleName}&view=Detail&record={$value}";
					$value = "<a class='modCT_{$moduleName} showReferenceTooltip js-popover-tooltip--record' href='$url'>$label</a>";
				} else {
					$value = '';
				}
				break;
			case 'status':
				$value = \App\Language::translate((empty($value) ? 'FL_INACTIVE' : 'FL_ACTIVE'));
				break;
			case 'login_method':
				$value = \App\Language::translate($value, 'Users');
				break;
			case 'user_id':
				$value = \App\Fields\Owner::getLabel($value);
				break;
			case 'login_time':
			case 'logout_time':
				$value = \App\Fields\DateTime::formatToDisplay($value);
				break;
			case 'type':
				$label = \App\Language::translate($this->getTypeValues($value), $this->getModule()->getName(true));
				$value = \App\TextUtils::textTruncate($label);
				break;
			case 'custom_params':
				if ($value) {
					$params = \App\Json::decode($value);
					$value = '';
					foreach ($params as $key => $row) {
						switch ($key) {
							case 'language':
								$row = $row ? \App\Language::getLanguageLabel($row) : '';
								break;
							case 'logout_time':
							case 'invalid_login_time':
							case 'error_time':
								$row = \App\Fields\DateTime::formatToDisplay($row);
								break;
							default:
								$row = \App\Purifier::encodeHtml($row);
								break;
						}
						if (isset(Settings_WebserviceUsers_Record_Model::$customParamsLabels[$key])) {
							$value .= \App\Language::translate(Settings_WebserviceUsers_Record_Model::$customParamsLabels[$key], 'Settings:WebserviceUsers') . ": $row \n";
						}
					}
					$value = \App\Layout::truncateText($value, 50, true);
				}
				break;
			default: break;
		}
		return $value;
	}

	/** {@inheritdoc} */
	public function getRecordLinks(): array
	{
		$links = [];
		$recordLinks = [
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_HISTORY_ACTIVITY',
				'linkicon' => 'yfi yfi-login-history',
				'linkclass' => 'btn btn-sm btn-primary',
				'linkurl' => $this->getModule()->getHistoryAccessActivityUrl() . '&record=' . $this->getId(),
				'modalView' => true,
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_SESSION_RECORD',
				'linkicon' => 'fas fa-users-cog',
				'linkclass' => 'btn btn-sm btn-primary',
				'linkurl' => $this->getModule()->getSessionViewUrl() . '&record=' . $this->getId(),
				'modalView' => true,
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => $this->getModule()->getEditViewUrl() . '&record=' . $this->getId(),
				'linkicon' => 'yfi yfi-full-editing-view',
				'linkclass' => 'btn btn-sm btn-primary',
				'modalView' => true,
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => 'javascript:Settings_WebserviceUsers_List_Js.deleteById(' . $this->getId() . ');',
				'linkicon' => 'fas fa-trash-alt',
				'linkclass' => 'btn btn-sm btn-danger',
			],
		];
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
	}

	/**
	 * Type field values.
	 *
	 * @param mixed|null $value
	 *
	 * @return string|string[]
	 */
	public function getTypeValues($value = null)
	{
		$data = [
			\Api\WebservicePremium\Privilege::USER_PERMISSIONS => 'PLL_USER_PERMISSIONS',
			\Api\WebservicePremium\Privilege::ACCOUNTS_RELATED_RECORDS => 'PLL_ACCOUNTS_RELATED_RECORDS',
			\Api\WebservicePremium\Privilege::ACCOUNTS_RELATED_RECORDS_AND_LOWER_IN_HIERARCHY => 'PLL_ACCOUNTS_RELATED_RECORDS_AND_LOWER_IN_HIERARCHY',
			\Api\WebservicePremium\Privilege::ACCOUNTS_RELATED_RECORDS_IN_HIERARCHY => 'PLL_ACCOUNTS_RELATED_RECORDS_IN_HIERARCHY',
		];
		if ($value) {
			return $data[$value];
		}
		return $data;
	}

	/** {@inheritdoc} */
	public function save()
	{
		$result = parent::save();
		if ($result && $this->isNew() && \App\Config::api('enableEmailPortal', false)) {
			$this->sendEmail();
		}
	}

	/**
	 * Send mails with access.
	 *
	 * @return void
	 */
	public function sendEmail()
	{
		if (empty($this->get('crmid'))) {
			return;
		}
		$moduleName = 'Contacts';
		$recordModel = Vtiger_Record_Model::getInstanceById($this->get('crmid'), $moduleName);
		if ($recordModel->get('emailoptout')) {
			\App\Mailer::sendFromTemplate([
				'template' => 'YetiPortalRegister',
				'moduleName' => $moduleName,
				'recordId' => $this->get('crmid'),
				'to' => $this->get('user_name'),
				'password' => $this->changes['password'],
				'login' => $this->get('user_name'),
				'acceptable_url' => Settings_WebserviceApps_Record_Model::getInstanceById($this->get('server_id'))->get('url'),
			]);
		}
	}
}
