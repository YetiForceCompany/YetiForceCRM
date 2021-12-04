<?php
/**
 * Auto Assign file.
 *
 * The file is part of the paid functionality. Using the file is allowed only after purchasing a subscription.
 * File modification allowed only with the consent of the system producer.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * Auto Assign class.
 */
class AutoAssign extends Base
{
	/** Basic table name */
	const TABLE_NAME = 's_#__auto_assign';
	/** Members tables */
	const MEMBERS_TABLES = ['s_#__auto_assign_users' => 'id', 's_#__auto_assign_groups' => 'id', 's_#__auto_assign_roles' => 'id'];
	/** Round robin table name */
	const ROUND_ROBIN_TABLE = 'u_#__auto_assign_rr';

	/** Status inactive */
	const STATUS_INACTIVE = 0;
	/** Status active */
	const STATUS_ACTIVE = 1;

	/** Manual mode */
	const MODE_MANUAL = 1;
	/** Handler mode */
	const MODE_HANDLER = 2;
	/** Workflow mode */
	const MODE_WORKFLOW = 4;

	/** Load balance method */
	const METHOD_LOAD_BALANCE = 0;
	/** Round robin method */
	const METHOD_ROUND_ROBIN = 1;

	/**
	 * Get all auto assign entries for module.
	 *
	 * @param string $moduleName
	 * @param int    $mode       A bitmask of one or more of the mode flags
	 * @param int    $state
	 *
	 * @return array
	 */
	public static function getByModule(string $moduleName, int $mode = self::MODE_HANDLER | self::MODE_WORKFLOW | self::MODE_MANUAL, int $state = self::STATUS_ACTIVE): array
	{
		$query = (new \App\Db\Query())->from(self::TABLE_NAME)
			->where(['tabid' => \App\Module::getModuleId($moduleName), 'state' => $state]);
		$mods = ['or'];
		foreach ([self::MODE_MANUAL => 'gui', self::MODE_HANDLER => 'handler', self::MODE_WORKFLOW => 'workflow'] as $key => $column) {
			if ($mode & $key) {
				$mods[] = [$column => 1];
			}
		}
		$query->andWhere($mods);

		return $query->all();
	}

	/**
	 * Get all auto assign instances for module.
	 *
	 * @param string   $moduleName
	 * @param int|null $mode       A bitmask of one or more of the mode flags
	 *
	 * @return array
	 */
	public static function getInstancesByModule(string $moduleName, int $mode = null): array
	{
		$instances = [];
		foreach (self::getByModule($moduleName, $mode) as $autoAssignData) {
			$instances[$autoAssignData['id']] = self::getInstance($autoAssignData);
		}
		return $instances;
	}

	/**
	 * Get auto assign instance for record.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 * @param int|null             $mode        A bitmask of one or more of the mode flags
	 *
	 * @return self|null
	 */
	public static function getAutoAssignForRecord(\Vtiger_Record_Model $recordModel, int $mode = null): ?self
	{
		$autoAssignInstance = null;
		foreach (self::getByModule($recordModel->getModuleName(), $mode) as $autoAssignData) {
			$conditions = \App\Json::isEmpty($autoAssignData['conditions']) ? [] : \App\Json::decode($autoAssignData['conditions']);
			if (\App\Condition::checkConditions($conditions, $recordModel)) {
				$autoAssignInstance = self::getInstance($autoAssignData);
				break;
			}
		}
		return $autoAssignInstance;
	}

	/**
	 * Get auto assign instance by ID.
	 *
	 * @param int $id
	 *
	 * @return self|null
	 */
	public static function getInstanceById(int $id): ?self
	{
		$data = (new \App\Db\Query())->from(self::TABLE_NAME)->where(['id' => $id])->one();
		return $data ? (new self())->setData($data) : null;
	}

	/**
	 * Get auto assign instance by data.
	 *
	 * @param array $data
	 *
	 * @return self|null
	 */
	public static function getInstance(array $data): ?self
	{
		return $data ? (new self())->setData($data) : null;
	}

