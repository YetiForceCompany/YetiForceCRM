<?php

class Project_ProjectHandler_Handler
{
	/**
	 * EntityAfterSave handler function.
	 *
	 * @param \App\EventHandler $eventHandler
	 */
	public function entityAfterSave(\App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		\App\DebugerEx::log("entityAfterSave({$recordModel->getId()}): isNew = ", $recordModel->isNew());
		if ($recordModel->isNew()) {
			Vtiger_Module_Model::getInstance('Project')->updateProgress($recordModel->getId());
		} else {
			$delta = $recordModel->getPreviousValue();
			\App\DebugerEx::log($delta);
			foreach ($delta as $name => $value) {
				if ($name === 'parentid') {
					\App\DebugerEx::log('1) entityAfterSave:', $recordModel->get($name), $value);
					$projectModel = Vtiger_Module_Model::getInstance('Project');
					if (!empty($recordModel->get($name))) {
						$projectModel->updateProgress($recordModel->get($name));
					}
					if (!empty($value)) {
						$projectModel->updateProgress($value);
					}
					$projectModel->updateProgress($recordModel->getId());
				}
			}
		}
	}

	/**
	 * EntityChangeState handler function.
	 *
	 * @param \App\EventHandler $eventHandler
	 */
	public function entityChangeState(\App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		if ($recordModel->hasParent()) {
			Vtiger_Module_Model::getInstance('Project')->updateProgress($recordModel->get('parentid'));
		}
	}
}
