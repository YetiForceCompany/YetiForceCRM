<?php

namespace Api\Portal;

/**
 * Privilege file for client portal.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class Privilege
{
	/**
	 * Function to check permission for a Module/Action/Record.
	 *
	 * @param string   $moduleName
	 * @param string   $actionName
	 * @param bool|int $record
	 * @param mixed    $userId
	 *
	 * @return bool
	 */
	public static function isPermitted($moduleName, $actionName = null, $record = false, $userId = false)
	{
		if (!($user && $user instanceof User)) {
			$user = \App\User::getCurrentUserModel();
		}
		$permissionType = (int) $user->get('permission_type');
		if (1 === $permissionType || empty($record)) {
			return \App\Privilege::checkPermission($moduleName, $actionName, $record, $userId);
		}
		if (2 === $permissionType) {
			$parentRecordId = \App\Record::getParentRecord($user->get('permission_crmid'));
		} elseif (3 === $permissionType || 4 === $permissionType) {
			$parentRecordId = \App\Request::_getHeader('x-parent-id');
			if (empty($parentRecordId)) {
				$parentRecordId = \App\Record::getParentRecord($user->get('permission_crmid'));
			}
		}
		if (0 === \App\ModuleHierarchy::getModuleLevel($moduleName)) {
			return $parentRecordId == $record;
		}
		$parentModule = \App\Record::getType($parentRecordId);
		$fields = \App\Field::getRelatedFieldForModule($moduleName);
		$recordModel = \Vtiger_Record_Model::getInstanceById($record, $moduleName);
		if (isset($fields[$parentModule]) && $fields[$parentModule]['name'] !== $fields[$parentModule]['relmod']) {
			$field = $fields[$parentModule];
			return $recordModel->get($field['fieldname']) == $parentRecordId;
		}
		if (in_array($moduleName, ['Products', 'Services'])) {
			return (bool) $recordModel->get('discontinued');
		}
		if ($fields) {
			foreach ($fields as $relatedModuleName => $field) {
				if ($relatedModuleName === $parentModule) {
					continue;
				}
				if ($relatedField = \App\Field::getRelatedFieldForModule($relatedModuleName, $parentModule)) {
					$relatedRecordModel = \Vtiger_Record_Model::getInstanceById($recordModel->get($field['fieldname'], $relatedModuleName));
					return $relatedRecordModel->get($relatedField['fieldname']) == $parentRecordId;
				}
			}
		}
		return false;
	}
}
