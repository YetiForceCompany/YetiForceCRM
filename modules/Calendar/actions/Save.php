<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce Sp. z o.o.
 * *********************************************************************************** */

class Calendar_Save_Action extends Vtiger_Save_Action
{
	/**
	 * Function to save record.
	 *
	 * @param \App\Request $request Values of the record
	 *
	 * @throws \yii\db\Exception
	 *
	 * @return \Vtiger_Record_Model Record Model of saved record
	 */
	public function saveRecord(\App\Request $request)
	{
		$recordModel = parent::saveRecord($request);
		$data = $recordModel->getData();
		if ($request->getBoolean('reapeat')) {
			$recurringEvents = Calendar_RecuringEvents_Model::getInstanceFromRequest($request);
			if ($request->isEmpty('record') || (!$recordModel->isNew() && $recordModel->isEmpty('followup'))) {
				App\Db::getInstance()->createCommand()->update('vtiger_activity', ['followup' => $recordModel->getId()], ['activityid' => $recordModel->getId()])->execute();
				$data['followup'] = $recordModel->getId();
			} elseif (empty($data['followup'])) {
				$data['followup'] = $recordModel->getId();
			}
			$recurringEvents->setChanges($recordModel->getPreviousValue());
			$recurringEvents->setData($data);
			$recurringEvents->save();
		}
		return $recordModel;
	}

	/**
	 * Function to get the record model based on the request parameters.
	 *
	 * @param \App\Request $request
	 *
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	protected function getRecordModelFromRequest(\App\Request $request)
	{
		$recordModel = parent::getRecordModelFromRequest($request);
		if (!$request->isEmpty('typeSaving') && $request->getInteger('typeSaving') === Calendar_RecuringEvents_Model::UPDATE_THIS_EVENT) {
			$recordModel->set('recurrence', $recordModel->getPreviousValue('recurrence'));
		}
		return $recordModel;
	}
}
