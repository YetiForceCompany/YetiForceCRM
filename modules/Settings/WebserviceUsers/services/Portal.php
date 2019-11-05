<?php

/**
 * Portal Record Model.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Settings_WebserviceUsers_Portal_Service class.
 */
class Settings_WebserviceUsers_Portal_Service extends Settings_WebserviceUsers_Record_Model
{
	/**
	 * Table name.
	 *
	 * @var string
	 */
	public $baseTable = 'w_#__portal_user';

	/**
	 * Table name.
	 *
	 * @var string
	 */
	public $baseIndex = 'id';

	/**
	 * Edit fields.
	 *
	 * @var string[]
	 */
	private $editFields = [
		'server_id' => 'FL_SERVER', 'status' => 'FL_STATUS', 'user_name' => 'FL_LOGIN', 'password_t' => 'FL_PASSWORD', 'type' => 'FL_TYPE', 'language' => 'FL_LANGUAGE', 'crmid' => 'FL_RECORD_NAME', 'user_id' => 'FL_USER', 'istorage' => 'FL_STORAGE'
	];
	/**
	 * List of fields displayed in list view.
	 *
	 * @var string[]
	 */
	public $listFields = [
		'server_id' => 'FL_SERVER', 'status' => 'FL_STATUS', 'user_name' => 'FL_LOGIN', 'type' => 'FL_TYPE', 'login_time' => 'FL_LOGIN_TIME', 'logout_time' => 'FL_LOGOUT_TIME', 'language' => 'FL_LANGUAGE', 'crmid' => 'FL_RECORD_NAME', 'user_id' => 'FL_USER'
	];

	/**
	 * Function to get Module instance.
	 *
	 * @return Settings_WebserviceUsers_Module_Model
	 */
	public function getModule()
	{
		if (!$this->module) {
			$this->module = Settings_Vtiger_Module_Model::getInstance('Settings:WebserviceUsers');
		}
		return $this->module;
	}

	/**
	 * Function to set Module instance.
	 *
	 * @param Settings_WebserviceUsers_Module_Model $moduleModel
	 *
	 * @return $this
	 */
	public function setModule($moduleModel)
	{
		$this->module = $moduleModel;

		return $this;
	}

	/**
	 * Function determines fields available in edition view.
	 *
	 * @return string[]
	 */
	public function getEditFields()
	{
		return $this->editFields;
	}

	/**
	 * {@inheritdoc}
	 */
	public function init(array $data)
	{
		$data['password_t'] = App\Encryption::getInstance()->decrypt($data['password_t']);
		$this->setData($data);
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getListFields(): array
	{
		if (!isset($this->listFieldModels)) {
			$fieldObjects = [];
			foreach ($this->listFields as $fieldName => $fieldLabel) {
				$fieldObjects[$fieldName] = new \App\Base(['name' => $fieldName, 'label' => $fieldLabel]);
			}
			$this->listFieldModels = $fieldObjects;
		}
		return $this->listFieldModels;
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
				break;
			case 'istorage':
				$params['uitype'] = 10;
				$params['referenceList'] = ['IStorages'];
				break;
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
			case 'password_t':
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
					case 'password_t':
						$value = $request->getRaw($field, null);
						break;
					default:
					throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||{$field}", 406);
						break;
				}
			}
			$this->set($field, $value);
		}
	}

	/**
	 * Function to validate.
	 *
	 * @return bool
	 */
	public function validate()
	{
		$query = (new \App\Db\Query())->from($this->baseTable)->where(['server_id' => $this->get('server_id'), 'user_name' => $this->get('user_name')]);
		if ($this->getId()) {
			$query->andWhere(['<>', 'id', $this->getId()]);
		}
		if ($query->exists()) {
			throw new \App\Exceptions\IllegalValue('ERR_DUPLICATE_LOGIN', 406);
		}
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
			case 'password_t':
				$value = App\Encryption::getInstance()->encrypt($value);
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
				return $servers ? $servers->getName() : '<span class="redColor">ERROR</span>';
			case 'crmid':
				return $this->get($name) ? \App\Record::getLabel($this->get($name)) : '';
			case 'status':
				return empty($this->get($name)) ? 'PLL_INACTIVE' : 'PLL_ACTIVE';
			case 'user_id':
				return \App\Fields\Owner::getLabel($this->get($name));
			case 'language':
				return $this->get($name) ? \App\Language::getLanguageLabel($this->get($name)) : '';
			case 'type':
				$label = \App\Language::translate($this->getTypeValues($this->get($name)), $this->getModule()->getName(true));

				return \App\TextParser::textTruncate($label);
			default:
				break;
		}
		return $this->get($name);
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
				'linklabel' => 'FL_PASSWORD',
				'linkicon' => 'fas fa-copy',
				'linkclass' => 'btn btn-sm btn-primary clipboard',
				'linkdata' => ['copy-attribute' => 'clipboard-text', 'clipboard-text' => \App\Purifier::encodeHtml(App\Encryption::getInstance()->decrypt($this->get('password_t')))]
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
			\Api\Portal\Privilege::ACCOUNTS_RELATED_RECORDS => 'PLL_ACCOUNTS_RELATED_RECORDS',
			\Api\Portal\Privilege::ACCOUNTS_RELATED_RECORDS_AND_LOWER_IN_HIERARCHY => 'PLL_ACCOUNTS_RELATED_RECORDS_AND_LOWER_IN_HIERARCHY',
			\Api\Portal\Privilege::ACCOUNTS_RELATED_RECORDS_IN_HIERARCHY => 'PLL_ACCOUNTS_RELATED_RECORDS_IN_HIERARCHY',
		];
		if ($value) {
			return $data[$value];
		}
		return $data;
	}

	/**
	 * Function removes record.
	 *
	 * @return bool
	 */
	public function delete()
	{
		$db = App\Db::getInstance('webservice');
		$result = false;
		if ($recordId = $this->getId()) {
			$result = (bool) $db->createCommand()->delete($this->baseTable, [$this->baseIndex => $recordId])->execute();
		}
		return $result;
	}

	/**
	 * {@inheritdoc}
	 */
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
			$emailsFields = $recordModel->getModule()->getFieldsByType('email');
			$addressEmail = '';
			foreach ($emailsFields as $fieldModel) {
				if (!$recordModel->isEmpty($fieldModel->getFieldName())) {
					$addressEmail = $recordModel->get($fieldModel->getFieldName());
					break;
				}
			}
			if (!empty($addressEmail)) {
				\App\Mailer::sendFromTemplate([
					'template' => 'YetiPortalRegister',
					'moduleName' => $moduleName,
					'recordId' => $this->get('crmid'),
					'to' => $addressEmail,
					'password' => $this->get('password_t'),
					'login' => $this->get('user_name'),
					'acceptable_url' => Settings_WebserviceApps_Record_Model::getInstanceById($this->get('server_id'))->get('acceptable_url')
				]);
			}
		}
	}
}
