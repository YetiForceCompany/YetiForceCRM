<?php

/**
 * Record Model.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
		$previousValue = $this->changes['id'] ?? null;
		return !$this->getId() || $this->getId() === $previousValue;
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

	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return $this->get('name');
	}

	/**
	 * {@inheritdoc}
	 */
	public function init(array $data)
	{
		$this->setData($data);
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function set($key, $value)
	{
		if (($this->value[$key] ?? null) !== $value) {
			$this->changes[$key] = $value;
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
		$instance->init($data);
		\App\Cache::staticSave($cacheName, $id, $instance);

		return $instance;
	}

	/**
	 * Function to get the clean instance.
	 *
	 * @param string $type
	 *
	 * @return \self
	 */
	public static function getCleanInstance($type)
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
		return array_intersect_key($this->getData(), $this->getFieldsForSave());
	}

	/**
	 * Check if the data is correct.
	 *
	 * @return bool|string false - if everything is ok
	 */
	public function checkData()
	{
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
		if (empty($this->getId())) {
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