	/**
	 * Function to get the Id.
	 *
	 * @return int
	 */
	public function getId(): int
	{
		return $this->get('id');
	}

	/**
	 * Get name of auto assign instance.
	 *
	 * @param bool $encode
	 *
	 * @return string
	 */
	public function getName(bool $encode = true): string
	{
		return \App\Language::translate($this->get('subject'), 'Settings:AutomaticAssignment', false, $encode);
	}

	/**
	 * Check conditions for record.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return bool
	 */
	public function checkConditionForRecord(\Vtiger_Record_Model $recordModel): bool
	{
		$conditions = \App\Json::isEmpty($this->get('conditions')) ? [] : \App\Json::decode($this->get('conditions'));
		return \App\Condition::checkConditions($conditions, $recordModel);
	}

	/**
	 * Check if the instance is active in a given mode.
	 *
	 * @param int $mode
	 *
	 * @return bool
	 */
	public function isActive(int $mode)
	{
		switch ($mode) {
			case self::MODE_MANUAL:
				$result = !$this->isEmpty('gui');
				break;
			case self::MODE_HANDLER:
				$result = !$this->isEmpty('handler');
				break;
			case self::MODE_WORKFLOW:
				$result = !$this->isEmpty('workflow');
				break;
			default:
				$result = false;
				break;
		}
		return $result && self::STATUS_ACTIVE === (int) $this->get('state');
	}

	/**
	 * Get an automatic selected user ID.
	 *
	 * @return int
	 */
	public function getOwner(): int
	{
		switch ($this->get('method')) {
			case self::METHOD_LOAD_BALANCE:
				$owner = $this->getOwnerByLoadBalance();
				break;
			case self::METHOD_ROUND_ROBIN:
				$owner = $this->getOwnerByRoundRobin();
				break;
			default:
			$owner = null;
				break;
		}

		return $owner ? $owner : (int) $this->get('default_assign');
	}

	/**
	 * Get user ID selected using the load balanced method.
	 *
	 * In order to correctly balance the entries attribution
	 * we need ot randomize the order in which they are returned.
	 * Otherwise, when multiple users have the same amount of entries
	 * it is always the first one in the results who will be assigned to new entry.
	 *
	 * @return int
	 */
	public function getOwnerByLoadBalance(): int
	{
		return (int) $this->getQuery()->orderBy(['count' => SORT_ASC, new \yii\db\Expression('RAND()')])->scalar();
	}

	/**
	 * Get user ID selected using the round robin method.
	 *
	 * @return int
	 */
	public function getOwnerByRoundRobin(): int
	{
		$robinTable = self::ROUND_ROBIN_TABLE;
		$columnName = "{$robinTable}.datetime";

		return (int) $this->getQuery()->leftJoin($robinTable, "vtiger_users.id = {$robinTable}.user")
			->addSelect([$columnName])
			->addGroupBy($columnName)
			->orderBy([$columnName => SORT_ASC])
			->scalar();
	}

	/**
	 * Query object for users allowed for assignment.
	 *
	 * @return Db\Query
	 */
	public function getQuery(): Db\Query
	{
		$ownerFieldName = 'assigned_user_id';
		$queryGeneratorUsers = $this->getAvailableUsersQuery();

		$moduleName = \App\Module::getModuleName($this->get('tabid'));
		$queryGenerator = (new \App\QueryGenerator($moduleName));
		$queryGenerator->permissions = false;
		$conditions = \App\Json::isEmpty($this->get('record_limit_conditions')) ? [] : \App\Json::decode($this->get('record_limit_conditions'));
		$queryGenerator->setFields([$ownerFieldName])
			->setCustomColumn(['count' => new \yii\db\Expression('COUNT(*)')])
			->setConditions($conditions)
			->setGroup($ownerFieldName)
			->addNativeCondition([$queryGenerator->getColumnName($ownerFieldName) => $queryGeneratorUsers->createQuery()]);
		$subQuery = $queryGenerator->createQuery();

		$recordLimit = (int) $this->get('record_limit');
		if (0 === $recordLimit) {
			$queryGeneratorUsers->setCustomColumn(['temp_limit' => $queryGeneratorUsers->getColumnName('records_limit')]);
		} else {
			$queryGeneratorUsers->setCustomColumn(['temp_limit' => new \yii\db\Expression($recordLimit)]);
		}
		$queryGeneratorUsers->setGroup('id')->setCustomGroup(['temp_limit', 'count']);
		$query = $queryGeneratorUsers->createQuery(true);
		$query->leftJoin(['crm_data_temp_table' => $subQuery], "crm_data_temp_table.{$ownerFieldName}={$queryGeneratorUsers->getColumnName('id')}");
		$query->addSelect(['crm_data_temp_table.count']);
		$query->andHaving(['or', ['<', 'count', new \yii\db\Expression('temp_limit')], ['temp_limit' => 0], ['count' => null]]);

		return $query;
	}

