<?php
/**
 * Privilege File for client portal.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\WebservicePremium;

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
	 * @throws \Api\Core\Exception
	 */
	public static function getConditions(\App\Db\Query $query, string $moduleName, $user = false, $relatedRecord = null)
	{
		if (!($user && $user instanceof \App\User)) {
			$user = \App\User::getCurrentUserModel();
		}
		if (!$user->has('permission_type')) {
			return \App\PrivilegeQuery::getPrivilegeQuery($query, $moduleName, $user, $relatedRecord);
		}
		switch ($user->get('permission_type')) {
			case Privilege::USER_PERMISSIONS:
				return \App\PrivilegeQuery::getPrivilegeQuery($query, $moduleName, $user, $relatedRecord);
			case Privilege::ACCOUNTS_RELATED_RECORDS:
				$parentId = \App\Record::getParentRecord($user->get('permission_crmid'));
				break;
			case Privilege::ACCOUNTS_RELATED_RECORDS_AND_LOWER_IN_HIERARCHY:
			case Privilege::ACCOUNTS_RELATED_RECORDS_IN_HIERARCHY:
				$parentId = \Api\WebservicePremium\Privilege::getParentCrmId($user);
				break;
			default:
				throw new \Api\Core\Exception('Invalid permissions ', 400);
		}
		$where = ['and'];
		$fieldInfo = \Api\Core\Module::getApiFieldPermission($moduleName, $user->get('permission_app'));
		if ($fieldInfo) {
			$where[] = [$fieldInfo['tablename'] . '.' . $fieldInfo['columnname'] => 1];
		} elseif ('ModComments' !== $moduleName) {
			$query->andWhere(new Expression('0=1'));
			return;
		}
		\App\PrivilegeQuery::getPrivilegeQuery($query, $moduleName, $user, $relatedRecord);

		$parentModule = \App\Record::getType($parentId) ?? '';
		$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
		$relatedRecordModuleName = $relatedRecord ? \App\Record::getType($relatedRecord) : '';

		if (0 === \App\ModuleHierarchy::getModuleLevel($moduleName)) {
			$where[] = ["{$moduleModel->basetable}.{$moduleModel->basetableid}" => $parentId];
		} elseif (\in_array($moduleName, ['Products', 'Services'])) {
			// exception
		} elseif ('ModComments' !== $moduleName && $fieldsForParent = $moduleModel->getReferenceFieldsForModule($parentModule)) {
			$whereOr = ['or'];
			foreach ($fieldsForParent as $referenceField) {
				$whereOr[] = ["{$referenceField->getTableName()}.{$referenceField->getColumnName()}" => $parentId];
			}
			$where[] = $whereOr;
		} elseif ($relatedRecord && (!$relatedRecordModuleName || !\App\Privilege::isPermitted($relatedRecordModuleName, 'DetailView', $relatedRecord, $user->getId()))) {
			$query->andWhere(new Expression('0=1'));
		} elseif ((!$relatedRecord && !\in_array($moduleName, ['Products', 'Services'])) || (\in_array($relatedRecordModuleName, ['Products', 'Services']) && 'Documents' != $moduleName)) {
			$whereOr = ['or'];
			foreach (array_keys(\App\Relation::getByModule($parentModule, true, $moduleName)) as $relationId) {
				$relationModel = \Vtiger_Relation_Model::getInstanceById($relationId);
				$relationModel->set('parentRecord', \Vtiger_Record_Model::getInstanceById($parentId, $parentModule));
				$queryGenerator = $relationModel->getQuery();
				$queryGenerator->permissions = false;
				$queryGenerator->clearFields()->setFields(['id']);
				$subQuery = $queryGenerator->createQuery()->select($queryGenerator->getColumnName('id'));
				$whereOr[] = ["{$moduleModel->basetable}.{$moduleModel->basetableid}" => $subQuery];
			}
			if ($fields = $moduleModel->getFieldsByReference()) {
				foreach ($fields as $fieldModel) {
					if (!$fieldModel->isActiveField()) {
						continue;
					}
					foreach ($fieldModel->getReferenceList() as $relModuleName) {
						if ('Users' === $relModuleName || $relModuleName === $parentModule || $relModuleName === $moduleName) {
							continue;
						}
						$relModuleModel = \Vtiger_Module_Model::getInstance($relModuleName);
						foreach ($relModuleModel->getReferenceFieldsForModule($parentModule) as $referenceField) {
							$queryGenerator = new \App\QueryGenerator($relModuleName);
							$queryGenerator->permissions = false;
							$queryGenerator->setFields(['id'])->addCondition($referenceField->getName(), $parentId, 'eid');
							$subQuery = $queryGenerator->createQuery()->select($queryGenerator->getColumnName('id'));
							$whereOr[] = ["{$fieldModel->getTableName()}.{$fieldModel->getColumnName()}" => $subQuery];
						}
					}
				}
			}
			if (\count($whereOr) > 1) {
				$where[] = $whereOr;
			} else {
				$query->andWhere(new Expression('0=1'));
			}
		}
		$query->andWhere($where);
	}
}
