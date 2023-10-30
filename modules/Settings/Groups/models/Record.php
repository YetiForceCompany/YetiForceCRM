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

/**
 * Roles Record Model Class.
 */
class Settings_Groups_Record_Model extends Settings_Vtiger_Record_Model
{
	/** @var array Record changes */
	protected $changes = [];

	/**
	 * Function to get the Id.
	 *
	 * @return int Group Id
	 */
	public function getId()
	{
		return (int) $this->get('groupid');
	}

	/**
	 * Function to set the Id.
	 *
	 * @param int $id Group Id
	 *
	 * @return $this instance
	 */
	public function setId($id)
	{
		$this->set('groupid', $id);
		return $this;
	}

	/**
	 * Function to get the Group Name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->get('groupname');
	}

	/**
	 * Function to get the Edit View Url for the Group.
	 *
	 * @return string
	 */
	public function getEditViewUrl()
	{
		return '?module=Groups&parent=Settings&view=Edit&record=' . $this->getId();
	}

	/**
	 * Function to get the Delete Action Url for the current group.
	 *
	 * @return string
	 */
	public function getDeleteActionUrl()
	{
		return 'index.php?module=Groups&parent=Settings&view=DeleteAjax&record=' . $this->getId();
	}

	/**
	 * Function to get the Detail Url for the current group.
	 *
	 * @return string
	 */
	public function getDetailViewUrl()
	{
		return '?module=Groups&parent=Settings&view=Detail&record=' . $this->getId();
	}

	/**
	 * Function to get Module instance.
	 *
	 * @return Settings_Groups_Module_Model
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * Set module Instance.
	 *
	 * @param Settings_Groups_Module_Model $moduleModel
	 *
	 * @return $this
	 */
	public function setModule($moduleModel)
	{
		$this->module = $moduleModel;
		return $this;
	}

