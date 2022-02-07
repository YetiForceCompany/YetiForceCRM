<?php

/**
 * Queue handler file.
 *
 * @package   Handler
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Queue handler class.
 */
class Queue_Queue_Handler
{
	/**
	 * EntityAfterSave handler function.
	 *
	 * @param \App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		if ('PLL_ACCEPTED' === $recordModel->get('queue_status')) {
			(new \App\BatchMethod(['method' => 'Queue_Queue_Handler::updateData', 'params' => [$recordModel->getId()]]))->save();
		}
	}

	/**
	 * Update data from record ID.
	 *
	 * @param int $recordId
	 */
	public static function updateData(int $recordId)
	{
		$recordModel = \Vtiger_Record_Model::getInstanceById($recordId, 'Queue');
		$data = \App\Json::decode($recordModel->get('changes')) ?: [];
		if ($data) {
			$relatedRecordId = $data['record'];
			$relatedModule = $data['module'];
			$changes = $data['changes'] ?: [];
			if (\App\Record::isExists($relatedRecordId, $relatedModule)) {
				$relatedRecordModel = \Vtiger_Record_Model::getInstanceById($relatedRecordId, $relatedModule);
				foreach ($changes as $fieldName => $value) {
					$fieldModel = $relatedRecordModel->getField($fieldName);
					if ($fieldModel && $fieldModel->isWritable()) {
						$relatedRecordModel->set($fieldName, $value);
					}
				}
				if ($relatedRecordModel->getPreviousValue()) {
					$relatedRecordModel->save();
				}
			}
		}
		$recordModel->set('queue_status', 'PLL_COMPLETED')->save();
	}
}
