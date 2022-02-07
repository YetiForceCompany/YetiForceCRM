<?php
/**
 * Approvals Register Module Model Class.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
				if ($fieldModel->isActiveField() && $fieldModel->getReferenceList() === [$relatedModule]) {
					$referenceFieldModel = $fieldModel;
					break;
				}
			}
			$relatedModuleModel = \Vtiger_Module_Model::getInstance($moduleName);
			$relatedApproveModel = \Vtiger_Module_Model::getInstance($relatedModule);
			if ($referenceFieldModel
				&& $relatedModuleModel->isActive()
				&& $relatedApproveModel->isActive()
				&& ($relationModel = \Vtiger_Relation_Model::getInstance($recordModel->getModule(), $relatedModuleModel))
				&& ($relationApproveModel = \Vtiger_Relation_Model::getInstance($relatedApproveModel, $relatedModuleModel))
				&& ($fieldModel = $relationApproveModel->getRelationField())
				&& $fieldModel->isActiveField()
			) {
				$relationModel->set('parentRecord', $recordModel);
				$subQuery = (new \App\QueryGenerator($relatedModule))->addCondition('approvals_status', 'PLL_ACTIVE', 'e')->setFields(['id'])->createQuery();

				$queryGenerator = $relationModel->getQuery();
				$sqlColumnName = $queryGenerator->getColumnName('registration_date');
				$approvalAll = $queryGenerator->clearFields()->setFields([$fieldModel->getName(), 'approvals_register_type'])
					->setCustomColumn(['registration_date' => new \yii\db\Expression("MAX({$sqlColumnName})")])
					->addCondition('approvals_register_status', $acceptValue, 'e')
					->addNativeCondition([$queryGenerator->getColumnName($fieldModel->getName()) => $subQuery])
					->setGroup($fieldModel->getName())
					->setGroup('approvals_register_type')
					->setOrder('registration_date', \App\Db::DESC)
					->createQuery()->createCommand()->queryAllByGroup(2);

				$approves = array_keys(array_filter($approvalAll, function ($item) {
					return 'PLL_ACCEPTANCE' === $item[0];
				}));

				$recordModel->set($referenceFieldModel->getName(), $referenceFieldModel->getUITypeModel()->getDBValue($approves))->save();
			}
		}
	}
}
