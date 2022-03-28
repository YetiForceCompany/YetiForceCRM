<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Calendar_SaveAjax_Action extends Vtiger_SaveAjax_Action
{
	/** {@inheritdoc} */
	public function saveRecord(App\Request $request)
	{
		parent::saveRecord($request);
		if ($request->getBoolean('postponed') && ($relId = $this->record->get('followup')) && \App\Privilege::isPermitted($this->record->getModuleName(), 'ActivityPostponed', $relId)) {
			$relRecord = Vtiger_Record_Model::getInstanceById($relId, $this->record->getModuleName());
			$relRecord->set('activitystatus', 'PLL_POSTPONED');
			$relRecord->save();
		}
	}

	/** {@inheritdoc} */
	public function getRecordModelFromRequest(App\Request $request)
	{
		if (!$request->isEmpty('record') && !$request->has('field')) {
			$className = Vtiger_Loader::getComponentClassName('Action', 'Save', $request->getModule());
			$recordModel = (new $className())->getRecordModelFromRequest($request);
		} else {
			$recordModel = parent::getRecordModelFromRequest($request);
		}
		return $recordModel;
	}
}
