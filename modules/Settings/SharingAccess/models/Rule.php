<?php
/**
 * Settings SharingAccess rule model class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */

/**
 * Sharng Access Vtiger Module Model Class.
 */
class Settings_SharingAccess_Rule_Model extends \App\Base
{
	const RULE_TYPE_GROUPS = 'GRP';
	const RULE_TYPE_ROLE = 'ROLE';
	const RULE_TYPE_ROLE_AND_SUBORDINATES = 'RS';
	const RULE_TYPE_USERS = 'US';
	const READ_ONLY_PERMISSION = 0;
	const READ_WRITE_PERMISSION = 1;

	public static $allPermissions = [
		self::READ_ONLY_PERMISSION => 'Read Only',
		self::READ_WRITE_PERMISSION => 'Read Write',
	];
	public static $ruleMemberToRelationMapping = [
		self::RULE_TYPE_GROUPS => Settings_SharingAccess_RuleMember_Model::RULE_MEMBER_TYPE_GROUPS,
		self::RULE_TYPE_ROLE => Settings_SharingAccess_RuleMember_Model::RULE_MEMBER_TYPE_ROLES,
		self::RULE_TYPE_ROLE_AND_SUBORDINATES => Settings_SharingAccess_RuleMember_Model::RULE_MEMBER_TYPE_ROLE_AND_SUBORDINATES,
		self::RULE_TYPE_USERS => Settings_SharingAccess_RuleMember_Model::RULE_MEMBER_TYPE_USERS,
	];
	public static $dataShareTableColArr = [
		self::RULE_TYPE_GROUPS => [
			self::RULE_TYPE_GROUPS => [
				'table' => 'vtiger_datashare_grp2grp',
				'source_id' => 'share_groupid',
				'target_id' => 'to_groupid',
			],
			self::RULE_TYPE_USERS => [
				'table' => 'vtiger_datashare_grp2us',
				'source_id' => 'share_groupid',
				'target_id' => 'to_userid',
			],
			self::RULE_TYPE_ROLE => [
				'table' => 'vtiger_datashare_grp2role',
				'source_id' => 'share_groupid',
				'target_id' => 'to_roleid',
			],
			self::RULE_TYPE_ROLE_AND_SUBORDINATES => [
				'table' => 'vtiger_datashare_grp2rs',
				'source_id' => 'share_groupid',
				'target_id' => 'to_roleandsubid',
			],
		],
		self::RULE_TYPE_USERS => [
			self::RULE_TYPE_GROUPS => [
				'table' => 'vtiger_datashare_us2grp',
				'source_id' => 'share_userid',
				'target_id' => 'to_groupid',
			],
			self::RULE_TYPE_USERS => [
				'table' => 'vtiger_datashare_us2us',
				'source_id' => 'share_userid',
				'target_id' => 'to_userid',
			],
			self::RULE_TYPE_ROLE => [
				'table' => 'vtiger_datashare_us2role',
				'source_id' => 'share_userid',
				'target_id' => 'to_roleid',
			],
			self::RULE_TYPE_ROLE_AND_SUBORDINATES => [
				'table' => 'vtiger_datashare_us2rs',
				'source_id' => 'share_userid',
				'target_id' => 'to_roleandsubid',
			],
		],
		self::RULE_TYPE_ROLE => [
			self::RULE_TYPE_GROUPS => [
				'table' => 'vtiger_datashare_role2group',
				'source_id' => 'share_roleid',
				'target_id' => 'to_groupid',
			],
			self::RULE_TYPE_USERS => [
				'table' => 'vtiger_datashare_role2us',
				'source_id' => 'share_roleid',
				'target_id' => 'to_userid',
			],
			self::RULE_TYPE_ROLE => [
				'table' => 'vtiger_datashare_role2role',
				'source_id' => 'share_roleid',
				'target_id' => 'to_roleid',
			],
			self::RULE_TYPE_ROLE_AND_SUBORDINATES => [
				'table' => 'vtiger_datashare_role2rs',
				'source_id' => 'share_roleid',
				'target_id' => 'to_roleandsubid',
			],
		],
		self::RULE_TYPE_ROLE_AND_SUBORDINATES => [
			self::RULE_TYPE_GROUPS => [
				'table' => 'vtiger_datashare_rs2grp',
				'source_id' => 'share_roleandsubid',
				'target_id' => 'to_groupid',
			],
			self::RULE_TYPE_USERS => [
				'table' => 'vtiger_datashare_rs2us',
				'source_id' => 'share_roleandsubid',
				'target_id' => 'to_userid',
			],
			self::RULE_TYPE_ROLE => [
				'table' => 'vtiger_datashare_rs2role',
				'source_id' => 'share_roleandsubid',
				'target_id' => 'to_roleid',
			],
			self::RULE_TYPE_ROLE_AND_SUBORDINATES => [
				'table' => 'vtiger_datashare_rs2rs',
				'source_id' => 'share_roleandsubid',
				'target_id' => 'to_roleandsubid',
			],
		],
	];