	/**
	 * Query generator object of available users.
	 *
	 * @return QueryGenerator
	 */
	public function getAvailableUsersQuery(): QueryGenerator
	{
		$queryGenerator = (new \App\QueryGenerator('Users'))
			->setFields(['id'])
			->addCondition('status', 'Active', 'e')
			->addCondition('available', 1, 'e')
			->addCondition('auto_assign', 1, 'e');
		$columnName = $queryGenerator->getColumnName('id');
		$availableUsers = $this->getMembers();

		$condition = ['or'];
		foreach ($availableUsers as $member) {
			[$type, $id] = explode(':', $member);
			switch ($type) {
				case \App\PrivilegeUtil::MEMBER_TYPE_USERS:
					$condition[$type][$columnName][] = (int) $id;
					break;
				case \App\PrivilegeUtil::MEMBER_TYPE_GROUPS:
					$condition[] = [$columnName => (new \App\Db\Query())->select(['userid'])->from(["condition_{$type}_{$id}_" . \App\Layout::getUniqueId() => \App\PrivilegeUtil::getQueryToUsersByGroup((int) $id)])];
					break;
				case \App\PrivilegeUtil::MEMBER_TYPE_ROLES:
					$condition[] = [$columnName => \App\PrivilegeUtil::getQueryToUsersByRole($id)];
					break;
				case \App\PrivilegeUtil::MEMBER_TYPE_ROLE_AND_SUBORDINATES:
					$condition[] = [$columnName => \App\PrivilegeUtil::getQueryToUsersByRoleAndSubordinate($id)];
					break;
				default:
					break;
			}
		}
		if (1 === \count($condition)) {
			$condition = [$columnName => 0];
		}
		$queryGenerator->addNativeCondition($condition);

		return $queryGenerator;
	}

	/**
	 * Get members.
	 *
	 * @return array
	 */
	public function getMembers(): array
	{
		if (!$this->has('members')) {
			$queryAll = null;
			foreach (self::MEMBERS_TABLES as $tableName => $index) {
				$query = (new \App\Db\Query())
					->select(['member' => new \yii\db\Expression('CONCAT(type,\':\', member)')])
					->from($tableName)
					->where(["{$tableName}.{$index}" => $this->getId()]);
				if ($queryAll) {
					$queryAll->union($query, true);
				} else {
					$queryAll = $query;
				}
			}
			$members = $queryAll->column();
			$this->set('members', $members);
		}
		return $this->get('members');
	}

	/**
	 * Post process action.
	 *
	 * @param int $userId
	 *
	 * @return void
	 */
	public function postProcess(int $userId)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		if ($userId && self::METHOD_ROUND_ROBIN === $this->get('method')) {
			$params = ['id' => $this->getId(), 'user' => $userId];
			$isExists = (new \App\Db\Query())->from(self::ROUND_ROBIN_TABLE)->where($params)->exists();
			if ($isExists) {
				$dbCommand->update(self::ROUND_ROBIN_TABLE, ['datetime' => (new \DateTime())->format('Y-m-d H:i:s.u')], $params)->execute();
			} else {
				$params['datetime'] = (new \DateTime())->format('Y-m-d H:i:s.u');
				$dbCommand->insert(self::ROUND_ROBIN_TABLE, $params)->execute();
			}
		}
	}
}
