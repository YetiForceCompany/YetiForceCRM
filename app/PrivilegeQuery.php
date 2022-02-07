<?php

namespace App;

/**
 * Privilege File basic class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class PrivilegeQuery
{
	/**
	 * Interpreter for privilege.
	 *
	 * @var string
	 */
	private static $interpreter;

	/**
	 * Sets interpreter.
	 *
	 * @param string $className
	 *
	 * @return void
	 */
	public static function setPermissionInterpreter(string $className)
	{
		static::$interpreter = $className;
	}

	/**
	 * Undocumented function.
	 *
	 * @param \App\Db\Query $query
	 * @param string        $moduleName
	 * @param int           $user
	 * @param int           $relatedRecord
	 *
	 * @return void
	 */
	public static function getConditions(Db\Query $query, string $moduleName, $user = false, $relatedRecord = null)
	{
		if (!empty(static::$interpreter) && class_exists(static::$interpreter)) {
			return (static::$interpreter)::getConditions($query, $moduleName, $user, $relatedRecord);
		}
		static::getPrivilegeQuery($query, $moduleName, $user, $relatedRecord);
	}

	/**
	 * Undocumented function.
	 *
	 * @param \App\Db\Query $query
	 * @param string        $moduleName
	 * @param int|\App\User $user
	 * @param int           $relatedRecord
	 *
	 * @return void
	 */
	public static function getPrivilegeQuery(Db\Query $query, $moduleName, $user = false, $relatedRecord = null)
	{
		if ($user && $user instanceof User) {
			$userId = $user->getId();
		} elseif (!$user) {
			$userId = \App\User::getCurrentUserId();
		}
		$userModel = \Users_Privileges_Model::getInstanceById($userId);
		if (!$userModel->isAdminUser() && \App\Config::security('PERMITTED_BY_PRIVATE_FIELD') && ($fieldInfo = \App\Field::getFieldInfo('private', $moduleName)) && \in_array($fieldInfo['presence'], [0, 2])) {
			$owners = array_merge([$userId], $userModel->groups);
			$conditions = ['or'];
			$conditions[] = ['vtiger_crmentity.private' => 0];
			$subConditions = ['or', ['vtiger_crmentity.smownerid' => $owners]];
			if (\App\Config::security('PERMITTED_BY_SHARED_OWNERS')) {
				$subQuery = (new \App\Db\Query())->select(['crmid'])->distinct()
					->from('u_yf_crmentity_showners')
					->where(['userid' => $owners]);
				$subConditions[] = ['vtiger_crmentity.crmid' => $subQuery];
			}
			$conditions[] = ['and', ['vtiger_crmentity.private' => 1], $subConditions];
			$query->andWhere($conditions);
		}
		if (\App\Config::security('PERMITTED_BY_RECORD_HIERARCHY') && !empty($relatedRecord)) {
			$role = $userModel->getRoleDetail();
			if (2 == $role->get('listrelatedrecord')) {
				$parentRecord = \Users_Privileges_Model::getParentRecord($relatedRecord, false, $role->get('listrelatedrecord'));
				if ($parentRecord) {
					$relatedRecord = $parentRecord;
				}
			}
			if (0 != $role->get('listrelatedrecord')) {
				$recordMetaData = \vtlib\Functions::getCRMRecordMetadata($relatedRecord);
				$recordPermission = Privilege::isPermitted($recordMetaData['setype'], 'DetailView', $relatedRecord, $userId);
				if ($recordPermission) {
					return '';
				}
			}
		}
		$tabId = Module::getModuleId($moduleName);
		if (!$userModel->isAdminUser() && 1 == $userModel->profile_global_permission[1] && 1 == $userModel->profile_global_permission[2] && 3 === ($userModel->defaultOrgSharingPermission[$tabId] ?? null)) {
			$conditions = ['or'];
			$conditions[] = ['vtiger_crmentity.smownerid' => $userId];
			if (!empty($userModel->groups)) {
				$conditions[] = ['vtiger_crmentity.smownerid' => $userModel->groups];
			}
			if (($modules = \App\Config::security('permittedModulesByCreatorField')) && \in_array($moduleName, $modules)) {
				$conditions[] = ['vtiger_crmentity.smcreatorid' => $userId];
			}
			if (\App\Config::security('PERMITTED_BY_ROLES')) {
				$parentRoleSeq = $userModel->parent_role_seq;
				$subQuery = (new \App\Db\Query())->select(['userid'])
					->from('vtiger_user2role')
					->innerJoin('vtiger_users', 'vtiger_user2role.userid = vtiger_users.id')
					->innerJoin('vtiger_role', 'vtiger_user2role.roleid = vtiger_role.roleid')
					->where(['like', 'vtiger_role.parentrole', "$parentRoleSeq::%", false]);
				$conditions[] = ['vtiger_crmentity.smownerid' => $subQuery];
			}
			if (\App\Config::security('PERMITTED_BY_SHARING')) {
				$sharingPrivileges = \App\User::getSharingFile($userId);
				if (isset($sharingPrivileges['permission'][$moduleName])) {
					$sharingPrivilegesModule = $sharingPrivileges['permission'][$moduleName];
					$sharingRuleInfo = $sharingPrivilegesModule['read'];
					if (!empty($sharingRuleInfo['ROLE'])) {
						$subQuery = (new \App\Db\Query())->select(['shareduserid'])
							->from('vtiger_tmp_read_user_sharing_per')
							->where(['userid' => $userId, 'tabid' => $tabId]);
						$conditions[] = ['vtiger_crmentity.smownerid' => $subQuery];
					}
					if (!empty($sharingRuleInfo['GROUP'])) {
						$subQuery = (new \App\Db\Query())->select(['sharedgroupid'])
							->from('vtiger_tmp_read_group_sharing_per')
							->where(['userid' => $userId, 'tabid' => $tabId]);
						$conditions[] = ['vtiger_crmentity.smownerid' => $subQuery];
					}
				}
			}
			if (\App\Config::security('PERMITTED_BY_SHARED_OWNERS')) {
				$subQuery = (new \App\Db\Query())->select(['crmid'])->distinct()
					->from('u_yf_crmentity_showners')
					->where(['userid' => array_merge([$userId], $userModel->groups)]);
				$conditions[] = ['vtiger_crmentity.crmid' => $subQuery];
			}
			if (!empty($conditions)) {
				$query->andWhere($conditions);
			}
		}
	}
}
