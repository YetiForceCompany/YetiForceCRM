<?php
/**
 * Privilege file for client portal.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

namespace Api\Portal;

/**
 * Class to check permission for client portal.
 */
class Privilege
{
	/**
	 * Permissions based on user.
	 */
	const USER_PERMISSIONS = 1;
	/**
	 * All records of account assigned directly to contact.
	 */
	const ACCOUNTS_RELATED_RECORDS = 2;
	/**
	 * All related records of account assigned directly to contact and accounts lower in hierarchy.
	 */
	const ACCOUNTS_RELATED_RECORDS_AND_LOWER_IN_HIERARCHY = 3;
	/**
	 * All related records of account assigned directly to contact and accounts from hierarchy.
	 */
	const ACCOUNTS_RELATED_RECORDS_IN_HIERARCHY = 4;

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
	public static function isPermitted(string $moduleName, $actionName = null, $record = false, $userId = false)
	{
		if (empty($record)) {
			return \App\Privilege::checkPermission($moduleName, $actionName, $record, $userId);
		}
		if (!$userId) {
			$user = \App\User::getCurrentUserModel();
		} else {
			$user = \App\User::getUserModel($userId);
		}
		$permissionFieldInfo = \Api\Core\Module::getFieldPermission($moduleName, $user->get('permission_app'));
		if (!$permissionFieldInfo) {
			return false;
		}
		switch ($user->get('permission_type')) {
			case self::USER_PERMISSIONS:
				return \App\Privilege::checkPermission($moduleName, $actionName, $record, $userId);
			case self::ACCOUNTS_RELATED_RECORDS:
				$parentRecordId = \App\Record::getParentRecord($user->get('permission_crmid'));
				break;
			case self::ACCOUNTS_RELATED_RECORDS_AND_LOWER_IN_HIERARCHY:
			case self::ACCOUNTS_RELATED_RECORDS_IN_HIERARCHY:
				$parentRecordId = (int) \App\Request::_getHeader('x-parent-id');
				if (empty($parentRecordId)) {
					$parentRecordId = \App\Record::getParentRecord($user->get('permission_crmid'));
				}
				break;
			default:
				throw new \Api\Core\Exception('Invalid permissions ', 400);
		}
		if (0 === \App\ModuleHierarchy::getModuleLevel($moduleName)) {
			return $parentRecordId === $record;
		}
		$parentModule = \App\Record::getType($parentRecordId);
		$fields = \App\Field::getRelatedFieldForModule($moduleName);
		$recordModel = \Vtiger_Record_Model::getInstanceById($record, $moduleName);
		if (!$recordModel->get($permissionFieldInfo['fieldname'])) {
			return false;
		}
		if (isset($fields[$parentModule]) && $fields[$parentModule]['name'] !== $fields[$parentModule]['relmod']) {
			$field = $fields[$parentModule];
			return ((int) $recordModel->get($field['fieldname'])) === $parentRecordId;
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
					return ((int) $relatedRecordModel->get($relatedField['fieldname'])) === $parentRecordId;
				}
			}
		}
		return false;
	}
}
