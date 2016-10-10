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

class ProjectTaskHandler extends VTEventHandler
{

	public function handleEvent($eventName, $data)
	{
		$moduleName = $data->getModuleName();
		if ($eventName == 'vtiger.entity.aftersave.final' && $moduleName == 'ProjectTask') {
			$recordId = $data->getId();
			if ($data->isNew()) {
				$recordModel = Vtiger_Module_Model::getInstance('ProjectMilestone');
				$recordModel->updateProgressMilestone($data->get('projectmilestoneid'));
			} else {
				vimport('include.events.VTEntityDelta');
				$vtEntityDelta = new VTEntityDelta();
				$delta = $vtEntityDelta->getEntityDelta($moduleName, $recordId, true);
				foreach ($delta as $name => $value) {
					if ($name == 'projectmilestoneid' || $name == 'estimated_work_time' || $name == 'projecttaskprogress') {
						$recordModel = Vtiger_Module_Model::getInstance('ProjectMilestone');
						if ($name == 'projectmilestoneid') {
							$recordModel->updateProgressMilestone($value['currentValue']);
							$recordModel->updateProgressMilestone($value['oldValue']);
						} else {
							$recordModel->updateProgressMilestone($data->get('projectmilestoneid'));
						}
					}
				}
			}
		} elseif ($eventName == 'vtiger.entity.afterdelete' && $moduleName == 'ProjectTask') {
			$recordModel = Vtiger_Module_Model::getInstance('ProjectMilestone');
			$recordModel->updateProgressMilestone($data->get('projectmilestoneid'));
		} elseif ($eventName == 'vtiger.entity.afterrestore' && $moduleName == 'ProjectTask') {
			$recordModel = Vtiger_Module_Model::getInstance('ProjectMilestone');
			$recordModel->updateProgressMilestone($data->get('projectmilestoneid'));
		}
	}
}
