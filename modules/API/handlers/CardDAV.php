<?php

/**
 * Api CardDAV Handler Class
 * @package YetiForce.Handlers
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class API_CardDAV_Handler extends VTEventHandler
{

	public function handleEvent($eventName, $entityData)
	{
		if ($eventName == 'vtiger.entity.aftersave.final') {
			$adb = PearDatabase::getInstance();
			$log = LoggerManager::getInstance();
			$recordId = $entityData->getId();
			$moduleName = $entityData->getModuleName();
			$isNew = $entityData->isNew();
			if (!$isNew && $moduleName == 'Contacts') {
				$updateRecord = false;
				$vtEntityDelta = new VTEntityDelta();
				$delta = $vtEntityDelta->getEntityDelta($moduleName, $recordId, true);
				$delta = array_change_key_case($delta, CASE_LOWER);
				$fields = array('firstname', 'lastname', 'email', 'secondary_email', 'phone', 'mobile');
				foreach ($fields as $val) {
					if (isset($delta[$val])) {
						$updateRecord = true;
						break;
					}
				}
				if ($updateRecord) {
					$adb->pquery('UPDATE vtiger_contactdetails SET dav_status = ? WHERE contactid = ?', array(1, $recordId));
				}
			}
			if (!$isNew && $moduleName == 'OSSEmployees') {
				$updateRecord = false;
				$vtEntityDelta = new VTEntityDelta();
				$delta = $vtEntityDelta->getEntityDelta($moduleName, $recordId, true);
				$delta = array_change_key_case($delta, CASE_LOWER);
				$fields = array('name', 'last_name', 'business_phone', 'business_mail', 'private_phone', 'private_mail');
				foreach ($fields as $val) {
					if (isset($delta[$val])) {
						$updateRecord = true;
						break;
					}
				}
				if ($updateRecord) {
					$adb->pquery('UPDATE vtiger_ossemployees SET dav_status = ? WHERE ossemployeesid = ?', array(1, $recordId));
				}
			}
		}
	}
}
