<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

require_once 'modules/com_vtiger_workflow/VTEventHandler.inc';
require_once 'modules/Emails/mail.php';
require_once 'modules/HelpDesk/HelpDesk.php';

class ModCommentsHandler extends VTEventHandler
{

	public function handleEvent($eventName, $data)
	{

		if ($eventName == 'vtiger.entity.beforesave') {
			// Entity is about to be saved, take required action
		}

		if ($eventName == 'vtiger.entity.aftersave') {
			$db = PearDatabase::getInstance();

			$relatedToId = $data->get('related_to');
			if ($relatedToId) {
				/*
				  $moduleName = getSalesEntityType($relatedToId);
				  $focus = CRMEntity::getInstance($moduleName);
				  $focus->retrieve_entity_info($relatedToId, $moduleName);
				  $focus->id = $relatedToId;
				  $fromPortal = $data->get('from_portal');
				  if ($fromPortal) {
				  $focus->column_fields['from_portal'] = $fromPortal;
				  }
				  $entityData = VTEntityData::fromCRMEntity($focus);

				  $wfs = new VTWorkflowManager($db);
				  $relatedToEventHandler = new VTWorkflowEventHandler();
				  $relatedToEventHandler->workflows = $wfs->getWorkflowsForModuleSupportingComments($entityData->getModuleName());

				  $wsId = vtws_getWebserviceEntityId($entityData->getModuleName(), $entityData->getId());
				  $fromPortal = $entityData->get('from_portal');

				  $util = new VTWorkflowUtils();
				  $entityCache = new VTEntityCache($util->adminUser());

				  $entityCacheData = $entityCache->forId($wsId);
				  $entityCacheData->set('from_portal', $fromPortal);
				  $entityCache->cache[$wsId] = $entityCacheData;

				  $relatedToEventHandler->handleEvent($eventName, $entityData, $entityCache);
				  $util->revertUser();
				  /*
				  $eventModuleName = $data->getModuleName();
				  if ($eventModuleName == 'ModComments') {
				  $shownerid[] = $focus->column_fields['shownerid'];
				  $shownerid[] = $focus->column_fields['assigned_user_id'];
				  $data->set('shownerid',$shownerid);
				  }
				 */
			}
		}
	}
}
