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
		$data = $recordModel->getData();
		$recordModel->save();
		$recordModel->addRelationOperation($request);

		if ($request->get('reapeat') === 'on') {
			$recurringEvents = Events_RecuringEvents_Model::getInstanceFromRequest($request);
			if ($request->isEmpty('record')) {
				App\Db::getInstance()->createCommand()->update('vtiger_activity', ['followup' => $recordModel->getId()], ['activityid' => $recordModel->getId()])->execute();
				$data['followup'] = $recordModel->getId();
			} else if (empty($data['followup'])) {
				$data['followup'] = $recordModel->getId();
			}
			$recurringEvents->setChanges($recordModel->getPreviousValue());
			$recurringEvents->setData($data);
			$recurringEvents->save();
		}
		return $recordModel;
	}

	/**
	 * Function to get the record model based on the request parameters
	 * @param Vtiger_Request $request
	 * @return Vtiger_Record_Model
	 */
	public function getRecordModelFromRequest(Vtiger_Request $request)
	{
		$recordModel = parent::getRecordModelFromRequest($request);
		if ((int) $request->get('typeSaving') === Events_RecuringEvents_Model::UPDATE_THIS_EVENT) {
			$recordModel->set('recurrence', $recordModel->getPreviousValue('recurrence'));
		}
		return $recordModel;
	}
}
