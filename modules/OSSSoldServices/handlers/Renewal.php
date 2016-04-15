<?php

/**
 * OSSSoldServices Renewal Handler Class
 * @package YetiForce.Handler
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSSoldServices_Renewal_Handler extends VTEventHandler
{

	function handleEvent($eventName, $entityData)
	{
		$moduleName = $entityData->getModuleName();
		if ($eventName == 'vtiger.entity.beforesave.final' && $moduleName == 'OSSSoldServices') {
			$recordId = $entityData->getId();

			$recordModel = Vtiger_Record_Model::getInstanceByEntity($entityData->focus, $recordId);
			$entityData->set('osssoldservices_renew', $recordModel->getRenewalValue());
		}
	}
}