	/**
	 * Function to get the Id of the Sharing Access Rule.
	 *
	 * @return <Number> Id
	 */
	public function getId()
	{
		return $this->get('shareid');
	}

	public function setModule($moduleName)
	{
		$module = Settings_SharingAccess_Module_Model::getInstance($moduleName);
		$this->module = $module;

		return $this;
	}

	public function setModuleFromInstance($module)
	{
		$this->module = $module;

		return $this;
	}

	/**
	 * Function to get the Group Name.
	 *
	 * @return string
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * Function to get rules.
	 *
	 * @return array
	 */
	protected function getRuleComponents()
	{
		if (!isset($this->rule_details) && $this->getId()) {
			$relationTypeComponents = explode('::', $this->get('relationtype'));
			$sourceType = $relationTypeComponents[0];
			$targetType = $relationTypeComponents[1];
			$tableColumnInfo = self::$dataShareTableColArr[$sourceType][$targetType];
			$tableName = $tableColumnInfo['table'];
			$sourceColumnName = $tableColumnInfo['source_id'];
			$targetColumnName = $tableColumnInfo['target_id'];
			$row = (new App\Db\Query())->from($tableName)->where(['shareid' => $this->getId()])
				->one();
			if ($row) {
				$sourceMemberType = self::$ruleMemberToRelationMapping[$sourceType];
				$qualifiedSourceId = Settings_SharingAccess_RuleMember_Model::getQualifiedId($sourceMemberType, $row[$sourceColumnName]);
				$this->rule_details['source_member'] = Settings_SharingAccess_RuleMember_Model::getInstance($qualifiedSourceId);
				$targetMemberType = self::$ruleMemberToRelationMapping[$targetType];
				$qualifiedTargetId = Settings_SharingAccess_RuleMember_Model::getQualifiedId($targetMemberType, $row[$targetColumnName]);
				$this->rule_details['target_member'] = Settings_SharingAccess_RuleMember_Model::getInstance($qualifiedTargetId);
				$this->rule_details['permission'] = $row['permission'];
			}
		}
		return $this->rule_details;
	}

	public function getSourceMember()
	{
		if ($this->getId()) {
			$ruleComponents = $this->getRuleComponents();

			return $ruleComponents['source_member'];
		}
		return false;
	}

	public function getTargetMember()
	{
		if ($this->getId()) {
			$ruleComponents = $this->getRuleComponents();

			return $ruleComponents['target_member'];
		}
		return false;
	}

	public function getPermission()
	{
		if ($this->getId()) {
			$ruleComponents = $this->getRuleComponents();

			return $ruleComponents['permission'];
		}
		return false;
	}

