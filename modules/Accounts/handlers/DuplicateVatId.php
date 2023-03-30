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
	/** @var array List of fields for verification */
	const FIELDS = [
		'Accounts' => ['vat_id'],
		'Leads' => ['vat_id'],
	];

	/**
	 * EditViewPreSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function editViewPreSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$response = ['result' => true];
		$values = [];
		foreach (self::FIELDS[$recordModel->getModuleName()] as $fieldName) {
			$fieldModel = $recordModel->getModule()->getFieldByName($fieldName);
			if ($fieldModel->isViewable() && ($value = $recordModel->get($fieldName))) {
				$values[] = $value;
			}
		}
		foreach (self::FIELDS as $moduleName => $fields) {
			$queryGenerator = new \App\QueryGenerator($moduleName);
			$queryGenerator->setStateCondition('All');
			$queryGenerator->setFields(['id'])->permissions = false;
			if ($moduleName === $recordModel->getModuleName() && $recordModel->getId()) {
				$queryGenerator->addCondition('id', $recordModel->getId(), 'n');
			}
			foreach ($fields as $fieldName) {
				$queryGenerator->addCondition($fieldName, $values, 'e', false);
			}
			if ($queryGenerator->createQuery()->exists()) {
				$response = [
					'result' => false,
					'hoverField' => 'vat_id',
					'message' => App\Language::translateArgs('LBL_DUPLICATE_VAT_ID', $recordModel->getModuleName(), \App\Language::translate($moduleName, $moduleName)),
					'type' => 'confirm',
					'hash' => hash('sha256', implode('|', $recordModel->getData()))
				];
				break;
			}
		}
		return $response;
	}
}
