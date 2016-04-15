<?php

/**
 * Assets Renewal Handler Class
 * @package YetiForce.Handler
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Assets_Renewal_Handler extends VTEventHandler
{

	function handleEvent($eventName, $entityData)
	{
		$moduleName = $entityData->getModuleName();
		if ($eventName == 'vtiger.entity.beforesave.final' && $moduleName == 'Assets') {
			$recordId = $entityData->getId();

			$recordModel = Vtiger_Record_Model::getInstanceByEntity($entityData->focus, $recordId);
			$entityData->set('assets_renew', $recordModel->getRenewalValue());
		}
	}
}
