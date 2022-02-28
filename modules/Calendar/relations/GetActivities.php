<?php
/**
 * Main file that includes basic operations on relations.
 *
 * @package   Relation
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Calendar_GetActivities_Relation class.
 */
class Calendar_GetActivities_Relation extends Vtiger_GetDependentsList_Relation
{
	/** {@inheritdoc} */
	public function getQuery()
	{
		$moduleName = $this->relationModel->getParentModuleModel()->getName();
		$fields = $this->relationModel->getRelationModuleModel()->getReferenceFieldsForModule($moduleName);
		if (!$fields) {
			\App\Log::error('No relation field | Relation name:' . $this->relationModel->get('name') . ' | Relation id:' . $this->relationModel->getId(), __METHOD__);
			throw new \App\Exceptions\AppException('ERR_ILLEGAL_VALUE');
		}
		$conditions = ['or'];
		foreach ($fields as $fieldModel) {
			$conditions[] = ["{$fieldModel->getTableName()}.{$fieldModel->getColumnName()}" => $this->relationModel->get('parentRecord')->getId()];
		}
		$queryGenerator = $this->relationModel->getQueryGenerator();
		$queryGenerator->addNativeCondition($conditions);
		switch (\App\Request::_get('time')) {
			case 'current':
				$queryGenerator->addCondition('activitystatus', implode('##', Calendar_Module_Model::getComponentActivityStateLabel('current')), 'e');
				break;
			case 'history':
				$queryGenerator->addCondition('activitystatus', implode('##', Calendar_Module_Model::getComponentActivityStateLabel('history')), 'e');
				break;
			default:
				break;
		}
	}
}
