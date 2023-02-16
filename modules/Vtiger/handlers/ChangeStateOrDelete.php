<?php
/**
 * Check is record exists in inventory records file.
 *
 * @package Handler
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */

/**
 * Check is record exists in inventory records class.
 */
class Vtiger_ChangeStateOrDelete_Handler
{
	/**
	 * Register pre delete.
	 *
	 * @param App\EventHandler $eventHandler
	 *
	 * @return void
	 */
	public function preDelete(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$inventoryDeleteModel = new Vtiger_Delete_Model($recordModel);
		$inventoryDeleteModel->setRecordsWhereDeleteRecordIsSet();
		$result = ['result' => true];
		if ($inventoryDeleteModel->isRelatedRecordExists()) {
			$result = [
				'result' => false,
				'type' => 'confirm',
				'message' => App\Language::translate('LBL_CONFIRM_DELETE_OR_CHANGE_STATE', $recordModel->getModuleName()) . ': ' . $inventoryDeleteModel->getRelatedRecordsDisplayValue(),
				'hash' => hash('sha256', implode('|', $recordModel->getData()))
			];
		}
		return $result;
	}

	public function preStateChange(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$result = ['result' => true];
		if (App\Record::STATE_ACTIVE === App\Record::getState($recordModel->getId())) {
			$inventoryDeleteModel = new Vtiger_Delete_Model($recordModel);
			$inventoryDeleteModel->setRecordsWhereDeleteRecordIsSet();
			$result = ['result' => true];
			if ($inventoryDeleteModel->isRelatedRecordExists()) {
				$result = [
					'result' => false,
					'type' => 'confirm',
					'message' => App\Language::translate('LBL_CONFIRM_DELETE_OR_CHANGE_STATE', $recordModel->getModuleName()) . ': ' . $inventoryDeleteModel->getRelatedRecordsDisplayValue(),
					'hash' => hash('sha256', implode('|', $recordModel->getData()))
				];
			}
		}
		return $result;
	}
}
