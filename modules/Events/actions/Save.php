<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Events_Save_Action extends Calendar_Save_Action
{

	/**
	 * Function to save record
	 * @param Vtiger_Request $request - values of the record
	 * @return Vtiger_Record_Model - record Model of saved record
	 */
	public function saveRecord(Vtiger_Request $request)
	{
		$recordModel = $this->getRecordModelFromRequest($request);
		$recordModel->save();
		if ($request->get('relationOperation')) {
			$parentModuleName = $request->get('sourceModule');
			$parentModuleModel = Vtiger_Module_Model::getInstance($parentModuleName);
			$parentRecordId = $request->get('sourceRecord');
			$relatedModule = $recordModel->getModule();
			if ($relatedModule->getName() == 'Events') {
				$relatedModule = Vtiger_Module_Model::getInstance('Calendar');
			}
			$relatedRecordId = $recordModel->getId();

			$relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModule);
			$relationModel->addRelation($parentRecordId, $relatedRecordId);
		}

		if (!AppRequest::isEmpty('recurringtype') && AppRequest::get('recurringtype') !== '--None--') {
			vimport('~modules/Calendar/RepeatEvents.php');
			$focus = CRMEntity::getInstance($recordModel->getModuleName());
			$focus->column_fields = $recordModel->getData();
			Calendar_RepeatEvents::repeatFromRequest($focus);
		}
		return $recordModel;
	}
}
