<?php
namespace App;

/**
 * Privilege File basic class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class PrivilegeQuery
{

	public static function getAccessConditions($moduleName, $userId = false, $relatedRecord = false)
	{
		if ($userId === false) {
			$currentUser = vglobal('current_user');
			$userId = $currentUser->id;
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
		$tabId = \includes\Modules::getModuleId($moduleName);
		if ($userModel->is_admin === 'off' && $userModel->profile_global_permission[1] == 1 && $userModel->profile_global_permission[2] == 1 && $userModel->defaultOrgSharingPermission[$tabId] === 3) {
			$parentRoleSeq = $userModel->parent_role_seq;
			$query[] = "vtiger_crmentity.smownerid = '$userId'";
			if (!empty($userModel->groups)) {
				$query[] = 'vtiger_crmentity.smownerid IN (' . implode(',', $userModel->groups) . ')';
			}
			if (\AppConfig::security('PERMITTED_BY_ROLES')) {
				$query[] = "vtiger_crmentity.smownerid IN (SELECT vtiger_user2role.userid AS userid FROM vtiger_user2role INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid WHERE vtiger_role.parentrole like '$parentRoleSeq::%')";
			}
			if (\AppConfig::security('PERMITTED_BY_SHARING')) {
				$sharingPrivileges = \Vtiger_Util_Helper::getUserSharingFile($userId);
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
}
