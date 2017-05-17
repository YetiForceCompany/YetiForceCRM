<?php
/**
 * Attachments Handler Class
 * @package YetiForce.Handler
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Attachments handler class
 */
class Vtiger_Attachments_Handler
{

	/**
	 * EntityAfterSave function
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$fields = $recordModel->getModule()->getFieldsByUiType(311);
		if ($fields) {
			foreach ($fields as $fieldName => $fieldModel) {
				$previousValue = $recordModel->getPreviousValue($fieldName);
				if ($previousValue !== false || (!empty($recordModel->get($fieldName)) && $recordModel->isNew())) {
					\Vtiger_Files_Model::updateStatus($previousValue, $recordModel->get($fieldName), $recordModel->getId(), $fieldModel->getId());
				}
			}
		}
	}
}
