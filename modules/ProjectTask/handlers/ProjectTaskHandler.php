<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class ProjectTask_ProjectTaskHandler_Handler
{

	/**
	 * EntityAfterSave handler function
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		if ($recordModel->isNew()) {
			Vtiger_Module_Model::getInstance('ProjectMilestone')->updateProgressMilestone($recordModel->get('projectmilestoneid'));
		} else {
			$delta = $recordModel->getPreviousValue();
			foreach ($delta as $name => &$value) {
				if ($name === 'projectmilestoneid' || $name === 'estimated_work_time' || $name === 'projecttaskprogress') {
					$moduledModel = Vtiger_Module_Model::getInstance('ProjectMilestone');
					if ($name === 'projectmilestoneid') {
						$moduledModel->updateProgressMilestone($recordModel->get($name));
						$moduledModel->updateProgressMilestone($value);
					} else {
						$moduledModel->updateProgressMilestone($recordModel->get('projectmilestoneid'));
					}
				}
			}
		}
	}

	/**
	 * EntityAfterDelete handler function
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterDelete(App\EventHandler $eventHandler)
	{
		Vtiger_Module_Model::getInstance('ProjectMilestone')->updateProgressMilestone($eventHandler->getRecordModel()->get('projectmilestoneid'));
	}

	/**
	 * EntityAfterRestore handler function
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterRestore(App\EventHandler $eventHandler)
	{
		Vtiger_Module_Model::getInstance('ProjectMilestone')->updateProgressMilestone($eventHandler->getRecordModel()->get('projectmilestoneid'));
	}
}
