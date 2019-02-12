<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Settings_SMSNotifier_Record_Model extends Settings_Vtiger_Record_Model
{
	/**
	 * Edit fields.
	 *
	 * @var string[]
	 */
	private $editFields = ['providertype' => 'FL_PROVIDER', 'isactive' => 'FL_STATUS', 'api_key' => 'FL_API_KEY'];

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
		$moduleModel = $this->getModule();

		return $moduleModel->getCreateRecordUrl() . '&record=' . $this->getId();
	}

	/**
	 * Function to get record links.
	 *
	 * @return <Array> list of link models <Vtiger_Link_Model>
	 */
	public function getRecordLinks()
	{
		$links = [];
		$recordLinks = [
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => $this->getEditViewUrl() . '&record=' . $this->getId(),
				'linkicon' => 'fas fa-edit',
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
	public function getDisplayValue($name)
	{
		if ($name === 'isactive') {
			return empty($this->get($name)) ? 'PLL_INACTIVE' : 'PLL_ACTIVE';
		}
		return $this->get($name);
	}

	/**
	 * Function to save the record.
	 *
	 * @return bool
	 */
	public function save()
	{
		$db = \App\Db::getInstance();
		$success = false;
		if (!$this->changes) {
			return $success;
		}
		if (isset($this->changes['api_key'])) {
			$this->changes['api_key'] = App\Encryption::getInstance()->encrypt($this->changes['api_key']);
		}
		$table = $this->getModule()->getBaseTable();
		$index = $this->getModule()->getBaseIndex();
		if (empty($this->getId())) {
			$success = $db->createCommand()->insert($table, $this->changes)->execute();
			if ($success) {
				$this->set('id', $db->getLastInsertID("{$table}_{$index}_seq"));
			}
		} else {
			$success = $db->createCommand()->update($table, $this->changes, [$index => (int) $this->getId()])->execute();
		}
		$this->clearCache($this->getId());

		return $success;
	}

	/**
	 * Clear cache.
	 *
	 * @param int $id
	 */
	public function clearCache($id)
	{
		if ($id) {
			\App\Cache::staticDelete(get_class(), $id);
		}
		\App\Cache::delete('SMSNotifierConfig', 'activeProviderInstance');
	}

	/**
	 * Function to set the value for a given key.
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public function set($key, $value)
	{
		if ($key !== $this->getModule()->getBaseIndex() && ($this->value[$key] ?? null) !== $value) {
			$this->changes[$key] = $value;
		}
		$this->value[$key] = $value;

		return $this;
	}

	/**
	 * Function to get the instance, given id.
	 *
	 * @param int    $id
	 * @param string $moduleName
	 *
	 * @return \self
	 */
	public static function getInstanceById($id, $moduleName)
	{
		$cacheName = get_class();
		if (\App\Cache::staticHas($cacheName, $id)) {
			return \App\Cache::staticGet($cacheName, $id);
		}
		$instance = self::getCleanInstance($moduleName);
		$data = (new App\Db\Query())
			->from($instance->getModule()->getBaseTable())
			->where([$instance->getModule()->getBaseIndex() => $id])
			->one(App\Db::getInstance('admin'));
		$data['api_key'] = App\Encryption::getInstance()->decrypt($data['api_key']);
		$instance->setData($data);
		$instance->isNew = false;
		\App\Cache::staticSave($cacheName, $id, $instance);

		return $instance;
	}

	/**
	 * Function to get clean record instance by using moduleName.
	 *
	 * @param string $qualifiedModuleName
	 *
	 * @return \self
	 */
	public static function getCleanInstance($qualifiedModuleName)
	{
		$recordModel = new self();
		$moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
		$recordModel->isNew = true;

		return $recordModel->setModule($moduleModel);
	}

	/**
	 * Function determines fields available in edition view.
	 *
	 * @param string $name
	 *
	 * @return \Settings_Vtiger_Field_Model
	 */
	public function getFieldInstanceByName($name)
	{
		$moduleName = $this->getModule()->getName(true);
		$fieldsLabel = $this->getEditFields();
		$params = ['uitype' => 1, 'column' => $name, 'name' => $name, 'label' => $fieldsLabel[$name], 'displaytype' => 1, 'typeofdata' => 'V~M', 'presence' => 0, 'isEditableReadOnly' => false];
		switch ($name) {
			case 'providertype':
				$params['uitype'] = 16;
				$params['picklistValues'] = [];
				foreach ($this->getModule()->getAllProviders() as $provider) {
					$params['picklistValues'][$provider->getName()] = \App\Language::translate($provider->getName(), $moduleName);
				}
				break;
			case 'isactive':
				$params['uitype'] = 16;
				$params['picklistValues'] = [1 => \App\Language::translate('PLL_ACTIVE', $moduleName), 0 => \App\Language::translate('PLL_INACTIVE', $moduleName)];
				break;
			default:
				break;
		}
		return Settings_Vtiger_Field_Model::init($moduleName, $params);
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
	 * Function to get instance of provider model.
	 *
	 * @param string $providerName
	 *
	 * @return bool|\SMSNotifier_Basic_Provider
	 */
	public function getProviderInstance()
	{
		return SMSNotifier_Module_Model::getProviderInstance($this->get('providertype'));
	}
}
