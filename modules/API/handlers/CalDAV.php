<?php

/**
 * Api CalDAV Handler Class
 * @package YetiForce.Handlers
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class API_CalDAV_Handler extends VTEventHandler
{

	public function handleEvent($eventName, $entityData)
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
