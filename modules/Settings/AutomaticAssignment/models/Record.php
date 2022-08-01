<?php

/**
 * Automatic Assignment Record Model Class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_AutomaticAssignment_Record_Model extends Settings_Vtiger_Record_Model
{
	/** @var array Record changes */
	protected $changes = [];

	/**
	 * Function to get the Id.
	 *
	 * @return int Role Id
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Function to set the id of the record.
	 *
	 * @param int $value - id value
	 */
	public function setId($value)
	{
		return $this->set('id', (int) $value);
	}

	/**
	 * Function to get the Role Name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->get('subject');
	}

	/**
	 * Function to get Module instance.
	 *
	 * @return Settings_AutomaticAssignment_Module_Model
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * Set module Instance.
	 *
	 * @param Settings_AutomaticAssignment_Module_Model $moduleModel
	 *
	 * @return Settings_AutomaticAssignment_Module_Model
	 */
	public function setModule($moduleModel)
	{
		return $this->module = $moduleModel;
	}

	/**
	 * Function to get table name.
	 *
	 * @return string
	 */
	public function getTable()
	{
		return $this->getModule()->baseTable;
	}

	/**
	 * Function to get table primary key.
	 *
	 * @return string
	 */
	public function getTableIndex()
	{
		return $this->getModule()->baseIndex;
	}

	/**
	 * Function to get the Edit View Url.
	 *
	 * @return string
	 */
	public function getEditViewUrl()
	{
		return $this->getModule()->getEditViewUrl() . '&record=' . $this->getId();
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
			$result = $db->createCommand()->delete($this->getTable(), ['id' => $recordId])->execute();
		}
		$this->updateHandler();
		return !empty($result);
	}

	/**
	 * Function to save.
	 */
	public function save()
	{
		$db = App\Db::getInstance('admin');
		$transaction = $db->beginTransaction();
		try {
			$this->saveToDb();
			$this->updateHandler();
			$transaction->commit();
		} catch (\Throwable $ex) {
			$transaction->rollBack();
			\App\Log::error($ex->__toString());
			throw $ex;
		}
	}

	/**
	 * Save data to the database.
	 */
	public function saveToDb()
	{
		$db = \App\Db::getInstance('admin');
		$tablesData = $this->getId() ? array_intersect_key($this->getData(), $this->changes) : array_intersect_key($this->getData(), array_flip($this->getModule()->getEditableFields()));
		if ($tablesData) {
			$baseTable = $this->getModule()->baseTable;
			$baseTableIndex = $this->getModule()->baseIndex;
			foreach ($this->getValuesToSave($tablesData) as $tableName => $tableData) {
				if (!$this->getId() && $baseTable === $tableName) {
					$db->createCommand()->insert($tableName, $tableData)->execute();
					$this->setId((int) $db->getLastInsertID("{$baseTable}_id_seq"));
				} elseif ($baseTable === $tableName) {
					$db->createCommand()->update($tableName, $tableData, [$baseTableIndex => $this->getId()])->execute();
				} else {
					$names = $tableData['names'];
					$names[] = 'id';
					foreach ($tableData['values'] as &$values) {
						$values[] = $this->getId();
					}
					$db->createCommand()->delete($tableName, ['id' => $this->getId()])->execute();
					$db->createCommand()->batchInsert($tableName, $names, $tableData['values'])->execute();
				}
			}
		}
	}

	/**
	 * Function formats data for saving.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	private function getValuesToSave(array $data): array
	{
		if (!$this->getId()) {
			$forSave[$this->getModule()->baseTable] = [];
		}
		foreach ($data as $fieldName => $value) {
			$fieldModel = $this->getFieldInstanceByName($fieldName);
			switch ($fieldName) {
				case 'members':
					$members = $fieldModel->getEditViewDisplayValue($value);
					$tables = [
						\App\PrivilegeUtil::MEMBER_TYPE_USERS => 's_#__auto_assign_users',
						\App\PrivilegeUtil::MEMBER_TYPE_GROUPS => 's_#__auto_assign_groups',
						\App\PrivilegeUtil::MEMBER_TYPE_ROLES => 's_#__auto_assign_roles',
						\App\PrivilegeUtil::MEMBER_TYPE_ROLE_AND_SUBORDINATES => 's_#__auto_assign_roles'
					];
					foreach ($members as $member) {
						[$type, $id] = explode(':', $member);
						$forSave[$tables[$type]]['names'] = ['member', 'type'];
						$forSave[$tables[$type]]['values'][] = [$id, $type];
					}
					break;
				default:
					$forSave[$fieldModel->getTableName()][$fieldModel->getColumnName()] = $value;
					break;
			}
		}
		return $forSave;
	}

	/** {@inheritdoc} */
	public function set($key, $value)
	{
		if ($this->getId() && !\in_array($key, ['id']) && (\array_key_exists($key, $this->value) && $this->value[$key] != $value)) {
			$this->changes[$key] = $this->get($key);
		}
		return parent::set($key, $value);
	}

	/** {@inheritdoc} */
	public function getRecordLinks(): array
	{
		$links = [];
		$recordLinks = [
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => $this->getEditViewUrl(),
				'linkicon' => 'yfi yfi-full-editing-view',
				'linkclass' => 'btn btn-primary btn-sm'
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => 'javascript:Settings_AutomaticAssignment_List_Js.deleteById(' . $this->getId() . ')',
				'linkicon' => 'fas fa-trash-alt',
				'linkclass' => 'btn text-white btn-danger btn-sm'
			],
		];
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
	}

	/**
	 * Function to get the instance, given id.
	 *
	 * @param int $id
	 *
	 * @return \self
	 */
	public static function getInstanceById(int $id)
	{
		$db = App\Db::getInstance('admin');
		$instance = self::getCleanInstance();
		$baseTable = $instance->getTable();
		$baseIndex = $instance->getTableIndex();
		$data = (new App\Db\Query())
			->from($baseTable)
			->where([$baseIndex => $id])
			->one($db);
		if ($data) {
			$queryAll = null;
			$customTables = $instance->getModule()->customFieldTable;
			foreach ($customTables as $tableName => $index) {
				$query = (new App\Db\Query())
					->select(['member' => new \yii\db\Expression('CONCAT(type,\':\', member)')])
					->from($tableName)
					->where(["{$tableName}.{$index}" => $id]);
				if ($queryAll) {
					$queryAll->union($query, true);
				} else {
					$queryAll = $query;
				}
			}
			$members = $queryAll->column($db);
			$data['members'] = $instance->getFieldInstanceByName('members')->getUITypeModel()->getDBValue($members);
			$instance->setData($data);
		}

		return $instance;
	}

	/**
	 * Function to get the clean instance.
	 *
	 * @return \self
	 */
	public static function getCleanInstance()
	{
		$cacheName = __CLASS__;
		$key = 'Clean';
		if (\App\Cache::staticHas($cacheName, $key)) {
			return clone \App\Cache::staticGet($cacheName, $key);
		}
		$moduleInstance = Settings_Vtiger_Module_Model::getInstance('Settings:AutomaticAssignment');
		$instance = new self();
		$instance->module = $moduleInstance;
		\App\Cache::staticSave($cacheName, $key, clone $instance);

		return $instance;
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public function getDisplayValue(string $name)
	{
		switch ($name) {
			case 'tabid':
				$moduleName = \App\Module::getModuleName($this->get($name));
				return \App\Language::translate($moduleName, $moduleName);
			case 'state':
				$label = empty($this->get($name)) ? 'PLL_INACTIVE' : 'PLL_ACTIVE';
				return \App\Language::translate($label, $this->getModule()->getName(true));
			case 'roleid':
				return empty($this->get($name)) ? 'LBL_SYSTEM' : \App\Language::translate(\App\PrivilegeUtil::getRoleName($this->get($name)));
			default:
				break;
		}
		$fieldInstance = $this->getFieldInstanceByName($name);
		return $fieldInstance->getDisplayValue($this->get($name));
	}

	/**
	 * Function checks if record is active.
	 *
	 * @return bool
	 */
	public function isActive()
	{
		return (bool) $this->get('active');
	}

	/**
	 * Default owner.
	 *
	 * @return int
	 */
	public function getDefaultOwner()
	{
		$owner = $this->get('assign');
		if ('Users' === \App\Fields\Owner::getType($owner)) {
			return \App\User::isExists($owner) ? $owner : 0;
		}
		return Settings_Groups_Record_Model::getInstance($owner) ? $owner : 0;
	}

	/**
	 * Function defines whether given tab in edit view should be refreshed after saving.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function isRefreshTab($name)
	{
		if (\in_array($name, ['conditions', 'assign', 'value', 'roleid'])) {
			return false;
		}
		return true;
	}

	/**
	 * Function returns field instances for given name.
	 *
	 * @param string $name
	 *
	 * @return Vtiger_Field_Model
	 */
	public function getFieldInstanceByName($name)
	{
		$fieldModel = $this->getModule()->getFieldInstanceByName($name);
		if ($this->has($name)) {
			$fieldModel->set('fieldvalue', $this->get($name) ?? '');
		}
		return $fieldModel;
	}

	/**
	 * Sets data from request.
	 *
	 * @param App\Request $request
	 */
	public function setDataFromRequest(App\Request $request)
	{
		foreach ($this->getModule()->getEditableFields() as $fieldName) {
			if ($request->has($fieldName)) {
				switch ($fieldName) {
					case 'conditions':
					case 'record_limit_conditions':
						$value = \App\Json::encode(\App\Condition::getConditionsFromRequest($request->getArray($fieldName, \App\Purifier::TEXT)));
						break;
					case 'default_assign':
						$fieldModel = $this->getFieldInstanceByName($fieldName);
						$value = $request->getByType($fieldName, $fieldModel->get('purifyType'));
						$fieldModel->getUITypeModel()->validate($value, true);
						break;
					default:
						$fieldModel = $this->getFieldInstanceByName($fieldName);
						$value = $request->getByType($fieldName, $fieldModel->get('purifyType'));
						$fieldUITypeModel = $fieldModel->getUITypeModel();
						$fieldUITypeModel->validate($value, true);
						$value = $fieldModel->getDBValue($value);
						break;
				}
				$this->set($fieldName, $value);
			}
		}
	}

	public function validate()
	{
		$response = [];
		$isDuplicate = (new App\Db\Query())
			->from($this->getModule()->baseTable)
			->where(['subject' => $this->get('subject'), 'tabid' => $this->get('tabid')])
			->andWhere(['not', [$this->getModule()->baseIndex => $this->getId()]])
			->exists();
		if ($isDuplicate) {
			$response[] = [
				'result' => false,
				'hoverField' => 'subject',
				'message' => App\Language::translate('LBL_DUPLICATE', $this->getModule()->getName(true))
			];
		}
		return $response;
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
	 * Update activate/remove handler.
	 *
	 * @return void
	 */
	public function updateHandler()
	{
		$db = \App\Db::getInstance('admin');
		$handlerClass = 'Vtiger_AutoAssign_Handler';
		$tableName = $this->getModule()->baseTable;
		$modules = (new \App\Db\Query())->select(['vtiger_tab.name'])
			->from($tableName)->innerJoin('vtiger_tab', "{$tableName}.tabid=vtiger_tab.tabid")
			->where(['and', ["{$tableName}.state" => 1], ["{$tableName}.handler" => 1]])->distinct()->column($db);
		if (!$modules) {
			\App\EventHandler::deleteHandler($handlerClass);
		} else {
			$type = 'EntityBeforeSave';
			$handlers = (new \App\Db\Query())->from('vtiger_eventhandlers')
				->where(['handler_class' => $handlerClass, 'event_name' => $type])
				->indexBy('event_name')->all();
			if (isset($handlers[$type])) {
				$data = ['include_modules' => implode(',', $modules), 'is_active' => 1];
				\App\EventHandler::update($data, $handlers[$type]['eventhandler_id']);
			} else {
				\App\EventHandler::registerHandler($type, $handlerClass, implode(',', $modules), '', 1, true, 0, \App\EventHandler::SYSTEM);
			}
		}
	}
}
