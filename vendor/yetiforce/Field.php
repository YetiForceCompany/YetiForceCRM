<?php
namespace App;

/**
 * Field basic class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Field
{

	public static function getUserFields($tabId, $block = false)
	{
		$currentUser = \Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$profileGlobalPermission = $currentUser->get('profile_global_permission');
		if ($currentUser->isAdminUser() || $profileGlobalPermission[1] === 0 || $profileGlobalPermission[2] === 0) {
			$query = (new \App\Db\Query())
				->from('vtiger_field')
				->where(['tabid' => $tabId, 'presence' => [0, 2]]);
		} else {
			$query = (new \App\Db\Query())
				->select('vtiger_field.tabid,vtiger_field.*, vtiger_profile2field.readonly')
				->from('vtiger_field')
				->innerJoin('vtiger_profile2field', 'vtiger_profile2field.fieldid = vtiger_field.fieldid')
				->innerJoin('vtiger_def_org_field', 'vtiger_def_org_field.fieldid = vtiger_field.fieldid')
				->where([
				'vtiger_field.tabid' => $tabId,
				'vtiger_profile2field.visible' => 0,
				'vtiger_def_org_field.visible' => 0,
				'vtiger_field.presence' => [0, 2]]);
			$profileList = $currentUser->getProfiles();
			if (!empty($profileList)) {
				$query->andWhere(['vtiger_profile2field.profileid' => $profileList]);
			}
		}
		if ($block !== false) {
			$query->andWhere(['block' => $block]);
		}
		return $query->all();
	}
}
