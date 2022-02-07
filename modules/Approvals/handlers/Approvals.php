<?php
/**
 * Approvals handler.
 *
 * @package Handler
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Approvals_Approvals_Handler class.
 */
class Approvals_Approvals_Handler
{
	/**
	 * EntityAfterSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$fieldModel = $recordModel->getField('approvals_status');
		$acceptValue = 'PLL_ACTIVE';
		if ($fieldModel
			&& $fieldModel->isActiveField()
			&& ($recordModel->get($fieldModel->getName()) === $acceptValue || $acceptValue === $recordModel->getPreviousValue($fieldModel->getName()))
			) {
			(new \App\BatchMethod([
				'method' => 'Approvals_Approvals_Handler::reloadApprovals',
				'params' => [$recordModel->getId()]
			]))->save();
		}
	}

	/**
	 * Reload approvals.
	 *
	 * @param int $approvalId
	 *
	 * @return void
	 */
	public static function reloadApprovals(int $approvalId): void
	{
		$moduleName = 'Approvals';
		if (\App\Record::isExists($approvalId, $moduleName)) {
			$contactModal = \Vtiger_Module_Model::getInstance('Contacts');
			foreach ($contactModal->getFieldsByType('multiReference', true) as $fieldModel) {
				if ($fieldModel->getReferenceList() === [$moduleName]) {
					$dataReader = (new \App\QueryGenerator('ApprovalsRegister'))
						->setFields(['contactid'])
						->addCondition('approvalsid', $approvalId, 'eid')->setDistinct('contactid')
						->createQuery()->createCommand()->query();
					while ($contactId = $dataReader->readColumn(0)) {
						(new \App\BatchMethod([
							'method' => 'ApprovalsRegister_Module_Model::reloadApprovals',
							'params' => [$contactId]
						]))->save();
					}
				}
			}
		}
	}
}
