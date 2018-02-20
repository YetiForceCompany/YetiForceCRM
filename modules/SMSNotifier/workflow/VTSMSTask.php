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

class VTSMSTask extends VTTask
{
	public $executeImmediately = true;

	public function getFieldNames()
	{
		return ['content', 'sms_recepient'];
	}

	/**
	 * Execute task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel)
	{
		if (SMSNotifier_Module_Model::checkServer()) {
			$textParser = \App\TextParser::getInstanceByModel($recordModel);
			$content = $textParser->setContent($this->content)->parse()->getContent();
			$recepient = $textParser->setContent($this->sms_recepient)->parse()->getContent();
			$recepients = explode(',', $recepient);
			$toNumbers = [];
			foreach ($recepients as $toNumber) {
				$parseNumber = preg_replace_callback('/[^\d]/s', function ($m) {
					return '';
				}, $toNumber);
				if (!empty($parseNumber) && !in_array($parseNumber, $toNumbers)) {
					$toNumbers[] = $parseNumber;
				}
			}
			if ($toNumbers) {
				SMSNotifier_Record_Model::sendSMS($content, $toNumbers, [$recordModel->getId()], $recordModel->getModuleName());
			}
		}
	}
}
