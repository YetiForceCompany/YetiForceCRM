<?php

/**
 * Social Media Handler Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Vtiger_SocialMedia_Handler
{
	/**
	 * EntityAfterSave handler function.
	 *
	 * @param \App\EventHandler $eventHandler
	 */
	public function entityBeforeSave(\App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		if (Vtiger_SocialMedia_Model::isEnableForModule($recordModel) && !$recordModel->isNew()) {
			$columns = Vtiger_SocialMedia_Model::getInstanceByRecordModel($recordModel)->getAllColumnName();
			$columnsToRemove = [];
			foreach ($columns as $column) {
				if ($recordModel->getPreviousValue($column) !== false) {
					if (!empty($recordModel->getPreviousValue($column)) && empty($recordModel->get($column))) {
						$columnsToRemove[] = $column;
					} elseif (!empty($recordModel->getPreviousValue($column)) && !empty($recordModel->get($column))) {
						$columnsToRemove[] = $column;
					}
				}
			}
			foreach ($columnsToRemove as $column) {
				\App\SocialMedia\SocialMedia::removeAccount($recordModel->getField($column)->getUIType(), $recordModel->getPreviousValue($column));
			}
		}
	}

	/**
	 * EntityBeforeDelete handler function.
	 *
	 * @param \App\EventHandler $eventHandler
	 *
	 * @return bool
	 */
	public function entityBeforeDelete(\App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		if (Vtiger_SocialMedia_Model::isEnableForModule($recordModel)) {
			$columns = Vtiger_SocialMedia_Model::getInstanceByRecordModel($recordModel)->getAllColumnName();
			foreach ($columns as $column) {
				\App\SocialMedia\SocialMedia::removeAccount($recordModel->getField($column)->getUIType(), $recordModel->get($column));
			}
		}
	}
}
