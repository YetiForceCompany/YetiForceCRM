<?php
/**
 * Duplicate vat id handler.
 *
 * @package Handler
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Accounts_DuplicateVatId_Handler class.
 */
class Accounts_DuplicateVatId_Handler
{
	/**
	 * EditViewPreSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function editViewPreSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$response = ['result' => true];
		$fieldModel = $recordModel->getModule()->getFieldByName('vat_id');
		if ($fieldModel->isViewable() && ($vat = $recordModel->get('vat_id'))) {
			$queryGenerator = new \App\QueryGenerator($recordModel->getModuleName());
			$queryGenerator->setStateCondition('All');
			$queryGenerator->setFields(['id'])->permissions = false;
			$queryGenerator->addCondition($fieldModel->getName(), $vat, 'e');
			if ($recordModel->getId()) {
				$queryGenerator->addCondition('id', $recordModel->getId(), 'n');
			}
			if ($queryGenerator->createQuery()->exists()) {
				$response = [
					'result' => false,
					'hoverField' => 'vat_id',
					'message' => App\Language::translate('LBL_DUPLICATE_VAT_ID', $recordModel->getModuleName())
				];
			}
		}
		return $response;
	}
}