	public function isReadOnly()
	{
		if ($this->getId()) {
			$permission = $this->getPermission();

			return self::READ_ONLY_PERMISSION == $permission;
		}
		return false;
	}

	public function isReadWrite()
	{
		if ($this->getId()) {
			$permission = $this->getPermission();

			return self::READ_WRITE_PERMISSION == $permission;
		}
		return false;
	}

	public function getEditViewUrl()
	{
		return '?module=SharingAccess&parent=Settings&view=IndexAjax&mode=editRule&for_module=' . $this->getModule()->getId() . '&record=' . $this->getId();
	}

	public function getDeleteActionUrl()
	{
		return '?module=SharingAccess&parent=Settings&action=IndexAjax&mode=deleteRule&for_module=' . $this->getModule()->getId() . '&record=' . $this->getId();
	}

	/**
	 * Function to get the detailViewUrl for the rule member in Sharing Access Custom Rules.
	 *
	 * @return DetailViewUrl
	 */
	public function getSourceDetailViewUrl()
	{
		$sourceMember = $this->getSourceMember()->getId();
		$sourceMemberDetails = explode(':', $sourceMember);
		if ('Groups' === $sourceMemberDetails[0]) {
			return 'index.php?parent=Settings&module=Groups&view=Detail&record=' . $sourceMemberDetails[1];
		}
		if ('Roles' === $sourceMemberDetails[0]) {
			return 'index.php?parent=Settings&module=Roles&view=Edit&record=' . $sourceMemberDetails[1];
		}
		if ('RoleAndSubordinates' === $sourceMemberDetails[0]) {
			return 'index.php?parent=Settings&module=Roles&view=Edit&record=' . $sourceMemberDetails[1];
		}
	}

	/**
	 * Function to get the detailViewUrl for the rule member in Sharing Access Custom Rules.
	 *
	 * @return DetailViewUrl
	 */
	public function getTargetDetailViewUrl()
	{
		$targetMember = $this->getTargetMember()->getId();
		$targetMemberDetails = explode(':', $targetMember);

		if ('Groups' == $targetMemberDetails[0]) {
			return 'index.php?parent=Settings&module=Groups&view=Detail&record=' . $targetMemberDetails[1];
		}
		if ('Roles' == $targetMemberDetails[0]) {
			return 'index.php?parent=Settings&module=Roles&view=Edit&record=' . $targetMemberDetails[1];
		}
		if ('RoleAndSubordinates' == $targetMemberDetails[0]) {
			return 'index.php?parent=Settings&module=Roles&view=Edit&record=' . $targetMemberDetails[1];
		}
	}

	/**
	 * Function to get the Member Name from the Rule Model.
	 *
	 * @return Name of the rule Member
	 */
	public function getSourceMemberName()
	{
		$sourceMember = $this->getSourceMember()->getId();
		$sourceMemberDetails = explode(':', $sourceMember);

		return $sourceMemberDetails[0];
	}

	/**
	 * Function to get the Member Name from the Rule Model.
	 *
	 * @return Name of the rule Member
	 */
	public function getTargetMemberName()
	{
		$targetMember = $this->getTargetMember()->getId();
		$targetMemberDetails = explode(':', $targetMember);

		return $targetMemberDetails[0];
	}