	/** {@inheritdoc} */
	public function set($key, $value)
	{
		if ($this->getId() && !\in_array($key, ['id']) && (\array_key_exists($key, $this->value) && $this->value[$key] !== $value)) {
			$this->changes[$key] = $this->get($key);
		}
		return parent::set($key, $value);
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
	 * Function to get all the members of the groups.
	 *
	 * @return array
	 */
	public function getMembers(): array
	{
		$members = [];
		foreach ($this->getFieldInstanceByName('members')->getEditViewDisplayValue($this->get('members') ?? '') as $member) {
			[$type] = explode(':', $member, 2);
			$memberInstance = new Settings_Groups_Member_Model();
			$memberInstance->set('id', $member);
			$members[$type][] = $memberInstance;
		}
		return $members;
	}

	/**
	 * Gets member ID.
	 *
	 * @return string
	 */
	public function getMemberId()
	{
		return Settings_Groups_Member_Model::getQualifiedId(Settings_Groups_Member_Model::MEMBER_TYPE_GROUPS, $this->getId());
	}

	/**
	 * Function to get the Modules.
	 *
	 * @return $this
	 */
	public function setModules()
	{
		if ($this->isEmpty('modules')) {
			$modules = [];
			if ($this->getId()) {
				$modules = (new App\Db\Query())->select(['vtiger_group2modules.tabid'])->from('vtiger_group2modules')->where(['vtiger_group2modules.groupid' => $this->getId()])
					->column();
			}
			parent::set('modules', $this->getFieldInstanceByName('modules')->getDBValue($modules));
		}

		return $this;
	}

	/**
	 * Function to save.
	 */
	public function save()
	{
		$db = App\Db::getInstance('admin');
		$transaction = $db->beginTransaction();
		try {
			if ($errorLabel = $this->validate()) {
				throw new \App\Exceptions\AppException($errorLabel);
			}
			$userIds = array_unique(array_merge(
				$this->getUsersList($this->getPreviousValue('members') ?? ''),
				$this->getLeaderUsers($this->getPreviousValue('parentid'))
			));
			$this->saveToDb();
			$transaction->commit();
		} catch (\Throwable $ex) {
			$transaction->rollBack();
			\App\Log::error($ex->__toString());
			throw $ex;
		}
		\App\Cache::clear();
		$userIds = array_unique(array_merge($userIds,
			$this->getUsersList($this->get('members') ?? ''),
			$this->getLeaderUsers($this->get('parentid'))
		));
		$this->recalculate($userIds);
	}

	/**
	 * Get leader users.
	 *
	 * @param int|null $leader
	 *
	 * @return array
	 */
	public function getLeaderUsers(?int $leader): array
	{
		$users = [];
		if ($leader) {
			if ('Users' === \App\Fields\Owner::getType($leader)) {
				$users[] = $leader;
			} else {
				$users = \App\PrivilegeUtil::getUsersByGroup($leader);
			}
		}
		return $users;
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
					$this->setId((int) $tableData[$baseTableIndex]);
				} elseif ($baseTable === $tableName) {
					$db->createCommand()->update($tableName, $tableData, [$baseTableIndex => $this->getId()])->execute();
				} else {
					$db->createCommand()->delete($tableName, ['groupid' => $this->getId()])->execute();
					if ($names = $tableData['names'] ?? []) {
						$values = $tableData['values'] ?? [];
						foreach ($values as &$value) {
							$value[] = $this->getId();
						}
						$db->createCommand()->batchInsert($tableName, $names, $values)->execute();
					}
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
		$forSave = [];
		if (!$this->getId()) {
			$forSave[$this->getModule()->baseTable][$this->getModule()->baseIndex] = \App\Db::getInstance('admin')->getUniqueId('vtiger_users');
		}
		foreach ($data as $fieldName => $value) {
			$fieldModel = $this->getFieldInstanceByName($fieldName);
			switch ($fieldName) {
				case 'members':
					$members = $fieldModel->getEditViewDisplayValue($value);
					$tables = [
						\App\PrivilegeUtil::MEMBER_TYPE_USERS => ['table' => 'vtiger_users2group', 'memberColumn' => 'userid', 'groupColumn' => 'groupid'],
						\App\PrivilegeUtil::MEMBER_TYPE_GROUPS => ['table' => 'vtiger_group2grouprel', 'memberColumn' => 'containsgroupid', 'groupColumn' => 'groupid'],
						\App\PrivilegeUtil::MEMBER_TYPE_ROLES => ['table' => 'vtiger_group2role', 'memberColumn' => 'roleid', 'groupColumn' => 'groupid'],
						\App\PrivilegeUtil::MEMBER_TYPE_ROLE_AND_SUBORDINATES => ['table' => 'vtiger_group2rs', 'memberColumn' => 'roleandsubid', 'groupColumn' => 'groupid']
					];
					$forSave += array_fill_keys(array_column($tables, 'table'), []);
					foreach ($members as $member) {
						[$type, $memberId] = explode(':', $member);
						$tableName = $tables[$type]['table'];
						$memberColumn = $tables[$type]['memberColumn'];
						$groupColumn = $tables[$type]['groupColumn'];

						$forSave[$tableName]['names'] = [$memberColumn, $groupColumn];
						$forSave[$tableName]['values'][] = [$memberId];
					}
					break;
				case 'modules':
					$modules = $fieldModel->getEditViewDisplayValue($value);
					$tableName = 'vtiger_group2modules';
					$forSave[$tableName] = [];
					foreach ($modules as $tabId) {
						$forSave[$tableName]['names'] = ['tabid', 'groupid'];
						$forSave[$tableName]['values'][] = [$tabId];
					}
					break;
				default:
					$forSave[$fieldModel->getTableName()][$fieldModel->getColumnName()] = $value;
					break;
			}
		}

		return $forSave;
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
					case 'parentid':
						$fieldModel = $this->getFieldInstanceByName($fieldName);
						$value = $request->getByType($fieldName, $fieldModel->get('purifyType'));
						$fieldUITypeModel = $fieldModel->getUITypeModel();
						$fieldUITypeModel->validate($value, true);
						if ($value && ($ownerList = $fieldUITypeModel->getOwnerList($this)) && !isset($ownerList['LBL_USERS'][$value]) && !isset($ownerList['LBL_GROUPS'][$value])) {
							$value = 0;
						}
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
	 * Function to recalculate user privileges files.
	 *
	 * @param array $userIdsList
	 */
	public function recalculate(array $userIdsList)
	{
		$php_max_execution_time = \App\Config::main('php_max_execution_time');
		set_time_limit($php_max_execution_time);

		foreach ($userIdsList as $userId) {
			\App\UserPrivilegesFile::createUserPrivilegesfile($userId);
		}
	}

	/**
	 * Function to get all users related to this group.
	 *
	 * @param string $members
	 *
	 * @return array
	 */
	public function getUsersList(string $members): array
	{
		$userIdsList = [];
		if ($members) {
			$fieldModel = $this->getFieldInstanceByName('members');
			$userIdsList = \App\PrivilegeUtil::getQueryToUsersByMembers($fieldModel->getEditViewValue($members))->column();
		}

		return $userIdsList;
	}

	/**
	 * TransferOwnership.
	 *
	 * @param Settings_Groups_Record_Model|Users_Record_Model $transferToGroup
	 */
	protected function transferOwnership($transferToGroup)
	{
		$groupId = $this->getId();
		$transferGroupId = $transferToGroup->getId();

		App\Db::getInstance()->createCommand()->update('vtiger_crmentity', ['smownerid' => $transferGroupId], ['smownerid' => $groupId])->execute();
		App\Fields\Owner::transferOwnership($groupId, $transferGroupId);
	}

	/**
	 * Function to delete the group.
	 *
	 * @param Settings_Groups_Record_Model $transferToGroup
	 */
	public function delete($transferToGroup)
	{
		$db = App\Db::getInstance();
		$groupId = $this->getId();
		$eventHandler = new App\EventHandler();
		$eventHandler->setParams(['groupId' => $groupId, 'transferToGroup' => $transferToGroup]);
		$eventHandler->trigger('GroupBeforeDelete');
		$this->transferOwnership($transferToGroup);
		\App\PrivilegeUtil::deleteRelatedSharingRules($groupId, 'Groups');
		$db->createCommand()->delete('vtiger_groups', ['groupid' => $groupId])->execute();
		\App\Cache::clear();
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
				'linkclass' => 'btn-sm btn-primary'
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => "javascript:Settings_Vtiger_List_Js.triggerDelete(event,'" . $this->getDeleteActionUrl() . "')",
				'linkicon' => 'fas fa-trash-alt',
				'linkclass' => 'btn-sm btn-danger'
			],
		];
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
	}

