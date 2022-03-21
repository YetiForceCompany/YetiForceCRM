<?php

/**
 * Files clean handler class.
 *
 * @package Handler
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Vtiger_Files_Handler class.
 */
class Vtiger_Files_Handler
{
	/**
	 * EntityAfterSave function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		foreach ($recordModel->getModule()->getFieldsByType(['image', 'multiImage', 'multiAttachment'], true) as $fieldName => $fieldModel) {
			$currentData = [];
			if ($recordModel->get($fieldName) && ($recordModel->isNew() || false !== $recordModel->getPreviousValue($fieldName))) {
				$currentData = \App\Fields\File::parse(\App\Json::decode($recordModel->get($fieldName)));
				\App\Fields\File::cleanTemp(array_keys($currentData));
			}
			if ($previousValue = $recordModel->getPreviousValue($fieldName)) {
				$previousData = \App\Json::decode($previousValue);
				foreach ($previousData as $item) {
					if (!isset($currentData[$item['key']])) {
						\App\Fields\File::cleanTemp($item['key']);
						\App\Fields\File::loadFromInfo(['path' => $item['path']])->delete();
					}
				}
			}
		}
	}
}
