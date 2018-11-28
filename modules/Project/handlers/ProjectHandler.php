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
		if ($recordModel->isNew()) {
			//Vtiger_Module_Model::getInstance('Project')->updateProgress($recordModel->getId());
		} else {
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
