<?php
/**
 * Privilege file for client portal.
 *
 * @package Api
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		$userId = $user->getId();
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

		$moduleModel = $recordModel->getModule();
		if (\App\Config::security('PERMITTED_BY_PRIVATE_FIELD') && ($privateField = $recordModel->getField('private')) && $privateField->isActiveField() && $recordModel->get($privateField->getName())) {
			$isPermittedPrivateRecord = false;
			$recOwnId = $recordModel->get('assigned_user_id');
			$recOwnType = \App\Fields\Owner::getType($recOwnId);
			if ('Users' === $recOwnType) {
				if ($userId === $recOwnId) {
					$isPermittedPrivateRecord = true;
				}
			} elseif ('Groups' === $recOwnType) {
				if (\in_array($recOwnId, $user->getGroups())) {
					$isPermittedPrivateRecord = true;
				}
			}
			if (!$isPermittedPrivateRecord && \App\Config::security('PERMITTED_BY_SHARED_OWNERS')) {
				$shownerIds = \App\Fields\SharedOwner::getById($record);
				if (\in_array($userId, $shownerIds) || \count(array_intersect($shownerIds, $user->getGroups())) > 0) {
					$isPermittedPrivateRecord = true;
				}
			}
			if (!$isPermittedPrivateRecord) {
				\App\Privilege::$isPermittedLevel = 'SEC_PRIVATE_RECORD_NO';
				return $isPermittedPrivateRecord;
			}
		}
		if (\in_array($moduleName, ['Products', 'Services']) && !$recordModel->get('discontinued')) {
			\App\Privilege::$isPermittedLevel = $moduleName . '_DISCONTINUED_NO';
			return false;
		}
		if ($parentModule !== $moduleName && ($referenceField = current($moduleModel->getReferenceFieldsForModule($parentModule))) && $recordModel->get($referenceField->getName()) === $parentRecordId) {
			\App\Privilege::$isPermittedLevel = 'RECORD_RELATED_YES';
			return true;
		}
		if ($relationId = key(\App\Relation::getByModule($parentModule, true, $moduleName))) {
			$relationModel = \Vtiger_Relation_Model::getInstanceById($relationId);
			$relationModel->set('parentRecord', \Vtiger_Record_Model::getInstanceById($parentRecordId, $parentModule));
			$queryGenerator = $relationModel->getQuery();
			$queryGenerator->permissions = false;
			$queryGenerator->clearFields()->setFields(['id'])->addCondition('id', $record, 'e');
			if ($queryGenerator->createQuery()->exists()) {
				\App\Privilege::$isPermittedLevel = $moduleName . '_RELATED_YES';
				return true;
			}
		} elseif ($fields = $moduleModel->getFieldsByReference()) {
			foreach ($fields as $fieldModel) {
				if (!$fieldModel->isActiveField() || $recordModel->isEmpty($fieldModel->getName())) {
					continue;
				}
				$relRecordId = $recordModel->get($fieldModel->getName());
				foreach ($fieldModel->getReferenceList() as $relModuleName) {
					if ('Users' === $relModuleName || $relModuleName === $parentModule || $relModuleName === $moduleName) {
						continue;
					}
					$relModuleModel = \Vtiger_Module_Model::getInstance($relModuleName);
					if (($referenceField = current($relModuleModel->getReferenceFieldsForModule($parentModule))) && \App\Record::isExists($relRecordId, $relModuleName) && \App\Record::getType($relRecordId) === $relModuleName && \Vtiger_Record_Model::getInstanceById($relRecordId, $relModuleName)->get($referenceField->getName()) === $parentRecordId) {
						\App\Privilege::$isPermittedLevel = $moduleName . '_RELATED_SL_YES';
						return true;
					}
				}
			}
		}

		\App\Privilege::$isPermittedLevel = 'ALL_PERMISSION_NO';
		return false;
	}
}
