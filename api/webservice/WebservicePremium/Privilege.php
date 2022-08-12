<?php
/**
 * Privilege file for client portal.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\WebservicePremium;

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
				$parentRecordId = static::getParentCrmId($user);
				break;
			default:
				throw new \Api\Core\Exception('Invalid permissions ', 400);
		}
		if ('ModComments' !== $moduleName && !($permissionFieldInfo = \Api\Core\Module::getApiFieldPermission($moduleName, $user->get('permission_app')))) {
			\App\Privilege::$isPermittedLevel = 'FIELD_PERMISSION_NOT_EXISTS';
			return false;
		}
		if (!\App\Privilege::checkPermission($moduleName, $actionName, $record, $userId)) {
			return false;
		}
		if (0 === \App\ModuleHierarchy::getModuleLevel($moduleName)) {
			$permission = $parentRecordId === $record;
			\App\Privilege::$isPermittedLevel = 'RECORD_HIERARCHY_LEVEL_' . ($permission ? 'YES' : 'NO');
			return $permission;
		}
		$recordModel = \Vtiger_Record_Model::getInstanceById($record, $moduleName);
		if ('ModComments' !== $moduleName && !$recordModel->get($permissionFieldInfo['fieldname'])) {
			\App\Privilege::$isPermittedLevel = "FIELD_PERMISSION_NO {$permissionFieldInfo['fieldname']}: {$recordModel->get($permissionFieldInfo['fieldname'])}";
			return false;
		}

		$parentModule = \App\Record::getType($parentRecordId) ?? '';
		$moduleModel = $recordModel->getModule();
		if (\in_array($moduleName, ['Products', 'Services'])) {
			\App\Privilege::$isPermittedLevel = $moduleName . '_SPECIAL_PERMISSION_YES';
			return true;
		}
		$fieldsForParent = $moduleModel->getReferenceFieldsForModule($parentModule);
		if ($fieldsForParent) {
			foreach ($fieldsForParent as $referenceField) {
				if ($recordModel->get($referenceField->getName()) === $parentRecordId) {
					\App\Privilege::$isPermittedLevel = 'RECORD_RELATED_YES';
					return true;
				}
			}
			\App\Privilege::$isPermittedLevel = 'RECORD_RELATED_NO';
			return false;
		}
		foreach (array_keys(\App\Relation::getByModule($parentModule, true, $moduleName)) as $relationId) {
			$relationModel = \Vtiger_Relation_Model::getInstanceById($relationId);
			$relationModel->set('parentRecord', \Vtiger_Record_Model::getInstanceById($parentRecordId, $parentModule));
			$queryGenerator = $relationModel->getQuery();
			$queryGenerator->permissions = false;
			$queryGenerator->clearFields()->setFields(['id'])->addCondition('id', $record, 'e');
			if ($queryGenerator->createQuery()->exists()) {
				\App\Privilege::$isPermittedLevel = $moduleName . '_RELATED_YES';
				return true;
			}
		}
		if ($fields = $moduleModel->getFieldsByReference()) {
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
					foreach ($relModuleModel->getReferenceFieldsForModule($parentModule) as $referenceField) {
						if (\App\Record::isExists($relRecordId, $relModuleName) && \App\Record::getType($relRecordId) === $relModuleName && \Vtiger_Record_Model::getInstanceById($relRecordId, $relModuleName)->get($referenceField->getName()) === $parentRecordId) {
							\App\Privilege::$isPermittedLevel = $moduleName . '_RELATED_SL_YES';
							return true;
						}
					}
				}
			}
		}
		if ('Documents' === $moduleName) {
			foreach (\Documents_Record_Model::getReferenceModuleByDocId($record) as $parentModuleName) {
				$relationListView = \Vtiger_RelationListView_Model::getInstance($recordModel, $parentModuleName);
				$relationListView->setFields([]);
				$relationListView->getQueryGenerator()->setFields(['id'])->setLimit(10)->permissions = false;
				$dataReader = $relationListView->getRelationQuery()->createCommand()->query();
				while ($id = $dataReader->readColumn(0)) {
					if (\App\Privilege::isPermitted($parentModuleName, 'DetailView', $id, $user->getId())) {
						\App\Privilege::$isPermittedLevel = "PERMISSION_{$parentModuleName}_YES-{$id}";
						return true;
					}
				}
			}
		}

		\App\Privilege::$isPermittedLevel = 'ALL_PERMISSION_NO';
		return false;
	}

	/**
	 * Gets parent ID.
	 *
	 * @param \App\User $user
	 *
	 * @return int
	 */
	public static function getParentCrmId(\App\User $user): int
	{
		$contactId = $user->get('permission_crmid');
		$parentApiId = \App\Record::getParentRecord($contactId);
		if (($parentId = (int) \App\Request::_getHeader('x-parent-id')) && $parentApiId !== $parentId) {
			$hierarchy = new \Api\WebservicePremium\BaseModule\Hierarchy();
			$hierarchy->setAllUserData(['crmid' => $contactId, 'type' => $user->get('permission_type')]);
			$hierarchy->findId = $parentId;
			$hierarchy->moduleName = \App\Record::getType($parentApiId);
			$records = $hierarchy->get();
			if (isset($records[$parentId])) {
				return $parentId;
			}
			throw new \Api\Core\Exception('No permission to X-PARENT-ID', 403);
		}
		return $parentApiId;
	}
}
