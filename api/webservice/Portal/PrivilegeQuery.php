<?php
/**
 * Privilege File for client portal.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

namespace Api\Portal;

use yii\db\Expression;

/**
 * Class to check permission for client portal.
 */
class PrivilegeQuery
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Db\Query $query
	 * @param string        $moduleName
	 * @param mixed         $user
	 * @param int           $relatedRecord
	 *
	 * @return void
	 */
	public static function getConditions(\App\Db\Query $query, string $moduleName, $user = false, $relatedRecord = false)
	{
		if (!($user && $user instanceof User)) {
			$user = \App\User::getCurrentUserModel();
		}
		switch ($user->get('permission_type')) {
			case Privilege::USER_PERMISSIONS:
				return \App\PrivilegeQuery::getPrivilegeQuery($query, $moduleName, $user, $relatedRecord);
			case Privilege::ACCOUNTS_RELATED_RECORDS:
				$parentId = \App\Record::getParentRecord($user->get('permission_crmid'));
				break;
			case Privilege::ACCOUNTS_RELATED_RECORDS_AND_LOWER_IN_HIERARCHY:
			case Privilege::ACCOUNTS_RELATED_RECORDS_IN_HIERARCHY:
				$parentId = \App\Request::_getHeader('x-parent-id');
				if (empty($parentId)) {
					$parentId = \App\Record::getParentRecord($user->get('permission_crmid'));
				}
				break;
			default:
				throw new \Api\Core\Exception('Invalid permissions ', 400);
		}
		$fieldInfo = \Api\Core\Module::getFieldPermission($moduleName, $user->get('permission_app'));
		if (!$fieldInfo) {
			$query->andWhere(new Expression('0=1'));
			return;
		}
		$where = ['and'];
		$where[] = [$fieldInfo['tablename'] . '.' . $fieldInfo['columnname'] => 1];
		$parentModule = \App\Record::getType($parentId);
		$fields = \App\Field::getRelatedFieldForModule($moduleName);
		$foundField = true;
		if (0 === \App\ModuleHierarchy::getModuleLevel($moduleName)) {
			$entityInstance = \Vtiger_CRMEntity::getInstance($moduleName);
			$where[] = [$entityInstance->table_name . '.' . $entityInstance->table_index => $parentId];
		} elseif (isset($fields[$parentModule]) && $fields[$parentModule]['name'] !== $fields[$parentModule]['relmod']) {
			$field = $fields[$parentModule];
			$where[] = ["{$field['tablename']}.{$field['columnname']}" => $parentId];
		} elseif (in_array($moduleName, ['Products', 'Services'])) {
			$fieldModel = \Vtiger_Field_Model::getInstance('discontinued', \Vtiger_Module_Model::getInstance($moduleName));
			$where[] = ["{$fieldModel->getTableName()}.{$fieldModel->getColumnName()}" => 1];
		} elseif ($fields) {
			$foundField = false;
			foreach ($fields as $relatedModuleName => $field) {
				if ($relatedModuleName === $parentModule) {
					continue;
				}
				if ($relatedField = \App\Field::getRelatedFieldForModule($relatedModuleName, $parentModule)) {
					$entityInstance = \Vtiger_CRMEntity::getInstance($relatedField['name']);
					$query->innerJoin($entityInstance->table_name, "{$entityInstance->table_name}.$entityInstance->table_index = {$field['tablename']}.{$field['columnname']}");
					$where[] = ["{$relatedField['tablename']}.{$relatedField['columnname']}" => $parentId];
					$foundField = true;
				}
			}
		}
		if (!$foundField) {
			throw new \Api\Core\Exception('Invalid module, no relationship', 400);
		}
		$query->andWhere($where);
	}
}
