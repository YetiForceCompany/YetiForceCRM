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
		if (\App\SocialMedia::isEnableForModule($recordModel->getModuleName() && !$recordModel->isNew())) {
			$columnsToRemove = [];
			foreach (Vtiger_SocialMedia_Model::getInstanceByRecordModel($recordModel)->getAllColumnName() as $uiType => $column) {
				if ($recordModel->getPreviousValue($column) !== false) {
					$columnsToRemove[][$uiType] = $column;
					/*if (!empty($recordModel->getPreviousValue($column)) && empty($recordModel->get($column))) {
						$columnsToRemove[][$uiType] = $column;
					} elseif (!empty($recordModel->getPreviousValue($column)) && !empty($recordModel->get($column))) {
						$columnsToRemove[][$uiType] = $column;
					}*/
				}
			}
			foreach ($columnsToRemove as $column) {
				//\App\SocialMedia::removeAccount($recordModel->getField($column)->getUIType(), $recordModel->getPreviousValue($column));
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
		if (\App\SocialMedia::isEnableForModule($recordModel->getModuleName())) {
			foreach (Vtiger_SocialMedia_Model::getInstanceByRecordModel($recordModel)->getAllColumnName() as $column) {
				\App\SocialMedia::removeAccount($recordModel->getField($column)->getUIType(), $recordModel->get($column));
			}
		}
	}
}
