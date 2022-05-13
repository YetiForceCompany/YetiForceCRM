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

/**
 * Vtiger ListView Model Class.
 */
class Calendar_ListView_Model extends Vtiger_ListView_Model
{
	/**
	 * Function to get the list view entries.
	 *
	 * @param Vtiger_Paging_Model $pagingModel
	 *
	 * @return array - Associative array of record id mapped to Vtiger_Record_Model instance
	 */
	public function getListViewEntries(Vtiger_Paging_Model $pagingModel)
	{
		$queryGenerator = $this->getQueryGenerator();
		$queryGenerator->setField('visibility')->setField('assigned_user_id')->setField('activitystatus');
		$queryGenerator->setConcatColumn('date_start', "CONCAT(vtiger_activity.date_start, ' ', vtiger_activity.time_start)");
		$queryGenerator->setConcatColumn('due_date', "CONCAT(vtiger_activity.due_date, ' ', vtiger_activity.time_end)");

		return parent::getListViewEntries($pagingModel);
	}
}
