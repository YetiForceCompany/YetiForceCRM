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

	public static function getFieldsPermission($tabId, $block = false, $readOnly = true)
	{
		\App\Log::trace('Entering  ' . __METHOD__);
		$currentUser = \Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$query = (new \App\Db\Query())
			->select('vtiger_field.*, vtiger_profile2field.readonly,vtiger_profile2field.visible')
			->from('vtiger_field')
			->innerJoin('vtiger_profile2field', 'vtiger_profile2field.fieldid = vtiger_field.fieldid')
			->innerJoin('vtiger_def_org_field', 'vtiger_def_org_field.fieldid = vtiger_field.fieldid')
			->where([
				'vtiger_field.tabid' => $tabId,
				'vtiger_profile2field.visible' => 0,
				'vtiger_def_org_field.visible' => 0,
				'vtiger_field.presence' => [0, 2]])
			->groupBy('vtiger_field.fieldid,vtiger_profile2field.readonly,vtiger_profile2field.visible');

		$profileList = $currentUser->getProfiles();
		if ($profileList) {
			$query->andWhere(['vtiger_profile2field.profileid' => $profileList]);
		}
		if ($block) {
			$query->andWhere(['block' => $block]);
		}
		if ($readOnly) {
			$query->andWhere(['vtiger_profile2field.readonly' => 0]);
		}
		return $query->all();
	}
}
