<?php
/**
 * Batch method file.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * Batch method class.
 */
class AutoAssign extends Base
{
	/** Table name */
	const TABLE_NAME = 's_#__auto_assign';
	/** Table name */
	const MEMBERS_TABLES = ['s_#__auto_assign_users' => 'id', 's_#__auto_assign_groups' => 'id', 's_#__auto_assign_roles' => 'id'];
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

	public static function getByModule(string $moduleName, int $mode = self::MODE_HANDLER | self::MODE_WORKFLOW | self::MODE_MANUAL, int $state = self::STATUS_ACTIVE): array
	{
		$query = (new \App\Db\Query())->from(self::TABLE_NAME)
			->where(['tabid' => \App\Module::getModuleId($moduleName), 'state' => $state]);
		if ($mode & self::MODE_MANUAL) {
			$query->andWhere(['gui' => 1]);
		}
		if ($mode & self::MODE_HANDLER) {
			$query->andWhere(['handler' => 1]);
		}
		if ($mode & self::MODE_WORKFLOW) {
			$query->andWhere(['workflow' => 1]);
		}

		return $query->all();
	}

	public static function getInstancesByModule(string $moduleName, int $mode = null): array
	{
		$instances = [];
		foreach (self::getByModule($moduleName, $mode) as $autoAssignData) {
			$instances[$autoAssignData['id']] = self::getInstance($autoAssignData);
		}
		return $instances;
	}

	public static function getAutoAssignUser(\Vtiger_Record_Model $recordModel, int $mode = null): ?int
	{
		$autoAssignUserId = null;
		foreach (self::getByModule($recordModel->getModuleName(), $mode) as $autoAssignData) {
			$conditions = \App\Json::isEmpty($autoAssignData['conditions']) ? [] : \App\Json::decode($autoAssignData['conditions']);
			if (\App\Condition::checkConditions($conditions, $recordModel)) {
				$assignRecord = self::getInstance($autoAssignData);
				$autoAssignUserId = $assignRecord->getOwner();
			}
		}
		return $autoAssignUserId;
	}

	public static function getInstanceById(int $id): ?self
	{
		$data = (new \App\Db\Query())->from(self::TABLE_NAME)->where(['id' => $id])->one();
		return $data ? (new self())->setData($data) : null;
	}

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

	public function getName(bool $encode = true)
	{
		return \App\Language::translate($this->get('subject'), 'Settings:AutomaticAssignment', false, $encode);
	}

	public function checkConditionForRecord(\Vtiger_Record_Model $recordModel)
	{
		$conditions = \App\Json::isEmpty($this->get('conditions')) ? [] : \App\Json::decode($this->get('conditions'));
		return \App\Condition::checkConditions($conditions, $recordModel);
	}

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

	public function getOwner(): int
	{
		switch ($this->get('method')) {
			case self::METHOD_LOAD_BALANCE:
				$owner = $this->getOwnerByLoadBalance();
				break;
			case self::METHOD_ROUND_ROBIN:
				$owner = '';
				break;
			default:
			$owner = null;
				break;
		}

		return $owner ? $owner : (int) $this->get('default_assign');
	}

	public function getOwnerByLoadBalance(): int
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
		$query->orderBy(['count' => SORT_ASC, new \yii\db\Expression('RAND()')]);

		return (int) $query->scalar();
	}

	public function getAvailableUsersQuery()
	{
		$queryGenerator = (new \App\QueryGenerator('Users'))
			->setFields(['id'])
			->addCondition('status', 'Active', 'e')
			->addCondition('available', '1', 'e')
			->addCondition('auto_assign', '1', 'e');
		$columnName = $queryGenerator->getColumnName('id');
		$availableUsers = $this->getAvailableUsers();

		$condition = ['or'];
		foreach ($availableUsers as $member) {
			[$type, $id] = explode(':', $member);
			switch ($type) {
				case \App\PrivilegeUtil::MEMBER_TYPE_USERS:
					$condition[] = [$columnName => $id];
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

	public function getAvailableUsers()
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
}
