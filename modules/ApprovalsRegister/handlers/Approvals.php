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
 * ApprovalsRegister_Approvals_Handler class.
 */
class ApprovalsRegister_Approvals_Handler
{
	/**
	 * EntityAfterSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$fieldModel = $recordModel->getField('approvals_register_status');
		$acceptValue = \ApprovalsRegister_Module_Model::STATUS_ACCEPTED;
		if ($fieldModel &&
			$fieldModel->isActiveField() &&
			($contactId = (int) $recordModel->get('contactid')) &&
			($recordModel->get($fieldModel->getName()) === $acceptValue || $acceptValue === $recordModel->getPreviousValue($fieldModel->getName()))
			) {
			(new \App\BatchMethod([
				'method' => 'ApprovalsRegister_Module_Model::reloadApprovals',
				'params' => [$contactId]
			]))->save();
		}
	}
}
