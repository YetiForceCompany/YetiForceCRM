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

class HelpDesk_Record_Model extends Vtiger_Record_Model
{

	/**
	 * Function to get URL for Convert FAQ
	 * @return string
	 */
	public function getConvertFAQUrl()
	{
		return "index.php?module=" . $this->getModuleName() . "&action=ConvertFAQ&record=" . $this->getId();
	}

	/**
	 * Function to get Comments List of this Record
	 * @return string
	 */
	public function getCommentsList()
	{
		$db = PearDatabase::getInstance();
		$commentsList = array();

		$result = $db->pquery("SELECT commentcontent AS comments FROM vtiger_modcomments WHERE related_to = ?", array($this->getId()));
		$numOfRows = $db->num_rows($result);

		for ($i = 0; $i < $numOfRows; $i++) {
			array_push($commentsList, $db->query_result($result, $i, 'comments'));
		}

		return $commentsList;
	}

	public static function updateTicketRangeTimeField($recordModel, $updateFieldImmediately = false)
	{
		if (!$recordModel->isNew() && ($recordModel->getPreviousValue('ticketstatus') || $updateFieldImmediately)) {
			$currentDate = date('Y-m-d H:i:s');
			if (in_array($recordModel->get('ticketstatus'), ['Closed', 'Rejected'])) {
				$currentDate = null;
			}
			\App\Db::getInstance()->createCommand()
				->update('vtiger_troubletickets', [
					'response_time' => $currentDate,
					], ['ticketid' => $recordModel->getId()])
				->execute();
		}
		$closedTime = $recordModel->get('closedtime');
		if (!empty($closedTime) && $recordModel->has('report_time')) {
			$timeMinutesRange = round(vtlib\Functions::getDateTimeMinutesDiff($recordModel->get('createdtime'), $closedTime));
			if (!empty($timeMinutesRange)) {
				App\Db::getInstance()->createCommand()
					->update('vtiger_troubletickets', ['report_time' => $timeMinutesRange], ['ticketid' => $recordModel->getId()])
					->execute();
			}
		}
	}

	public function getActiveServiceContracts()
	{
		$query = (new \App\Db\Query())->from('vtiger_servicecontracts')
			->innerJoin('vtiger_crmentity', 'vtiger_servicecontracts.servicecontractsid = vtiger_crmentity.crmid')
			->where(['deleted' => 0, 'contract_status' => 'In Progress', 'sc_related_to' => $this->get('parent_id')]);
		\App\PrivilegeQuery::getConditions($query, 'ServiceContracts');
		return $query->all();
	}

	/**
	 * Function to save record
	 */
	public function saveToDb()
	{
		parent::saveToDb();
		$forModule = AppRequest::get('return_module');
		$forCrmid = AppRequest::get('return_id');
		if (AppRequest::get('return_action') && $forModule && $forCrmid && $forModule === 'ServiceContracts') {
			CRMEntity::getInstance($forModule)->save_related_module($forModule, $forCrmid, AppRequest::get('module'), $this->getId());
		}
	}
}
