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

class Calendar_Save_Action extends Vtiger_Save_Action
{
	/**
	 * Function to save record.
	 *
	 * @param \App\Request $request Values of the record
	 *
	 * @throws \yii\db\Exception
	 */
	public function saveRecord(App\Request $request)
	{
		parent::saveRecord($request);
		$data = $this->record->getData();
		if ($request->getBoolean('reapeat')) {
			$recurringEvents = Calendar_RecuringEvents_Model::getInstanceFromRequest($request);
			if ($request->isEmpty('record') || (!$this->record->isNew() && $this->record->isEmpty('followup'))) {
				App\Db::getInstance()->createCommand()->update('vtiger_activity', ['followup' => $this->record->getId()], ['activityid' => $this->record->getId()])->execute();
				$data['followup'] = $this->record->getId();
			} elseif (empty($data['followup'])) {
				$data['followup'] = $this->record->getId();
			}
			$recurringEvents->setChanges($this->record->getPreviousValue());
			$recurringEvents->setData($data);
			$recurringEvents->save();
		}
	}

	/**
	 * Function to get the record model based on the request parameters.
	 *
	 * @param \App\Request $request
	 *
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	protected function getRecordModelFromRequest(App\Request $request)
	{
		parent::getRecordModelFromRequest($request);
		if (!$request->isEmpty('typeSaving') && Calendar_RecuringEvents_Model::UPDATE_THIS_EVENT === $request->getInteger('typeSaving')) {
			$this->record->set('recurrence', $this->record->getPreviousValue('recurrence'));
		}
		return $this->record;
	}
}
