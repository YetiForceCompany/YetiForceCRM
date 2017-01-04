<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */
require_once('modules/SMSNotifier/SMSNotifier.php');

class VTSMSTask extends VTTask
{

	public $executeImmediately = true;

	public function getFieldNames()
	{
		return array('content', 'sms_recepient');
	}

	/**
	 * Execute task
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel)
	{
		if (SMSNotifier::checkServer()) {
			$textParser = \App\TextParser::getInstanceByModel($recordModel);
			$content = $textParser->setContent($this->content)->parse()->getContent();
			$recepient = $textParser->setContent($this->sms_recepient)->parse()->getContent();
			$recepients = explode(',', $recepient);
			/** Pickup only non-empty numbers */
			$tonumbers = [];
			foreach ($recepients as &$tonumber) {
				if (!empty($tonumber)) {
					$tonumbers[] = $tonumber;
				}
			}
			SMSNotifier::sendsms($content, $tonumbers, \App\User::getCurrentUserId(), $recordModel->getId(), $recordModel->getModuleName());
		}
	}
}
