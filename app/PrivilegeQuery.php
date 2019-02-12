<?php

namespace App;

/**
 * Privilege File basic class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class PrivilegeQuery
{
	public static function getAccessConditions($moduleName, $userId = false, $relatedRecord = false)
	{
		if (!$userId) {
			$userId = \App\User::getCurrentUserId();
		}
		$userModel = \Users_Privileges_Model::getInstanceById($userId);
		if ($relatedRecord !== false && \AppConfig::security('PERMITTED_BY_RECORD_HIERARCHY')) {
			$role = $userModel->getRoleDetail();
			if ($role->get('listrelatedrecord') == 2) {
				$rparentRecord = \Users_Privileges_Model::getParentRecord($relatedRecord, false, $role->get('listrelatedrecord'));
				if ($rparentRecord) {
					$relatedRecord = $rparentRecord;
				}
			}
			if ($role->get('listrelatedrecord') != 0) {
				$recordMetaData = \vtlib\Functions::getCRMRecordMetadata($relatedRecord);
				$recordPermission = Privilege::isPermitted($recordMetaData['setype'], 'DetailView', $relatedRecord, $userId);
				if ($recordPermission) {
					return '';
				}
			}
		}
		$query = [];
		$tabId = Module::getModuleId($moduleName);
		if ($userModel->is_admin === 'off' && $userModel->profile_global_permission[1] == 1 && $userModel->profile_global_permission[2] == 1 && $userModel->defaultOrgSharingPermission[$tabId] === 3) {
			$query[] = "vtiger_crmentity.smownerid = '$userId'";
			if (!empty($userModel->groups)) {
				$query[] = 'vtiger_crmentity.smownerid IN (' . implode(',', $userModel->groups) . ')';
			}
			if (\AppConfig::security('PERMITTED_BY_ROLES')) {
				$parentRoleSeq = $userModel->parent_role_seq;
				$query[] = "vtiger_crmentity.smownerid IN (SELECT vtiger_user2role.userid AS userid FROM vtiger_user2role INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid WHERE vtiger_role.parentrole like '$parentRoleSeq::%')";
			}
			if (\AppConfig::security('PERMITTED_BY_SHARING')) {
				$sharingPrivileges = \App\User::getSharingFile($userId);
				if (isset($sharingPrivileges['permission'][$moduleName])) {
					$sharingPrivilegesModule = $sharingPrivileges['permission'][$moduleName];
					$sharingRuleInfo = $sharingPrivilegesModule['read'];
					if (!empty($sharingRuleInfo['ROLE'])) {
						$query[] = "vtiger_crmentity.smownerid IN (SELECT shareduserid FROM vtiger_tmp_read_user_sharing_per WHERE userid=$userId && tabid=$tabId)";
					}
					if (!empty($sharingRuleInfo['GROUP'])) {
						$query[] = "vtiger_crmentity.smownerid IN (SELECT vtiger_tmp_read_group_sharing_per.sharedgroupid FROM vtiger_tmp_read_group_sharing_per WHERE userid=$userId && tabid=$tabId)";
					}
				}
			}
			if (\AppConfig::security('PERMITTED_BY_SHARED_OWNERS')) {
				$shownerid = array_merge([$userId], $userModel->groups);
				$query[] = 'vtiger_crmentity.crmid IN (SELECT DISTINCT crmid FROM u_yf_crmentity_showners WHERE userid IN (' . implode(',', $shownerid) . '))';
			}
		}
		if (!empty($query)) {
			return ' AND (' . implode(' OR ', $query) . ')';
		}
		return '';
	}

	public static function getConditions(\App\Db\Query $query, $moduleName, $user = false, $relatedRecord = false)
	{
		if ($user && $user instanceof User) {
			$userId = $user->getId();
		} elseif (!$user) {
			$userId = \App\User::getCurrentUserId();
		}
		$userModel = \Users_Privileges_Model::getInstanceById($userId);
		if ($relatedRecord !== false && \AppConfig::security('PERMITTED_BY_RECORD_HIERARCHY')) {
			$role = $userModel->getRoleDetail();
			if ($role->get('listrelatedrecord') == 2) {
				$rparentRecord = \Users_Privileges_Model::getParentRecord($relatedRecord, false, $role->get('listrelatedrecord'));
				if ($rparentRecord) {
					$relatedRecord = $rparentRecord;
				}
			}
			if ($role->get('listrelatedrecord') != 0) {
				$recordMetaData = \vtlib\Functions::getCRMRecordMetadata($relatedRecord);
				$recordPermission = Privilege::isPermitted($recordMetaData['setype'], 'DetailView', $relatedRecord, $userId);
				if ($recordPermission) {
					return '';
				}
			}
		}
		$tabId = Module::getModuleId($moduleName);
		if (!$userModel->isAdminUser() && $userModel->profile_global_permission[1] == 1 && $userModel->profile_global_permission[2] == 1 && $userModel->defaultOrgSharingPermission[$tabId] === 3) {
			$conditions = ['or'];
			$conditions[] = ['vtiger_crmentity.smownerid' => $userId];
			if (!empty($userModel->groups)) {
				$conditions[] = ['vtiger_crmentity.smownerid' => $userModel->groups];
			}
			if (\AppConfig::security('PERMITTED_BY_ROLES')) {
				$parentRoleSeq = $userModel->parent_role_seq;
				$subQuery = (new \App\Db\Query())->select(['userid'])
					->from('vtiger_user2role')
					->innerJoin('vtiger_users', 'vtiger_user2role.userid = vtiger_users.id')
					->innerJoin('vtiger_role', 'vtiger_user2role.roleid = vtiger_role.roleid')
					->where(['like', 'vtiger_role.parentrole', "$parentRoleSeq::%", false]);
				$conditions[] = ['vtiger_crmentity.smownerid' => $subQuery];
			}
			if (\AppConfig::security('PERMITTED_BY_SHARING')) {
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
			if (\AppConfig::security('PERMITTED_BY_SHARED_OWNERS')) {
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
