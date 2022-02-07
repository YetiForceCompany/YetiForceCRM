<?php
/**
 * Duplicate product ean handler.
 *
 * @package Handler
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Products_DuplicateEan_Handler class.
 */
class Products_DuplicateEan_Handler
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
		$fieldModel = $recordModel->getModule()->getFieldByName('ean');
		if ($fieldModel->isViewable() && ($ean = $recordModel->get('ean'))) {
			$queryGenerator = new \App\QueryGenerator($recordModel->getModuleName());
			$queryGenerator->setStateCondition('All');
			$queryGenerator->setFields(['id'])->permissions = false;
			$queryGenerator->addCondition($fieldModel->getName(), $ean, 'e');
			if ($recordModel->getId()) {
				$queryGenerator->addCondition('id', $recordModel->getId(), 'n');
			}
			if ($queryGenerator->createQuery()->exists()) {
				$response = [
					'result' => false,
					'hoverField' => 'ean',
					'message' => App\Language::translate('LBL_DUPLICATE_EAN', $recordModel->getModuleName())
				];
			}
		}
		return $response;
	}
}
