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
	 * @return <String>
	 */
	public function getConvertFAQUrl()
	{
		return "index.php?module=" . $this->getModuleName() . "&action=ConvertFAQ&record=" . $this->getId();
	}

	/**
	 * Function to get Comments List of this Record
	 * @return <String>
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

	public static function updateTicketRangeTimeField($entityData, $updateFieldImmediately = false)
	{
		$db = PearDatabase::getInstance();
		$ticketId = $entityData->getId();
		$moduleName = $entityData->getModuleName();
		$status = 'ticketstatus';
		if (class_exists('VTEntityDelta')) {
			$vtEntityDelta = new VTEntityDelta();
			$delta = $vtEntityDelta->getEntityDelta($moduleName, $entityData->getId(), true);
		}
		$currentDate = date('Y-m-d H:i:s');
		if (!$entityData->isNew() && ((is_array($delta) && !empty($delta[$status])) || $updateFieldImmediately)) {
			if (in_array($entityData->get($status), ['Closed', 'Rejected'])) {
				$db->pquery('UPDATE vtiger_troubletickets SET `response_time` = NULL WHERE ticketid = ?', [$ticketId]);
			} else {
				$db->update('vtiger_troubletickets', ['response_time' => $currentDate], 'ticketid = ?', [$ticketId]);
			}
		}
		$closedTime = vtlib\Functions::getSingleFieldValue('vtiger_crmentity', 'closedtime', 'crmid', $ticketId);
		if (!empty($closedTime) && array_key_exists('report_time', $entityData->getData())) {
			$timeMinutesRange = round(vtlib\Functions::getDateTimeMinutesDiff($entityData->get('createdtime'), $closedTime));
			if (!empty($timeMinutesRange)) {
				$db->update('vtiger_troubletickets', ['report_time' => $timeMinutesRange], 'ticketid = ?', [$ticketId]);
			}
		}
	}

	public function getActiveServiceContracts()
	{
		$db = PearDatabase::getInstance();
		$sql = 'SELECT * FROM vtiger_servicecontracts INNER JOIN vtiger_crmentity ON vtiger_servicecontracts.servicecontractsid = vtiger_crmentity.crmid WHERE deleted = ? && contract_status = ? && sc_related_to = ?';
		$instance = CRMEntity::getInstance('ServiceContracts');
		$securityParameter = $instance->getUserAccessConditionsQuerySR('ServiceContracts', Users_Record_Model::getCurrentUserModel());
		if ($securityParameter != '')
			$sql.= $securityParameter;

		$result = $db->pquery($sql, [0, 'In Progress', $this->get('parent_id')]);
		$rows = [];
		while ($row = $db->getRow($result)) {
			$rows[] = $row;
		}
		return $rows;
	}
}
