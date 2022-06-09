<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Settings_SMSNotifier_Record_Model extends Settings_Vtiger_Record_Model
{
	/** @var array Record changes */
	protected $changes = [];

	/**
	 * Edit fields.
	 *
	 * @var string[]
	 */
	private $editFields = ['name', 'isactive'];

	/**
	 * Function to get Id of this record instance.
	 *
	 * @return <Integer> Id
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Function to get Name of this record instance.
	 *
	 * @return string Name
	 */
	public function getName()
	{
		return '';
	}

	/**
	 * Function to get module of this record instance.
	 *
	 * @return Settings_SMSNotifier_Module_Model $moduleModel
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * Function to set module instance to this record instance.
	 *
	 * @param Settings_SMSNotifier_Module_Model $moduleModel
	 *
	 * @return Settings_SMSNotifier_Record_Model this record
	 */
	public function setModule($moduleModel)
	{
		$this->module = $moduleModel;

		return $this;
	}

	/**
	 * Function to get Edit view url.
	 *
	 * @return string Url
	 */
	public function getEditViewUrl()
	{
		return \App\Integrations\SMSProvider::getProviderByName($this->get('providertype'))->getEditViewUrl() . '&record=' . $this->getId();
	}

	/** {@inheritdoc} */
	public function getRecordLinks(): array
	{
		$links = [];
		$recordLinks = [
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => $this->getEditViewUrl() . '&record=' . $this->getId(),
				'linkicon' => 'yfi yfi-full-editing-view',
				'linkclass' => 'btn btn-sm btn-primary',
				'modalView' => true,
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => 'javascript:Settings_Vtiger_List_Js.deleteById(' . $this->getId() . ');',
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
	 * Function to getDisplay value of every field.
	 *
	 * @param string $name field name
	 *
	 * @return mixed
	 */
	public function getDisplayValue(string $name)
	{
		if ('isactive' === $name) {
			$moduleName = $this->getModule()->getName();
			return empty($this->get($name)) ? \App\Language::translate('FL_INACTIVE', "Settings:$moduleName") : \App\Language::translate('FL_ACTIVE');
		}
		return $this->get($name);
	}

	/**
	 * Function to save.
	 */
	public function save()
	{
		$result = false;
		$db = App\Db::getInstance('admin');
		$transaction = $db->beginTransaction();
		try {
			$this->saveToDb();
			$transaction->commit();
			$result = true;
		} catch (\Throwable $ex) {
			$transaction->rollBack();
			\App\Log::error($ex->__toString());
			throw $ex;
		}
		$this->clearCache($this->getId());
		return $result;
	}

	/**
	 * Save data to the database.
	 */
	public function saveToDb()
	{
		$db = \App\Db::getInstance('admin');
		$fields = array_flip(['providertype', 'isactive', 'api_key', 'parameters', 'name']);
		$tablesData = $this->getId() ? array_intersect_key($this->getData(), $this->changes, $fields) : array_intersect_key($this->getData(), $fields);
		if ($tablesData) {
			$baseTable = $this->getModule()->baseTable;
			$baseTableIndex = $this->getModule()->baseIndex;
			if ($this->getId()) {
				$db->createCommand()->update($baseTable, $tablesData, [$baseTableIndex => (int) $this->getId()])->execute();
			} else {
				$db->createCommand()->insert($baseTable, $tablesData)->execute();
				$this->set('id', $db->getLastInsertID("{$baseTable}_{$baseTableIndex}_seq"));
			}
			if (!empty($tablesData['isactive'])) {
				$db->createCommand()->update($baseTable, ['isactive' => 0], ['<>', $baseTableIndex, (int) $this->getId()])->execute();
			}
		}
	}

	/**
	 * Get pervious value by field.
	 *
	 * @param string $fieldName
	 *
	 * @return mixed
	 */
	public function getPreviousValue(string $fieldName = '')
	{
		return $fieldName ? ($this->changes[$fieldName] ?? null) : $this->changes;
	}

	/**
	 * Sets data from request.
	 *
	 * @param App\Request $request
	 */
	public function setDataFromRequest(App\Request $request)
	{
		foreach ($this->getEditFields() as $fieldName => $fieldModel) {
			if ($request->has($fieldName)) {
				$value = $request->isEmpty($fieldName) && !$fieldModel->isMandatory() ? '' : $request->getByType($fieldName, $fieldModel->get('purifyType'));
				if ('api_key' === $fieldName) {
					$value = App\Encryption::getInstance()->encrypt($value);
				}
				$fieldModel->getUITypeModel()->validate($value, true);
				$value = $fieldModel->getUITypeModel()->getDBValue($value);

				if (\in_array($fieldName, ['id', 'providertype', 'isactive', 'api_key', 'name'])) {
					$this->set($fieldName, $value);
				} else {
					$parameters = $this->getParameters();
					$parameters[$fieldName] = $value;
					$this->set('parameters', \App\Json::encode($parameters));
				}
			}
		}
	}

	/**
	 * Clear cache.
	 *
	 * @param int $id
	 */
	public function clearCache($id)
	{
		\App\Cache::staticDelete(__CLASS__, $id);
	}

	/**
	 * Function to set the value for a given key.
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public function set($key, $value)
	{
		if ($this->getId() && !\in_array($key, ['id']) && (\array_key_exists($key, $this->value) && $this->value[$key] != $value) && !\array_key_exists($key, $this->changes)) {
			$this->changes[$key] = $this->get($key);
		}
		return parent::set($key, $value);
	}

	/**
	 * Data anonymization.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function anonymize(array $data): array
	{
		foreach ($data as $key => &$value) {
			if ('api_key' === $key || 'pwd' === $key) {
				$value = '****';
			}
		}
		return $data;
	}

	/**
	 * Function to get the instance, given id.
	 *
	 * @param int $id
	 *
	 * @return \self
	 */
	public static function getInstanceById($id)
	{
		$cacheName = __CLASS__;
		if (\App\Cache::staticHas($cacheName, $id)) {
			return \App\Cache::staticGet($cacheName, $id);
		}
		$instance = self::getCleanInstance();
		$data = (new App\Db\Query())
			->from($instance->getModule()->getBaseTable())
			->where([$instance->getModule()->getBaseIndex() => $id])
			->one(\App\Db::getInstance('admin'));
		$instance->setData($data);
		$instance->isNew = false;
		\App\Cache::staticSave($cacheName, $id, $instance);

		return $instance;
	}

	/**
	 * Function to get clean record instance by using moduleName.
	 *
	 * @param string $qualifiedModuleName
	 * @param string $provider
	 *
	 * @return \self
	 */
	public static function getCleanInstance(?string $provider = null)
	{
		$recordModel = new self();
		$moduleModel = Settings_Vtiger_Module_Model::getInstance('Settings:SMSNotifier');
		$recordModel->isNew = true;
		$recordModel->set('providertype', $provider);

		return $recordModel->setModule($moduleModel);
	}

	/**
	 * Function determines fields available in edition view.
	 *
	 * @param string $name
	 *
	 * @return \Vtiger_Field_Model
	 */
	public function getFieldInstanceByName($name)
	{
		$moduleName = $this->getModule()->getName(true);
		$params = ['uitype' => 1, 'column' => $name, 'name' => $name, 'displaytype' => 1, 'typeofdata' => 'V~M', 'presence' => 0, 'isEditableReadOnly' => false];
		switch ($name) {
			case 'providertype':
				$params['uitype'] = 16;
				$params['picklistValues'] = [];
				$params['label'] = 'FL_PROVIDER';
				$params['displaytype'] = 2;
				$params['purifyType'] = \App\Purifier::STANDARD;
				$params['fieldvalue'] = $this->getValueByField($name);
				foreach (\App\Integrations\SMSProvider::getProviders() as $provider) {
					$params['picklistValues'][$provider->getName()] = \App\Language::translate($provider->getName(), $moduleName);
				}
				break;
			case 'isactive':
				$params['uitype'] = 16;
				$params['label'] = 'FL_STATUS';
				$params['purifyType'] = \App\Purifier::INTEGER;
				$params['fieldvalue'] = $this->getValueByField($name);
				$params['picklistValues'] = [1 => \App\Language::translate('FL_ACTIVE'), 0 => \App\Language::translate('FL_INACTIVE')];
				break;
			case 'name':
				$params['uitype'] = 1;
				$params['label'] = 'FL_NAME';
				$params['purifyType'] = \App\Purifier::TEXT;
				$params['fieldvalue'] = $this->getValueByField($name);
				$params['maximumlength'] = 50;
				break;
			default:
				break;
		}

		return Settings_Vtiger_Field_Model::init($moduleName, $params);
	}

	/**
	 * Function determines fields available in edition view.
	 *
	 * @return Vtiger_Field_Model[]
	 */
	public function getEditFields()
	{
		$fields = [];
		foreach ($this->editFields as $fieldName) {
			$fields[$fieldName] = $this->getFieldInstanceByName($fieldName);
		}
		$provider = \App\Integrations\SMSProvider::getProviderByName($this->get('providertype'));
		foreach ($provider->getEditFields() as $fieldName => $fieldModel) {
			$fieldModel->set('fieldvalue', $this->getValueByField($fieldName));
			$fields[$fieldName] = $fieldModel;
		}

		return $fields;
	}

	/**
	 * Get parameters.
	 *
	 * @return array
	 */
	public function getParameters(): array
	{
		return $this->get('parameters') ? \App\Json::decode($this->get('parameters')) : [];
	}

	/**
	 * Get parameter value by name.
	 *
	 * @param string $fieldName
	 *
	 * @return string
	 */
	public function getParameter(string $fieldName): string
	{
		return $this->getParameters()[$fieldName] ?? '';
	}

	/**
	 * Get value by name.
	 *
	 * @param string $fieldName
	 *
	 * @return mixed
	 */
	public function getValueByField(string $fieldName)
	{
		return \array_key_exists($fieldName, $this->value) ? $this->value[$fieldName] : $this->getParameter($fieldName);
	}

	/**
	 * Function removes record.
	 *
	 * @return bool
	 */
	public function delete()
	{
		$db = App\Db::getInstance('admin');
		$recordId = $this->getId();
		if ($recordId) {
			$table = $this->getModule()->getBaseTable();
			$index = $this->getModule()->getBaseIndex();
			$result = $db->createCommand()->delete($table, [$index => $recordId])->execute();
			$this->clearCache($recordId);
		}
		return !empty($result);
	}

	/**
	 * Get webservice users.
	 *
	 * @return array
	 */
	public function getServiveUsers(): array
	{
		return (new \App\Db\Query())->from('w_#__sms_user')->where(['status' => 1])->all();
	}
}
