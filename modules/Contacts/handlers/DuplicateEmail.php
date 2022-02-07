<?php
/**
 * Duplicate e-mail handler.
 *
 * @package Handler
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Contacts_DuplicateEmail_Handler class.
 */
class Contacts_DuplicateEmail_Handler
{
	/**
	 * EditViewPreSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 *
	 * @return array Example: ['result' => false, 'message' => 'LBL_MESSAGE']
	 */
	public function editViewPreSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$response = ['result' => true];
		$values = [];
		$fields = $recordModel->getModule()->getFieldsByType('email', true);
		foreach ($fields as $fieldModel) {
			if (($value = $recordModel->get($fieldModel->getName())) && $fieldModel->isViewable()) {
				$values[] = $value;
			}
		}
		if ($fields && $values) {
			$queryGenerator = new \App\QueryGenerator($recordModel->getModuleName());
			$queryGenerator->setStateCondition('All');
			$queryGenerator->setFields(['id'])->permissions = false;
			foreach ($fields as $fieldModel) {
				$queryGenerator->addCondition($fieldModel->getName(), $values, 'e', false);
			}
			if ($recordModel->getId()) {
				$queryGenerator->addCondition('id', $recordModel->getId(), 'n', true);
			}
			if ($queryGenerator->createQuery()->exists()) {
				$response = [
					'result' => false,
					'message' => App\Language::translate('LBL_DUPLICATE_EMAIL_ADDRESS', $recordModel->getModuleName())
				];
			}
		}
		return $response;
	}
}
