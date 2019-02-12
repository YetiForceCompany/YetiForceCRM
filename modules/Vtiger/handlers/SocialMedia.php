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
		if (\App\SocialMedia::isEnableForModule($recordModel->getModuleName()) && !$recordModel->isNew()) {
			$accountsToRemove = [];
			foreach (Vtiger_SocialMedia_Model::getInstanceByRecordModel($recordModel)->getAllColumnName() as $uiType => $column) {
				$preValue = $recordModel->getPreviousValue($column);
				if ($preValue !== false && !empty($preValue)) {
					$accountsToRemove[$uiType][] = $preValue;
				}
			}
			foreach ($accountsToRemove as $uiType => $row) {
				\App\SocialMedia::removeMass($uiType, $row);
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
			$accountsToRemove = [];
			foreach (Vtiger_SocialMedia_Model::getInstanceByRecordModel($recordModel)->getAllColumnName() as $uiType => $column) {
				$accountsToRemove[$uiType][] = $recordModel->get($column);
			}
			foreach ($accountsToRemove as $uiType => $row) {
				\App\SocialMedia::removeMass($uiType, $row);
			}
		}
	}
}
