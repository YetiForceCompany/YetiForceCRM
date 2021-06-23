<?php
/**
 * Privilege file for client portal.
 *
 * @package Api
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
	 * @throws \Api\Core\Exception
	 *
	 * @return bool
	 */
	public static function isPermitted(string $moduleName, $actionName = null, $record = false, $userId = false)
	{
		if (!$userId) {
			$user = \App\User::getCurrentUserModel();
		} else {
			$user = \App\User::getUserModel($userId);
		}
		if (empty($record) || !$user->has('permission_type')) {
			return \App\Privilege::checkPermission($moduleName, $actionName, $record, $userId);
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
		if ('ModComments' !== $moduleName && !($permissionFieldInfo = \Api\Core\Module::getApiFieldPermission($moduleName, $user->get('permission_app')))) {
			\App\Privilege::$isPermittedLevel = 'FIELD_PERMISSION_NOT_EXISTS';
			return false;
		}
		if (0 === \App\ModuleHierarchy::getModuleLevel($moduleName)) {
			$permission = $parentRecordId === $record;
			\App\Privilege::$isPermittedLevel = 'RECORD_HIERARCHY_LEVEL_' . ($permission ? 'YES' : 'NO');
			return $permission;
		}

		$recordModel = \Vtiger_Record_Model::getInstanceById($record, $moduleName);
		if ('ModComments' !== $moduleName && !$recordModel->get($permissionFieldInfo['fieldname'])) {
			\App\Privilege::$isPermittedLevel = 'FIELD_PERMISSION_NO';
			return false;
		}

		$parentModule = \App\Record::getType($parentRecordId);
		$fields = \App\Field::getRelatedFieldForModule($moduleName);
		if (isset($fields[$parentModule]) && $fields[$parentModule]['name'] !== $fields[$parentModule]['relmod']) {
			$field = $fields[$parentModule];
			$permission = ((int) $recordModel->get($field['fieldname'])) === $parentRecordId;
			\App\Privilege::$isPermittedLevel = 'RECORD_RELATED_' . ($permission ? 'YES' : 'NO');
			return $permission;
		}
		if (\in_array($moduleName, ['Products', 'Services'])) {
			$permission = (bool) $recordModel->get('discontinued');
			\App\Privilege::$isPermittedLevel = $moduleName . '_DISCONTINUED_' . ($permission ? 'YES' : 'NO');
			return $permission;
		}
		foreach ($fields as $relatedModuleName => $field) {
			if ($relatedModuleName === $parentModule) {
				continue;
			}
			if ($relatedField = \App\Field::getRelatedFieldForModule($relatedModuleName, $parentModule)) {
				$relatedRecordModel = \Vtiger_Record_Model::getInstanceById($recordModel->get($field['fieldname'], $relatedModuleName));
				$permission = ((int) $relatedRecordModel->get($relatedField['fieldname'])) === $parentRecordId;
				\App\Privilege::$isPermittedLevel = $moduleName . '_RELATED_' . ($permission ? 'YES' : 'NO');
				return $permission;
			}
		}
		\App\Privilege::$isPermittedLevel = 'ALL_PERMISSION_NO';
		return false;
	}
}
