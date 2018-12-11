<?php

/**
 * Automatic Assignment Record Model Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_AutomaticAssignment_Record_Model extends Settings_Vtiger_Record_Model
{
	/**
	 * Raw data.
	 *
	 * @var array
	 */
	private $rawData = [];

	/**
	 * Variable determines the possibility of creating value duplicates.
	 *
	 * @var bool
	 */
	public $checkDuplicate = false;

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
	 * Function to get the Role Name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->get('rolename');
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
	 * Source module name.
	 *
	 * @return string
	 */
	public function getSourceModuleName()
	{
		return \App\Module::getModuleName($this->get('tabid'));
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
		return $this->module->baseTable;
	}

	/**
	 * Function to get table primary key.
	 *
	 * @return string
	 */
	public function getTableIndex()
	{
		return $this->module->baseIndex;
	}

	/**
	 * Function to get raw data.
	 *
	 * @return array
	 */
	public function getRawData()
	{
		return $this->rawData;
	}

	/**
	 * Function determines fields available in edition view.
	 *
	 * @return string[]
	 */
	public function getEditFields()
	{
		return ['roleid' => 'FL_MODE', 'value' => 'FL_VALUE', 'roles' => 'FL_ROLES', 'smowners' => 'FL_SMOWNERS', 'assign' => 'FL_ASSIGN', 'conditions' => 'FL_CONDITIONS'];
	}

	/**
	 * Function declare fields to edit.
	 *
	 * @return string[]
	 */
	public function getEditableFields()
	{
		return ['id', 'tabid', 'field', 'roleid', 'value', 'roles', 'smowners', 'assign', 'conditions', 'user_limit', 'active'];
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
		switch ($name) {
			case 'value':
				return Vtiger_Field_Model::getInstance($this->get('field'), Vtiger_Module_Model::getInstance($this->get('tabid')));
			case 'roles':
			case 'roleid':
				return Vtiger_Field_Model::getInstance('roleid', Vtiger_Module_Model::getInstance('Users'));
			case 'smowners':
			case 'assign':
				return Vtiger_Field_Model::getInstance('assigned_user_id', Vtiger_Module_Model::getInstance($this->get('tabid')));
			default:
				break;
		}
		return null;
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
	 * Function returns url of selected tab in edition view.
	 *
	 * @return string
	 */
	public function getEditViewTabUrl($tab)
	{
		return $this->getEditViewUrl() . '&tab=' . $tab;
	}

	/**
	 * Function changes the type of a given role.
	 *
	 * @param string $member
	 */
	public function changeRoleType($member)
	{
		$memberArr = explode(':', $member);
		if ($memberArr[0] === \App\PrivilegeUtil::MEMBER_TYPE_ROLES) {
			$memberArr[0] = \App\PrivilegeUtil::MEMBER_TYPE_ROLE_AND_SUBORDINATES;
		} else {
			$memberArr[0] = \App\PrivilegeUtil::MEMBER_TYPE_ROLES;
		}
		$roles = explode(',', $this->get('roles'));
		foreach ($roles as $key => $role) {
			if ($role === $member) {
				$roles[$key] = implode(':', $memberArr);
				break;
			}
		}
		$this->set('roles', $roles);
		$this->save();
	}

	/**
	 * Function removes given value from record.
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function deleteElement($name, $value)
	{
		$values = explode(',', $this->get($name));
		$key = array_search($value, $values);
		if ($key !== false) {
			unset($values[$key]);
		}
		$this->set($name, $values);
		$this->save();
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
		return !empty($result);
	}

	/**
	 * Function properly formats data for given field.
	 *
	 * @param string $key
	 *
	 * @return int|array
	 */
	public function getEditValue($key)
	{
		switch ($key) {
			case 'roles':
				$rows = [];
				if ($this->get($key)) {
					$value = explode(',', $this->get($key));
					foreach ($value as $index => $val) {
						$data = explode(':', $val);
						$name = \App\Language::translate(\App\PrivilegeUtil::getRoleName($data[1]));
						$rows[$index]['type'] = $data[0];
						$rows[$index]['name'] = $name;
						$rows[$index]['id'] = $val;
					}
				}

				return $rows;
			case 'tabid':
				$value = (int) $value;
				break;
			case 'smowners':
				$rows = [];
				if ($this->get($key)) {
					$value = explode(',', $this->get($key));
					foreach ($value as $index => $val) {
						$name = \App\Language::translate(\App\Fields\Owner::getLabel($val));
						$rows[$index]['type'] = \App\Fields\Owner::getType($val);
						$rows[$index]['name'] = $name;
						$rows[$index]['id'] = $val;
					}
				}

				return $rows;
			default:
				break;
		}
		return $value;
	}

	/**
	 * Function formats data for saving.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return int|string
	 */
	private function getValueToSave($key, $value)
	{
		switch ($key) {
			case 'roles':
				if (!is_array($value)) {
					$value = array_filter(explode(',', $value));
				}
				if ($this->checkDuplicate) {
					$newVal = [];
					$oldVal = [];
					foreach ($value as $val) {
						if (strpos($val, ':') !== false) {
							$valArr = explode(':', $val);
							$newVal[$valArr[1]] = $val;
						} else {
							$newVal[$val] = 'Roles:' . $val;
						}
					}
					if (isset($this->rawData[$key])) {
						$oldValue = array_filter(explode(',', $this->rawData[$key]));
						foreach ($oldValue as $val) {
							if (strpos($val, ':') !== false) {
								$valArr = explode(':', $val);
								$oldVal[$valArr[1]] = $val;
							} else {
								$oldVal[$val] = 'Roles:' . $val;
							}
						}
					}
					$value = array_unique(array_merge($newVal, $oldVal));
				}
				$value = implode(',', $value);
				break;
			case 'tabid':
			case 'user_limit':
			case 'assign':
				$value = (int) $value;
				break;
			case 'smowners':
				if (!is_array($value)) {
					$value = array_filter(explode(',', $value));
				}
				if ($this->checkDuplicate) {
					$oldValue = array_filter(explode(',', $this->rawData[$key]));
					$value = array_unique(array_merge($value, $oldValue));
				}
				$value = implode(',', $value);
				break;
			case 'conditions':
				if ($value !== $this->rawData[$key]) {
					$value = \App\Json::encode($this->transformAdvanceFilter($value));
				}
				break;
			default:
				break;
		}
		return $value;
	}

	/**
	 * Function transforms Advance filter to workflow conditions.
	 *
	 * @param array $condition
	 *
	 * @return array
	 */
	public function transformAdvanceFilter($conditions)
	{
		if (is_string($conditions)) {
			$conditions = \App\Json::decode($conditions);
		}
		$conditionResult = [];
		if (!empty($conditions)) {
			foreach ($conditions as $index => $condition) {
				$columns = $condition['columns'];
				if (!empty($columns) && is_array($columns)) {
					foreach ($columns as $column) {
						$conditionResult[] = ['fieldname' => $column['columnname'], 'operation' => $column['comparator'],
							'value' => $column['value'], 'valuetype' => $column['valuetype'] ?? '', 'joincondition' => $column['column_condition'],
							'groupjoin' => $condition['condition'] ?? '', 'groupid' => $index === 1 ? 0 : 1, ];
					}
				}
			}
		}
		return $conditionResult;
	}

	/**
	 * Function to save.
	 */
	public function save()
	{
		$db = App\Db::getInstance('admin');
		$params = [];
		$fieldsToEdit = $this->getEditableFields();
		foreach ($this->getData() as $key => $value) {
			if (!in_array($key, $fieldsToEdit)) {
				throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||' . $key, 406);
			}
			$params[$key] = $this->getValueToSave($key, $value);
		}
		if ($params && empty($this->getId())) {
			$success = $db->createCommand()->insert($this->getTable(), $params)->execute();
			if ($success) {
				$this->rawData = $params;
				$this->set('id', $db->getLastInsertID($this->getTable() . '_id_seq'));
			}
		} elseif (!empty($this->getId())) {
			$db->createCommand()->update($this->getTable(), $params, ['id' => $this->getId()])->execute();
		}
		$this->getModule()->clearCache($params);
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
				'linklabel' => 'LBL_CHANGE_RECORD_STATE',
				'linkurl' => 'javascript:Settings_AutomaticAssignment_List_Js.changeRecordState(' . $this->getId() . ', ' . (int) !$this->isActive() . ');',
				'linkicon' => 'fas fa-exchange-alt',
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => $this->getEditViewUrl(),
				'linkicon' => 'fas fa-edit',
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => 'javascript:Settings_AutomaticAssignment_List_Js.deleteById(' . $this->getId() . ')',
				'linkicon' => 'fas fa-trash-alt',
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
	public static function getInstanceById($id)
	{
		$cacheName = get_class();
		if (\App\Cache::staticHas($cacheName, $id)) {
			return \App\Cache::staticGet($cacheName, $id);
		}
		$instance = self::getCleanInstance();
		$data = (new App\Db\Query())
			->from($instance->getTable())
			->where([$instance->getTableIndex() => $id])
			->one(App\Db::getInstance('admin'));
		$instance->setData($data);
		$instance->rawData = $data;
		\App\Cache::staticSave($cacheName, $id, $instance);

		return $instance;
	}

	/**
	 * Function to get the clean instance.
	 *
	 * @return \self
	 */
	public static function getCleanInstance()
	{
		$cacheName = get_class();
		$key = 'Clean';
		if (\App\Cache::staticHas($cacheName, $key)) {
			return \App\Cache::staticGet($cacheName, $key);
		}
		$moduleInstance = Settings_Vtiger_Module_Model::getInstance('Settings:AutomaticAssignment');
		$instance = new self();
		$instance->module = $moduleInstance;
		\App\Cache::staticSave($cacheName, $key, $instance);

		return $instance;
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
			case 'field':
				$fieldInstance = $this->getFieldInstanceByName('value');

				return $fieldInstance->get('label');
			case 'tabid':
				return \App\Module::getModuleName($this->get($name));
			case 'active':
				return empty($this->get($name)) ? 'LBL_NO' : 'LBL_YES';
			case 'roleid':
				return empty($this->get($name)) ? 'LBL_SYSTEM' : \App\Language::translate(\App\PrivilegeUtil::getRoleName($this->get($name)));
			default:
				break;
		}
		return $this->get($name);
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
	 * List of  available users.
	 *
	 * @return int[]
	 */
	public function getUsers()
	{
		$users = [];
		$roles = $this->get('roles');
		if (!empty($roles)) {
			$roles = explode(',', $this->get('roles'));
			foreach ($roles as $member) {
				$users = array_merge($users, \App\PrivilegeUtil::getUserByMember($member));
			}
			$users = $this->filterUsers(array_unique($users));
		}
		if (empty($users)) {
			$smowners = $this->get('smowners') ? explode(',', $this->get('smowners')) : [];
			foreach ($smowners as $user) {
				if (\App\Fields\Owner::getType($user) !== 'Users') {
					$users = array_merge($users, \App\PrivilegeUtil::getUsersByGroup($user));
				} else {
					$users[] = $user;
				}
			}
			$users = $this->filterUsers(array_unique($users));
		}
		return $users;
	}

	/**
	 * Limit list of users to users with proper permissions.
	 *
	 * @param int[] $users
	 *
	 * @return int[]
	 */
	public function filterUsers($users)
	{
		foreach ($users as $key => $userId) {
			$userModel = \App\User::getUserModel($userId);
			if (!$userModel->isActive() || !$userModel->getDetail('available') || !$userModel->getDetail('auto_assign') || $this->getCustomConditions($userModel)) {
				unset($users[$key]);
			}
		}
		return $users;
	}

	/**
	 * Function supports custom user conditions.
	 *
	 * @param \App\User $userModel
	 *
	 * @return bool
	 */
	private function getCustomConditions($userModel)
	{
		if (!isset($this->customConditions)) {
			$userContitions = \AppConfig::module('Users', 'AUTO_ASSIGN_CONDITIONS');
			$this->customConditions = ($userContitions && isset($userContitions['modules'][$this->getSourceModuleName()])) ? $userContitions['modules'][$this->getSourceModuleName()] : [];
		}
		$result = true;
		foreach ($this->customConditions as $moduleFields => $condition) {
			switch ($condition[1]) {
				case 'like':
					$result = strpos($userModel->getDetail($condition[0]), $this->sourceRecordModel->get($moduleFields)) !== false;
					break;
				case '=':
					$result = $this->sourceRecordModel->get($moduleFields) === $userModel->getDetail($condition[0]);
					break;
				default:
					$result = true;
					break;
			}
			if (!$result) {
				break;
			}
		}
		return !$result;
	}

	/**
	 * Function returns ID of the user who has the lowest number of records.
	 *
	 * @return int
	 */
	public function getAssignUser()
	{
		$users = $this->getAvailableUsers();
		if (empty($users)) {
			return $this->getDefaultOwner();
		}
		asort($users);

		return key($users);
	}

	/**
	 * Default owner.
	 *
	 * @return int
	 */
	public function getDefaultOwner()
	{
		$owner = $this->get('assign');
		if (\App\Fields\Owner::getType($owner) === 'Users') {
			return \App\User::isExists($owner) ? $owner : 0;
		}
		return Settings_Groups_Record_Model::getInstance($owner) ? $owner : 0;
	}

	/**
	 * Function returns table of available users.
	 *
	 * @return int[]
	 */
	public function getAvailableUsers()
	{
		if (isset($this->availableUsers)) {
			return $this->availableUsers;
		}

		$this->availableUsers = array_fill_keys($this->getUsers(), 0);
		if (empty($this->availableUsers)) {
			return $this->availableUsers;
		}

		$queryGenerator = new \App\QueryGenerator($this->getSourceModuleName(), \App\User::getActiveAdminId());
		$queryGenerator->setFields(['assigned_user_id']);
		$queryGenerator->addJoin(['INNER JOIN', 'vtiger_users', 'vtiger_crmentity.smownerid = vtiger_users.id']);
		$queryGenerator->addNativeCondition(['vtiger_users.available' => 1, 'vtiger_users.auto_assign' => 1]);
		$conditions = $this->get('conditions');
		if ($conditions) {
			foreach (\App\Json::decode($conditions) as $condition) {
				$fieldName = explode(':', $condition['fieldname']);
				$queryGenerator->addCondition($fieldName[1], $condition['value'], $condition['operation'], (bool) $condition['groupjoin']);
			}
		}
		$query = $queryGenerator->createQuery();
		$count = new \yii\db\Expression('COUNT(vtiger_crmentity.crmid)');
		$query->addSelect(['c' => $count])
			->groupBy([$queryGenerator->getColumnName('assigned_user_id'), 'vtiger_users.records_limit'])
			->orderBy(['c' => SORT_ASC]);
		if ($this->get('user_limit')) {
			$access = new \yii\db\Expression('CASE WHEN ' . $count . '<=vtiger_users.records_limit OR vtiger_users.records_limit IS NULL OR vtiger_users.records_limit = 0 THEN 1 ELSE 0 END');
			$query->addSelect(['a' => $access]);
			$dataReader = $query->createCommand()->query();
			while ($row = $dataReader->read()) {
				$userId = $row['assigned_user_id'];
				if (!empty($row['a']) && isset($this->availableUsers[$userId])) {
					$this->availableUsers[$userId] = $row['c'];
				} else {
					unset($this->availableUsers[$userId]);
				}
				$dataReader->close();
			}
		} else {
			$dataReader = $query->createCommand()->query();
			while ($row = $dataReader->read()) {
				$userId = $row['assigned_user_id'];
				if (isset($this->availableUsers[$userId])) {
					$this->availableUsers[$userId] = $row['c'];
				}
			}
			$dataReader->close();
		}
		return $this->availableUsers;
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
		if (in_array($name, ['conditions', 'assign', 'value', 'roleid'])) {
			return false;
		}
		return true;
	}
}
