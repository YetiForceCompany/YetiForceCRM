<?php
/**
 * Approvals Register Module Model Class.
 *
 * @package Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * ApprovalsRegister_Module_Model class.
 */
class ApprovalsRegister_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Status.
	 */
	public const STATUS_ACCEPTED = 'PLL_ACCEPTED';

	/**
	 * Reload approvals.
	 *
	 * @param int $contactId
	 */
	public static function reloadApprovals(int $contactId)
	{
		if (\App\Record::isExists($contactId)) {
			$moduleName = 'ApprovalsRegister';
			$relatedModule = 'Approvals';
			$acceptValue = self::STATUS_ACCEPTED;
			$recordModel = \Vtiger_Record_Model::getInstanceById($contactId);
			$referenceFieldModel = null;
			foreach ($recordModel->getModule()->getFieldsByType('multiReference') as $fieldModel) {
				if ($fieldModel->isActiveField() && $fieldModel->getReferenceList() === $relatedModule) {
					$referenceFieldModel = $fieldModel;
					break;
				}
			}
			$relatedModuleModel = \Vtiger_Module_Model::getInstance($moduleName);
			$relatedApproveModel = \Vtiger_Module_Model::getInstance($relatedModule);
			if ($referenceFieldModel &&
				$relatedModuleModel->isActive() &&
				$relatedApproveModel->isActive() &&
				($relationModel = \Vtiger_Relation_Model::getInstance($recordModel->getModule(), $relatedModuleModel)) &&
				($relationApproveModel = \Vtiger_Relation_Model::getInstance($relatedApproveModel, $relatedModuleModel)) &&
				($fieldModel = $relationApproveModel->getRelationField()) &&
				$fieldModel->isActiveField()
			) {
				$relationModel->set('parentRecord', $recordModel);
				$subQuery = (new \App\QueryGenerator($relatedModule))->addCondition('approvals_status', 'PLL_ACTIVE', 'e')->setFields(['id'])->createQuery();

				$approves = [];
				$queryGenerator = $relationApproveModel->getQueryGenerator()
					->addTableToQuery($fieldModel->getTableName())
					->setFields(['approvals_register_type'])
					->addCondition('approvals_register_status', $acceptValue, 'e')
					->setOrder('registration_date', 'DESC')
					->setLimit(1);

				$dataReader = $relationModel->getQuery()->setFields([$fieldModel->getName()])
					->addCondition('approvals_register_status', $acceptValue, 'e')
					->addNativeCondition([$fieldModel->getTableName() . '.' . $fieldModel->getColumnName() => $subQuery])
					->setGroup($fieldModel->getName())->createQuery()->createCommand()->query();

				while ($approvalId = $dataReader->readColumn(0)) {
					$type = (clone $queryGenerator)
						->addNativeCondition([$fieldModel->getTableName() . '.' . $fieldModel->getColumnName() => $approvalId])
						->createQuery()->scalar();
					if ('PLL_ACCEPTANCE' === $type) {
						$approves[] = $approvalId;
					}
				}
				$dataReader->close();
				$recordModel->set($referenceFieldModel->getName(), $referenceFieldModel->getUITypeModel()->getDBValue($approves))->save();
			}
		}
	}
}
