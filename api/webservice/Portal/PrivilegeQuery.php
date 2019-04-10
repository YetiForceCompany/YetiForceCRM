<?php

namespace Api\Portal;

/**
 * Privilege File for client portal
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class PrivilegeQuery
{
	public static function getConditions(\App\Db\Query $query, $moduleName, $user = false, $relatedRecord = false)
	{
		if (!($user && $user instanceof User)) {
			$user = \App\User::getCurrentUserModel();
		}
		switch ($user->get('permission_type')) {
			case 1:
				return \App\PrivilegeQuery::getPrivilegeQuery($query, $moduleName, $user, $relatedRecord);
			case 2:
				$parentId = \App\Record::getParentRecord($user->get('permission_crmid'));
				break;
			case 3:
			case 4:
				$parentId = \App\Request::_getHeader('x-parent-id');
				if (empty($parentId)) {
					$parentId = \App\Record::getParentRecord($user->get('permission_crmid'));
				}
				break;
			default:
				throw new \Api\Core\Exception('Invalid permissions ', 400);
		}
		$parentModule = \App\Record::getType($parentId);
		$fields = \App\Field::getRelatedFieldForModule($moduleName);
		$foundField = true;
		$where = ['and'];
		if (0 === \App\ModuleHierarchy::getModuleLevel($moduleName)) {
			$entityInstance = \Vtiger_CRMEntity::getInstance($moduleName);
			$where[] = [$entityInstance->table_name . '.' . $entityInstance->table_index => $parentId];
		} elseif (isset($fields[$parentModule]) && $fields[$parentModule]['name'] !== $fields[$parentModule]['relmod']) {
			$field = $fields[$parentModule];
			$where[] = ["{$field['tablename']}.{$field['columnname']}" => $parentId];
		} elseif(in_array($moduleName, ['Products', 'Services']))  {
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