	/**
	 * Function to get all the groups.
	 *
	 * @return <Array> - Array of Settings_Groups_Record_Model instances
	 */
	public static function getAll()
	{
		$dataReader = (new App\Db\Query())->from('vtiger_groups')->createCommand()->query();
		$groups = [];
		while ($row = $dataReader->read()) {
			$group = new self();
			$group->setData($row);
			$groups[$group->getId()] = $group;
		}
		$dataReader->close();

		return $groups;
	}

	/**
	 * Function to get the clean instance.
	 *
	 * @return \self
	 */
	public static function getCleanInstance(): self
	{
		$cacheName = __CLASS__;
		$key = 'Clean';
		if (\App\Cache::staticHas($cacheName, $key)) {
			return \App\Cache::staticGet($cacheName, $key);
		}
		$moduleInstance = Settings_Vtiger_Module_Model::getInstance('Settings:Groups');
		$instance = new self();
		$instance->module = $moduleInstance;
		\App\Cache::staticSave($cacheName, $key, $instance);

		return $instance;
	}

	/**
	 * Function to get the instance of Group model, given group id or name.
	 *
	 * @param int|string $value
	 *
	 * @return Settings_Groups_Record_Model instance, if exists. Null otherwise
	 */
	public static function getInstance($value)
	{
		$instance = null;
		if (vtlib\Utils::isNumber($value)) {
			$dataReader = (new App\Db\Query())->from('vtiger_groups')->where(['groupid' => $value])->createCommand()->query();
		} else {
			$dataReader = (new App\Db\Query())->from('vtiger_groups')->where(['groupname' => $value])->createCommand()->query();
		}
		if ($dataReader->count() > 0) {
			$instance = self::getCleanInstance();
			$data = $dataReader->read();
			$data['members'] = $instance->getFieldInstanceByName('members')->getDBValue(Settings_Groups_Member_Model::getAllByTypeForGroup($data['groupid']));
			$instance->setData($data)->setModules();
		}
		$dataReader->close();

		return $instance;
	}

