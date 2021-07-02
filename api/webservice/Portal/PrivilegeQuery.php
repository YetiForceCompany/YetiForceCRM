<?php
/**
 * Privilege File for client portal.
 *
 * @package Api
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
				$parentId = \App\Request::_getHeader('x-parent-id');
				if (empty($parentId)) {
					$parentId = \App\Record::getParentRecord($user->get('permission_crmid'));
				}
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
		$parentModule = \App\Record::getType($parentId);

		$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
		if (0 === \App\ModuleHierarchy::getModuleLevel($moduleName)) {
			$where[] = ["{$moduleModel->basetable}.{$moduleModel->basetableid}" => $parentId];
		} elseif ('ModComments' === $moduleName && $relatedRecord && \App\Privilege::isPermitted(\App\Record::getType($relatedRecord), 'DetailView', $relatedRecord, $user->getId())) {
			return;
		} elseif ($parentModule !== $moduleName && ($referenceField = current($moduleModel->getReferenceFieldsForModule($parentModule)))) {
			$where[] = ["{$referenceField->getTableName()}.{$referenceField->getColumnName()}" => $parentId];
		} elseif ($relationId = key(\App\Relation::getByModule($parentModule, true, $moduleName))) {
			$relationModel = \Vtiger_Relation_Model::getInstanceById($relationId);
			$relationModel->set('parentRecord', \Vtiger_Record_Model::getInstanceById($parentId, $parentModule));
			$queryGenerator = $relationModel->getQuery();
			$queryGenerator->permissions = false;
			$queryGenerator->clearFields()->setFields(['id']);
			$subQuery = $queryGenerator->createQuery()->select($queryGenerator->getColumnName('id'));
			$where[] = ["{$moduleModel->basetable}.{$moduleModel->basetableid}" => $subQuery];
		} elseif ($fields = $moduleModel->getFieldsByReference()) {
			$foundField = false;
			foreach ($fields as $fieldModel) {
				if (!$fieldModel->isActiveField()) {
					continue;
				}
				foreach ($fieldModel->getReferenceList() as $relModuleName) {
					if ('Users' === $relModuleName || $relModuleName === $parentModule || $relModuleName === $moduleName) {
						continue;
					}
					$relModuleModel = \Vtiger_Module_Model::getInstance($relModuleName);
					if ($referenceField = current($relModuleModel->getReferenceFieldsForModule($parentModule))) {
						$queryGenerator = new \App\QueryGenerator($relModuleName);
						$queryGenerator->permissions = false;
						$queryGenerator->setFields(['id'])->addCondition($referenceField->getName(), $parentId, 'eid');
						$subQuery = $queryGenerator->createQuery()->select($queryGenerator->getColumnName('id'));
						$where[] = ["{$fieldModel->getTableName()}.{$fieldModel->getColumnName()}" => $subQuery];
						$foundField = true;
						break 2;
					}
				}
			}
			if (!$foundField) {
				$query->andWhere(new Expression('0=1'));
			}
		} else {
			$query->andWhere(new Expression('0=1'));
		}
		if (\in_array($moduleName, ['Products', 'Services']) && ($fieldModel = $moduleModel->getFieldByName('discontinued')) && $fieldModel->isActiveField()) {
			$where[] = ["{$fieldModel->getTableName()}.{$fieldModel->getColumnName()}" => 1];
		}
		$query->andWhere($where);
		if (\App\Config::security('PERMITTED_BY_PRIVATE_FIELD') && ($fieldInfo = \App\Field::getFieldInfo('private', $moduleName)) && \in_array($fieldInfo['presence'], [0, 2])) {
			$owners = array_merge([$user->getId()], $user->getGroups());
			$conditions = ['or'];
			$conditions[] = ['vtiger_crmentity.private' => 0];
			$subConditions = ['or', ['vtiger_crmentity.smownerid' => $owners]];
			if (\App\Config::security('PERMITTED_BY_SHARED_OWNERS')) {
				$subQuery = (new \App\Db\Query())->select(['crmid'])->distinct()->from('u_yf_crmentity_showners')->where(['userid' => $owners]);
				$subConditions[] = ['vtiger_crmentity.crmid' => $subQuery];
			}
			$conditions[] = ['and', ['vtiger_crmentity.private' => 1], $subConditions];
			$query->andWhere($conditions);
		}
	}
}
