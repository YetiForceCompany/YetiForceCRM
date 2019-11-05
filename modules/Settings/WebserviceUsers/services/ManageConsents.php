<?php

/**
 * Record Model.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_WebserviceUsers_ManageConsents_Service extends Settings_WebserviceUsers_Record_Model
{
	/**
	 * Table name.
	 *
	 * @var string
	 */
	public $baseTable = 'w_#__manage_consents_user';

	/**
	 * Table name.
	 *
	 * @var string
	 */
	public $baseIndex = 'id';

	/**
	 * {@inheritdoc}
	 */
	public $editFields = [
		'server_id' => 'FL_SERVER',
		'status' => 'FL_STATUS',
		'type' => 'FL_TYPE',
		'language' => 'FL_LANGUAGE',
		'user_id' => 'FL_USER'
	];

	/**
	 * {@inheritdoc}
	 */
	public $listFields = [
		'server_id' => 'FL_SERVER',
		'status' => 'FL_STATUS',
		'user_id' => 'FL_USER',
		'type' => 'FL_TYPE',
		'login_time' => 'FL_LOGIN_TIME',
		'logout_time' => 'FL_LOGOUT_TIME',
		'language' => 'FL_LANGUAGE'
	];

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
			case 'status':
				$params['uitype'] = 16;
				$params['picklistValues'] = [1 => \App\Language::translate('PLL_ACTIVE', $moduleName), 0 => \App\Language::translate('PLL_INACTIVE', $moduleName)];
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
			case 'token':
				$params['typeofdata'] = 'P~M';
				break;
			default:
				break;
		}
		return Settings_Vtiger_Field_Model::init($moduleName, $params);
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
					case 'language':
						$value = $request->getByType($field, 'Text');
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
	 * {@inheritdoc}
	 */
	public function getFieldsForSave()
	{
		$fields = $this->getEditFields();
		$fields['token'] = '';
		return $fields = array_intersect_key($fields, $this->changes);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDataForSave()
	{
		if ($this->isNew()) {
			$this->set('token', \App\Fields\Token::generateToken());
		}
		return array_intersect_key($this->getData(), $this->getFieldsForSave());
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
			case 'user_id':
				$value = (int) $value;
				break;
			default:
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
		switch ($name) {
			case 'server_id':
				$servers = Settings_WebserviceApps_Record_Model::getInstanceById($this->get($name));
				$value = $servers ? $servers->getName() : '<span class="redColor">ERROR</span>';
				break;
			case 'status':
				$value = empty($this->get($name)) ? 'PLL_INACTIVE' : 'PLL_ACTIVE';
				break;
			case 'user_id':
				$value = \App\Fields\Owner::getLabel($this->get($name));
				break;
			case 'language':
				$value = $this->get($name) ? \App\Language::getLanguageLabel($this->get($name)) : '';
				break;
			case 'type':
				$label = \App\Language::translate($this->getTypeValues($this->get($name)), $this->getModule()->getName(true));
				$value = \App\TextParser::textTruncate($label);
				break;
			default:
				$value = $this->get($name);
				break;
		}
		return $value;
	}

	/**
	 * Function to get the list view actions for the record.
	 *
	 * @return Vtiger_Link_Model[] - Associate array of Vtiger_Link_Model instances
	 */
	public function getRecordLinks()
	{
		$links = [];
		$recordLinks = [
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'FL_TOKEN',
				'linkicon' => 'fas fa-copy',
				'linkclass' => 'btn btn-sm btn-primary clipboard',
				'linkdata' => ['copy-attribute' => 'clipboard-text', 'clipboard-text' => \App\Purifier::encodeHtml($this->get('token'))]
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => $this->getModule()->getEditViewUrl() . '&record=' . $this->getId(),
				'linkicon' => 'fas fa-edit',
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
	 * @param type $value
	 *
	 * @return string
	 */
	public function getTypeValues($value = false)
	{
		$data = [
			\Api\Portal\Privilege::USER_PERMISSIONS => 'PLL_USER_PERMISSIONS',
		];
		if ($value) {
			return $data[$value];
		}
		return $data;
	}
}