	/**
	 * Data validation.
	 *
	 * @return string|null
	 */
	public function validate(): ?string
	{
		$error = null;
		if ($this->checkDuplicate()) {
			$error = self::ERROR_DUPLICATE;
		} else {
			$error = $this->checkLoop();
		}
		return $error ? self::GROUP_ERRORS[$error] : $error;
	}

	/**
	 * Check duplicate.
	 *
	 * @return bool
	 */
	public function checkDuplicate(): bool
	{
		$query = new App\Db\Query();
		$query->from('vtiger_groups')->where(['groupname' => $this->get('groupname')]);
		if ($this->getId()) {
			$query->andWhere(['<>', 'groupid', $this->getId()]);
		}

		return $query->exists();
	}

	/**
	 * Get elements by member type.
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	public function getMembersByType(string $type): array
	{
		$fieldModel = $this->getFieldInstanceByName('members');
		$members = $fieldModel->getEditViewValue($this->get($fieldModel->getName()));
		$needle = $type . ':';
		$length = \strlen($needle);
		foreach ($members as $key => $member) {
			if (0 === strncmp($member, $needle, $length)) {
				$members[$key] = substr($member, $length);
			} else {
				unset($members[$key]);
			}
		}

		return $members;
	}

	/** @var int Error ID - The limit of allowed group nests has been exceeded */
	public const ERROR_LOOP_LIMIT = 1;
	/** @var int Error ID - Indefinite loop */
	public const ERROR_LOOP_INF = 2;
	/** @var int Error ID - Duplicate record */
	public const ERROR_DUPLICATE = 3;
	/** @var array Labels by error ID */
	public const GROUP_ERRORS = [
		self::ERROR_LOOP_LIMIT => 'LBL_ALLOWED_GROUP_NESTS_EXCEEDED',
		self::ERROR_LOOP_INF => 'LBL_INDEFINITE_LOOP',
		self::ERROR_DUPLICATE => 'LBL_GROUP_DUPLICATE',
	];

	/**
	 * Check group correlations.
	 *
	 * @return int|null
	 */
	public function checkLoop(): ?int
	{
		$error = null;
		$groupsDown = $allGroups = $this->getMembersByType(\App\PrivilegeUtil::MEMBER_TYPE_GROUPS);
		if (!$groupsDown) {
			return $error;
		}
		$i = 2;
		$groupsUp = [];
		$max = \App\PrivilegeUtil::GROUP_LOOP_LIMIT;
		if ($this->getId()) {
			$allGroups[] = $this->getId();
			$groupsUp[] = $this->getId();
		}
		while ($i <= $max) {
			if ($groupsDown) {
				$groupsDown = (new App\Db\Query())->select(['containsgroupid'])->from('vtiger_group2grouprel')->where(['groupid' => $groupsDown])->column();
			}
			if ($groupsUp) {
				$groupsUp = (new App\Db\Query())->select(['groupid'])->from('vtiger_group2grouprel')->where(['containsgroupid' => $groupsUp])->column();
			}
			if ($groupsUp && $groupsDown) {
				++$i;
			}
			if ($i >= $max) {
				$error = self::ERROR_LOOP_LIMIT;
				break;
			}
			if (!$groupsDown && !$groupsUp) {
				break;
			}
			$allGroups = array_merge($allGroups, $groupsDown, $groupsUp);
			if (\count($allGroups) !== \count(array_flip($allGroups))) {
				$error = self::ERROR_LOOP_INF;
				break;
			}
			++$i;
		}

		return $error;
	}
}