	/** {@inheritdoc} */
	public function getRecordLinks(): array
	{
		$links = [];
		$recordLinks = [
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => 'javascript:app.showModalWindow(null, "' . $this->getEditViewUrl() . '");',
				'linkicon' => 'yfi yfi-full-editing-view',
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => 'javascript:app.showModalWindow(null, "' . $this->getDeleteActionUrl() . '");',
				'linkicon' => 'fas fa-trash-alt',
			],
		];
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
	}

	public function save()
	{
		$ruleId = $this->getId();
		$db = \App\Db::getInstance();

		if (!$ruleId) {
			$db->createCommand()->insert('vtiger_datashare_module_rel', [
				'tabid' => $this->getModule()->getId(),
			])->execute();
			$ruleId = $db->getLastInsertID('vtiger_datashare_module_rel_shareid_seq');
			$this->set('shareid', $ruleId);
		} else {
			$relationTypeComponents = explode('::', $this->get('relationtype'));
			$sourceType = $relationTypeComponents[0];
			$targetType = $relationTypeComponents[1];

			$tableColumnInfo = self::$dataShareTableColArr[$sourceType][$targetType];
			$db->createCommand()->delete($tableColumnInfo['table'], ['shareid' => $ruleId])->execute();
		}

		$sourceId = $this->get('source_id');
		$sourceIdComponents = Settings_SharingAccess_RuleMember_Model::getIdComponentsFromQualifiedId($sourceId);
		$sourceType = array_search($sourceIdComponents[0], self::$ruleMemberToRelationMapping);
		$targetId = $this->get('target_id');
		$targetIdComponents = Settings_SharingAccess_RuleMember_Model::getIdComponentsFromQualifiedId($targetId);
		$targetType = array_search($targetIdComponents[0], self::$ruleMemberToRelationMapping);
		$tableColumnName = self::$dataShareTableColArr[$sourceType][$targetType];
		$tableName = $tableColumnName['table'];
		$sourceColumnName = $tableColumnName['source_id'];
		$targetColumnName = $tableColumnName['target_id'];

		$this->set('relationtype', implode('::', [$sourceType, $targetType]));

		$db->createCommand()->insert($tableName, [
			'shareid' => $ruleId,
			$sourceColumnName => $sourceIdComponents[1],
			$targetColumnName => $targetIdComponents[1],
			'permission' => $this->get('permission'),
		])->execute();

		$db->createCommand()->update('vtiger_datashare_module_rel', [
			'relationtype' => $this->get('relationtype'),
		], ['shareid' => $ruleId])->execute();

		Settings_SharingAccess_Module_Model::recalculateSharingRules();
	}

	/**
	 * Delete the rule.
	 */
	public function delete()
	{
		$dbCommand = App\Db::getInstance()->createCommand();
		$ruleId = $this->getId();
		$relationTypeComponents = explode('::', $this->get('relationtype'));
		$tableColumnInfo = self::$dataShareTableColArr[$relationTypeComponents[0]][$relationTypeComponents[1]];
		$dbCommand->delete($tableColumnInfo['table'], ['shareid' => $ruleId])->execute();
		$dbCommand->delete('vtiger_datashare_module_rel', ['shareid' => $ruleId])->execute();
		Settings_SharingAccess_Module_Model::recalculateSharingRules();
	}

	/**
	 * Function to get all the rules.
	 *
	 * @param mixed $moduleModel
	 * @param mixed $ruleId
	 *
	 * @return array - Array of Settings_Groups_Record_Model instances
	 */
	public static function getInstance($moduleModel, $ruleId)
	{
		$result = (new \App\Db\Query())->from('vtiger_datashare_module_rel')->where(['tabid' => $moduleModel->getId(), 'shareid' => $ruleId])->one();
		if ($result) {
			$ruleModel = new self();

			return $ruleModel->setData($result)->setModuleFromInstance($moduleModel);
		}
		return false;
	}

	/**
	 * Function to get all the rules.
	 *
	 * @param mixed $moduleModel
	 *
	 * @return Settings_Groups_Record_Model[]
	 */
	public static function getAllByModule($moduleModel)
	{
		$dataReader = (new App\Db\Query())->from('vtiger_datashare_module_rel')
			->where(['tabid' => $moduleModel->getId()])
			->createCommand()->query();
		$ruleModels = [];
		while ($row = $dataReader->read()) {
			$ruleModel = new self();
			$ruleModels[$row['shareid']] = $ruleModel->setData($row)->setModuleFromInstance($moduleModel);
		}
		$dataReader->close();

		return $ruleModels;
	}
}
