<?php
/**
 * Settings Meeting Services model class.
 *
 * @package   Settings.Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Settings_AdminAccess_Record_Model class.
 */
class Settings_AdminAccess_Record_Model extends Settings_Vtiger_Record_Model
{
	/**
	 * Changes value.
	 *
	 * @var array
	 */
	private $changes = [];

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
	 * Record name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->get('name');
	}

	/**
	 * Gets instance.
	 *
	 * @param int|string                        $module
	 * @param Settings_Vtiger_Module_Model|null $moduleModel
	 *
	 * @return self
	 */
	public static function getInstance($module, $moduleModel = null)
	{
		$instance = new self();
		if (null === $moduleModel) {
			$moduleModel = $instance->getModule();
		}
		$where = [$moduleModel->getBaseIndex() => $module];
		if (!is_numeric($module)) {
			$where = ['name' => $module];
		}
		$instance->setModule($moduleModel);
		$data = (new App\Db\Query())
			->from($moduleModel->getBaseTable())
			->where($where)
			->one(App\Db::getInstance('admin'));
		if ($data) {
			$data['user'] = (new \App\Db\Query())->from(\App\Security\AdminAccess::ACCESS_TABLE_NAME)->select(['user'])->where(['module_id' => $data['id']])->column();
		}
		$instance->setData($data);
		return $instance;
	}

	/**
	 * Function to set module instance to this record instance.
	 *
	 * @param Settings_Vtiger_Module_Model $moduleModel
	 *
	 * @return $this
	 */
	public function setModule($moduleModel)
	{
		$this->module = $moduleModel;
		return $this;
	}

	/**
	 * Function to save.
	 */
	public function save()
	{
		$db = \App\Db::getInstance('admin');
		$transaction = $db->beginTransaction();
		try {
			$dbCommand = $db->createCommand();
			foreach ($this->getValuesForSave() as $tableName => $tableData) {
				if (\App\Security\AdminAccess::ACCESS_TABLE_NAME === $tableName) {
					$insertData = [];
					foreach ($tableData['user'] as $value) {
						$insertData[] = [$this->getId(), $value];
					}
					$dbCommand->delete($tableName, ['module_id' => $this->getId()])->execute();
					$dbCommand->batchInsert($tableName, ['module_id', 'user'], $insertData)->execute();
				} else {
					$dbCommand->update($tableName, $tableData, ['id' => $this->getId()])->execute();
				}
			}
			$this->clearCache();
			$transaction->commit();
		} catch (\Throwable $ex) {
			$transaction->rollBack();
			throw $ex;
		}
	}

	/**
	 * Prepare value to save.
	 *
	 * @return array
	 */
	public function getValuesForSave()
	{
		$forSave = [];
		$moduleModel = $this->getModule();
		$saveFields = array_intersect($moduleModel->getFieldsForSave(), array_keys($this->changes));
		foreach ($saveFields as $fieldName) {
			$fieldModel = $moduleModel->getFieldInstanceByName($fieldName);
			$value = $this->get($fieldName);
			$forSave[$fieldModel->getTableName()][$fieldModel->getColumnName()] = $value;
		}
		return $forSave;
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
			$this->changes[$key] = $this->get($key);
		}
		$this->value[$key] = $value;

		return $this;
	}

	/**
	 * Function to get Module instance.
	 *
	 * @return Settings_Vtiger_Module_Model
	 */
	public function getModule()
	{
		if (!isset($this->module)) {
			$this->module = Settings_Vtiger_Module_Model::getInstance('Settings:AdminAccess');
		}
		return $this->module;
	}

	/**
	 * Clear cache.
	 *
	 * @return void
	 */
	public function clearCache(): void
	{
		$previous = $this->changes['user'] ?? [];
		$users = array_merge($this->get('user'), $previous);
		foreach (array_unique($users) as $userId) {
			\App\Cache::delete('AdminPermittedModulesByUser', $userId);
		}
		\App\Cache::delete('AdminActiveModules', '');
	}
}
