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

class API_CalDAV_Handler extends VTEventHandler
{

	function handleEvent($eventName, $entityData)
	{
		if ($eventName == 'vtiger.entity.aftersave.final') {
			$adb = PearDatabase::getInstance();
			$log = LoggerManager::getInstance();
			$recordId = $entityData->getId();
			$moduleName = $entityData->getModuleName();
			$isNew = $entityData->isNew();
			if (!$isNew && in_array($moduleName, ['Events', 'Calendar'])) {
				$adb->pquery('UPDATE vtiger_activity SET dav_status = ? WHERE activityid = ?', array(1, $recordId));
			}
		}
	}
}
