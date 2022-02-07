<?php
/**
 * Duplicate IGRN handler.
 *
 * @package Handler
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * IGDNC_IgdnExist_Handler class.
 */
class IGRNC_IgrnExist_Handler
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
		$fieldModel = $recordModel->getModule()->getFieldByName('igrnid');
		if ($fieldModel->isViewable() && ($id = $recordModel->get($fieldModel->getName()))) {
			$queryGenerator = new \App\QueryGenerator($recordModel->getModuleName());
			$queryGenerator->setStateCondition('All');
			$queryGenerator->setFields(['id'])->permissions = false;
			$queryGenerator->addCondition($fieldModel->getName(), $id, 'eid');
			if ($recordModel->getId()) {
				$queryGenerator->addCondition('id', $recordModel->getId(), 'n');
			}
			if ($queryGenerator->createQuery()->exists()) {
				$response = [
					'result' => false,
					'hoverField' => $fieldModel->getName(),
					'message' => App\Language::translateArgs('LBL_DUPLICATE_FIELD_VALUE', $recordModel->getModuleName(), App\Language::translate($fieldModel->getFieldLabel(), $recordModel->getModuleName()))
				];
			}
		}
		return $response;
	}
}
