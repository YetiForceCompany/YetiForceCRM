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
	public function entityAfterSave(\App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		if (Vtiger_SocialMedia_Model::isEnableForModule($recordModel)) {
			$socialMedia = Vtiger_SocialMedia_Model::getInstanceByRecordModel($recordModel);
			$columns = $socialMedia->getAllColumnName();
			$columnsToAdd = [];
			$columnsToRemove = [];
			if ($recordModel->isNew()) {
				$accountsToAdd = array_filter($columns, function ($column) use ($recordModel) {
					return !empty($recordModel->get($column));
				});
			} else {
				foreach ($columns as $column) {
					if ($recordModel->getPreviousValue($column) !== false) {
						if (empty($recordModel->getPreviousValue($column)) && !empty($recordModel->get($column))) {
							//Adding
							$columnsToAdd[] = $column;
						} elseif (!empty($recordModel->getPreviousValue($column)) && empty($recordModel->get($column))) {
							//Removing
							$columnsToRemove[] = $column;
						} elseif ($recordModel->getPreviousValue($column) != $recordModel->get($column)) {
							//Adding and removing
							$columnsToAdd[] = $column;
							$columnsToRemove[] = $column;
						}
					}
				}
			}
			foreach ($columnsToAdd as $column) {
				\App\SocialMedia\SocialMedia::addAccount($recordModel->get($column), (int) $recordModel->getField($column)->getUIType());
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
	}

	public function checkIsNewAccount(\Vtiger_Record_Model $recordModel)
	{
		//$columns = $socialMedia->getAllColumnName();
	}
}
