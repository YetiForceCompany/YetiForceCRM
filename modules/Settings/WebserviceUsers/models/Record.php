<?php

/**
 * Record Model.
 *
 * @package Settings.Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_WebserviceUsers_Record_Model extends Settings_Vtiger_Record_Model
{
	/**
	 * Changes.
	 *
	 * @var array
	 */
	public $changes = [];
	/**
	 * Table name.
	 *
	 * @var string
	 */
	public $baseTable = '';

	/**
	 * Table name.
	 *
	 * @var string
	 */
	public $baseIndex = '';

	/**
	 * Module Name.
	 *
	 * @var string
	 */
	public $name = 'WebserviceUsers';

	/**
	 * Edit fields.
	 *
	 * @var string[]
	 */
	public $editFields = [];

	/**
	 * List of fields displayed in list view.
	 *
	 * @var string[]
	 */
	public $listFields = [];

	/** @var array List of fields in param column. */
	public $paramsFields = [];

	/** @var array List of custom params labels. */
	public static $customParamsLabels = [
		'language' => 'FL_LANGUAGE',
		'ip' => 'FL_LAST_IP',
		'invalid_login_time' => 'FL_DATETIME_LAST_INVALID_LOGIN',
		'invalid_login' => 'FL_LAST_INVALID_LOGIN',
		'logout_time' => 'FL_LOGOUT_TIME',
		'last_error' => 'FL_LAST_ERROR',
		'error_time' => 'FL_LAST_ERROR_DATE',
		'error_method' => 'FL_LAST_ERROR_METHOD',
		'version' => 'FL_VERSION',
		'fromUrl' => 'FL_FROM_URL',
		'agent' => 'LBL_USER_AGENT',
		'deviceId' => 'LBL_DEVICE_ID',
	];

	/**
	 * Record ID.
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Check if record is new.
	 *
	 * @return int
	 */
	public function isNew()
	{
		return !$this->getId() || $this->getId() && \array_key_exists('id', $this->changes) && empty($this->changes['id']);
	}

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

	/** {@inheritdoc} */
	public function getName()
	{
		return $this->get('name');
	}

	/** {@inheritdoc} */
	public function init(array $data)
	{
		$this->setData($data);
		return $this;
	}

	/** {@inheritdoc} */
	public function set($key, $value)
	{
		if (($prev = $this->value[$key] ?? null) !== $value) {
			$this->changes[$key] = $prev;
		}
		parent::set($key, $value);
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
	 * Get user session.
	 *
	 * @param string $container
	 *
	 * @return array
	 */
	public function getUserSession(string $container): array
	{
		$dataReader = (new \App\Db\Query())->from(\Api\Core\Containers::$listTables[$container]['session'])
			->where(['user_id' => $this->getId()])
			->orderBy(['changed' => SORT_DESC])
			->limit(30)
			->createCommand()->query();
		$data = [];
		while ($row = $dataReader->read()) {
			$data[] = $this->getFormatDataSession($row);
		}
		return $data;
	}

	/**
	 * Get user session.
	 *
	 * @param string $container
	 *
	 * @return array
	 */
	public function getUserHistoryAccessActivity(string $container): array
	{
		$dataReader = (new \App\Db\Query())->from(\Api\Core\Containers::$listTables[$container]['loginHistory'])
			->where(['user_id' => $this->getId()])
			->orderBy(['id' => SORT_DESC])
			->limit(30)
			->createCommand()->query();
		$data = [];
		while ($row = $dataReader->read()) {
			$data[] = $this->getFormatLoginHistory($row);
		}
		return $data;
	}

	/**
	 * Get format data session.
	 *
	 * @param array $row
	 *
	 * @return array
	 */
	public function getFormatDataSession(array $row): array
	{
		foreach ($row as $key => $value) {
			switch ($key) {
				case 'parent_id':
					$row[$key] = $value ? \App\Record::getLabel($value) : '';
					break;
				case 'language':
					$row[$key] = $value ? \App\Language::getLanguageLabel($value) : '';
					break;
				case 'created':
				case 'changed':
					$row[$key] = \App\Fields\DateTime::formatToDisplay($value);
					break;
				case 'params':
					if ($value) {
						$params = \App\Json::decode($value);
						$value = '';
						foreach ($params as $paramsKey => $paramsValue) {
							$value .= \App\Language::translate(self::$customParamsLabels[$paramsKey] ?? $paramsKey, 'Settings:WebserviceUsers') . ": $paramsValue \n";
						}
						$row[$key] = \App\Layout::truncateText($value, 50, true);
					}
					break;
					case 'agent':
						$row[$key] = \App\Layout::truncateText($value, 50, true);
						break;
				default:
					break;
			}
			if (!\in_array($key, ['params', 'agent'])) {
				$row[$key] = App\Purifier::encodeHtml($row[$key]);
			}
		}
		return $row;
	}

	/**
	 * Get format data session.
	 *
	 * @param array $row
	 *
	 * @return array
	 */
	public function getFormatLoginHistory(array $row): array
	{
		foreach ($row as $key => $value) {
			switch ($key) {
				case 'time':
					$row[$key] = \App\Fields\DateTime::formatToDisplay($value);
					break;
				case 'status':
						$row[$key] = \App\Language::translate($value, 'Settings::' . $this->getModule()->getName());
					break;
				case 'agent':
					$row[$key] = \App\Layout::truncateText($value, 50, true);
					break;
				case 'device_id':
					$row[$key] = "<div class=\"js-popover-tooltip ml-2 mr-2 d-inline mt-2\" data-js=\"popover\" data-content=\"$value\">" . \App\TextUtils::textTruncate($value, 14) . '</div>';
					break;
				default:
					break;
			}
			if (!\in_array($key, ['device_id', 'agent'])) {
				$row[$key] = App\Purifier::encodeHtml($row[$key]);
			}
		}
		return $row;
	}

	/** {@inheritdoc} */
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
	 * Function to get the instance, given id.
	 *
	 * @param int    $id
	 * @param string $type
	 *
	 * @return \self
	 */
	public static function getInstanceById($id, $type)
	{
		$cacheName = __CLASS__;
		if (\App\Cache::staticHas($cacheName, $id)) {
			return \App\Cache::staticGet($cacheName, $id);
		}
		$instance = self::getCleanInstance($type);
		$data = (new App\Db\Query())
			->from($instance->baseTable)
			->where([$instance->baseIndex => $id])
			->one(App\Db::getInstance('webservice'));
		if (!empty($data['custom_params']) && !App\Json::isEmpty($data['custom_params'])) {
			$data['custom_params'] = \App\Json::decode($data['custom_params']);
			$data = array_merge($data, $data['custom_params']);
		} else {
			$data['custom_params'] = [];
		}
		if (!empty($data['auth'])) {
			$data['auth'] = \App\Json::decode(\App\Encryption::getInstance()->decrypt($data['auth']));
		} else {
			$data['auth'] = [];
		}
		$data['authy_methods'] = $data['auth']['authy_methods'] ?? '';
		$instance->init($data);
		\App\Cache::staticSave($cacheName, $id, $instance);
		return $instance;
	}

	/**
	 * Function to get the clean instance.
	 *
	 * @param string $type
	 *
	 * @return $this
	 */
	public static function getCleanInstance($type): self
	{
		$moduleInstance = Settings_Vtiger_Module_Model::getInstance('Settings:WebserviceUsers');
		$moduleInstance->typeApi = $type;
		$instance = $moduleInstance->getService();
		$instance->module = $moduleInstance;
		return $instance;
	}

	/**
	 * Function gives list fields for save.
	 *
	 * @return array
	 */
	public function getFieldsForSave()
	{
		return array_intersect_key($this->getEditFields(), $this->changes);
	}

	/**
	 * Function gives data for save.
	 *
	 * @return array
	 */
	public function getDataForSave()
	{
		if (empty($this->getId())) {
			$fields = $this->getEditFields();
		} else {
			$fields = $this->getFieldsForSave();
		}
		return array_intersect_key($this->getData(), $fields);
	}

	/**
	 * Check if the data is correct.
	 *
	 * @return bool|string false - if everything is ok
	 */
	public function checkData()
	{
		if (empty($this->listFields['user_name'])) {
			return false;
		}
		if ($this->isEmpty('user_name')) {
			$userName = $this->getUserName();
			if (empty($userName)) {
				return 'LBL_EMAIL_ADDRESS_NOT_FOUND';
			}
			if ((new App\Db\Query())
				->from($this->baseTable)
				->where(['user_name' => $userName])
				->exists(App\Db::getInstance('webservice'))) {
				return 'LBL_DUPLICATE_EMAIL_ADDRESS';
			}
		}
		return false;
	}

	/**
	 * Function to save.
	 *
	 * @return bool
	 */
	public function save()
	{
		$db = App\Db::getInstance('webservice');
		$table = $this->baseTable;
		$index = $this->baseIndex;
		$data = $this->getDataForSave();
		$params = $this->get('custom_params');
		foreach ($this->paramsFields as $name) {
			if (!isset($data[$name])) {
				continue;
			}
			if ('' !== $data[$name]) {
				$params[$name] = $data[$name];
			}
			unset($data[$name]);
		}
		$data['custom_params'] = $params ? \App\Json::encode($params) : null;
		if (empty($data['authy_methods']) || '-' === $data['authy_methods']) {
			$data['auth'] = '';
		} else {
			$auth = $this->get('auth') ?: [];
			$auth['authy_methods'] = $data['authy_methods'] ?? '';
			$data['auth'] = \App\Encryption::getInstance()->encrypt(\App\Json::encode($auth));
		}
		unset($data['authy_methods']);
		if (empty($this->getId())) {
			$data['user_name'] = $this->getUserName();
			$success = $db->createCommand()->insert($table, $data)->execute();
			if ($success) {
				$this->set('id', $db->getLastInsertID("{$table}_{$index}_seq"));
			}
		} else {
			$success = $db->createCommand()->update($table, $data, [$index => $this->getId()])->execute();
		}
		return $success;
	}

	/**
	 * Get user name.
	 *
	 * @return string
	 */
	public function getUserName(): string
	{
		if (!$this->isEmpty('user_name')) {
			return $this->get('user_name');
		}
		$email = '';
		if (1 !== (int) $this->get('type')) {
			try {
				$email = Vtiger_Record_Model::getInstanceById($this->get('crmid'), 'Contacts')->get('email');
			} catch (\Throwable $th) {
			}
		} else {
			$email = \App\User::getUserModel($this->get('user_id'))->getDetail('email1');
		}
		$this->set('user_name', $email);
		return $email;
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
}
