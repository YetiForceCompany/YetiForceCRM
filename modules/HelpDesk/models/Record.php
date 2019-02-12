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
	 * Function to get URL for Convert FAQ.
	 *
	 * @return string
	 */
	public function getConvertFAQUrl()
	{
		return 'index.php?module=' . $this->getModuleName() . '&action=ConvertFAQ&record=' . $this->getId();
	}

	/**
	 * Function to get Comments List of this Record.
	 *
	 * @return string
	 */
	public function getCommentsList()
	{
		return (new \App\Db\Query())
			->select(['comments' => 'commentcontent'])
			->from('vtiger_modcomments')
			->where(['related_to' => $this->getId()])->column();
	}

	/**
	 * Update ticket range time field.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 * @param bool                $updateFieldImmediately
	 */
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
			$timeMinutesRange = round(\App\Fields\Date::getDiff($recordModel->get('createdtime'), $closedTime, 'minutes'));
			if (!empty($timeMinutesRange)) {
				App\Db::getInstance()->createCommand()
					->update('vtiger_troubletickets', ['report_time' => $timeMinutesRange], ['ticketid' => $recordModel->getId()])
					->execute();
			}
		}
	}

	/**
	 * Get active service contracts.
	 *
	 * @return array
	 */
	public function getActiveServiceContracts()
	{
		$query = (new \App\Db\Query())->from('vtiger_servicecontracts')
			->innerJoin('vtiger_crmentity', 'vtiger_servicecontracts.servicecontractsid = vtiger_crmentity.crmid')
			->where(['deleted' => 0, 'contract_status' => 'In Progress', 'sc_related_to' => $this->get('parent_id')])
			->orderBy(['due_date' => SORT_ASC]);
		\App\PrivilegeQuery::getConditions($query, 'ServiceContracts');
		return $query->all();
	}

	/**
	 * Function to save record.
	 */
	public function saveToDb()
	{
		parent::saveToDb();
		$forModule = \App\Request::_get('return_module');
		$forCrmid = \App\Request::_get('return_id');
		if (\App\Request::_get('return_action') && $forModule && $forCrmid && $forModule === 'ServiceContracts') {
			CRMEntity::getInstance($forModule)->saveRelatedModule($forModule, $forCrmid, \App\Request::_get('module'), $this->getId());
		}
	}
}
