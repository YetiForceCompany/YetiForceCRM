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

/**
 * Vtiger ListView Model Class.
 */
class Calendar_ListView_Model extends Vtiger_ListView_Model
{
	public function getBasicLinks()
	{
		$basicLinks = [];
		$moduleModel = $this->getModule();
		$createPermission = \App\Privilege::isPermitted($moduleModel->getName(), 'CreateView');
		if ($createPermission) {
			$basicLinks[] = [
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => 'LBL_ADD_EVENT',
				'linkurl' => $this->getModule()->getCreateEventRecordUrl(),
				'linkclass' => 'modCT_' . $moduleModel->getName(),
				'linkicon' => 'fas fa-plus',
				'showLabel' => 1,
			];
			$basicLinks[] = [
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => 'LBL_ADD_TASK',
				'linkurl' => $this->getModule()->getCreateTaskRecordUrl(),
				'linkclass' => 'modCT_' . $moduleModel->getName(),
				'linkicon' => 'fas fa-plus',
				'showLabel' => 1,
			];
		}

		return $basicLinks;
	}

	/*
	 * Function to give advance links of a module
	 * 	@RETURN array of advanced links
	 */

	public function getAdvancedLinks()
	{
		$moduleModel = $this->getModule();
		$createPermission = \App\Privilege::isPermitted($moduleModel->getName(), 'CreateView') && \App\Privilege::isPermitted($moduleModel->getName(), 'EditView');
		$advancedLinks = [];
		$importPermission = \App\Privilege::isPermitted($moduleModel->getName(), 'Import');
		if ($importPermission && $createPermission) {
			$advancedLinks[] = [
				'linktype' => 'LISTVIEW',
				'linklabel' => 'LBL_IMPORT',
				'linkurl' => 'javascript:Calendar_List_Js.triggerImportAction("' . $moduleModel->getImportUrl() . '")',
				'linkicon' => 'fas fa-download',
			];
		}

		$exportPermission = \App\Privilege::isPermitted($moduleModel->getName(), 'Export');
		if ($exportPermission) {
			$advancedLinks[] = [
				'linktype' => 'LISTVIEW',
				'linklabel' => 'LBL_EXPORT',
				'linkurl' => 'javascript:Calendar_List_Js.triggerExportAction("' . $this->getModule()->getExportUrl() . '")',
				'linkicon' => 'fas fa-upload',
			];
		}

		return $advancedLinks;
	}

	/**
	 * Function to get the list of Mass actions for the module.
	 *
	 * @param array $linkParams
	 *
	 * @return array - Associative array of Link type to List of  Vtiger_Link_Model instances for Mass Actions
	 */
	public function getListViewMassActions($linkParams)
	{
		$moduleModel = $this->getModule();
		$links = Vtiger_Link_Model::getAllByType($moduleModel->getId(), ['LISTVIEWMASSACTION'], $linkParams);

		$massActionLinks = [];
		if ($moduleModel->isPermitted('MassTransferOwnership')) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_TRANSFER_OWNERSHIP',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerTransferOwnership("index.php?module=' . $moduleModel->getName() . '&view=MassActionAjax&mode=transferOwnership")',
				'linkicon' => 'fas fa-user',
			];
		}
		if ($moduleModel->isPermitted('MassActive')) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MASS_ACTIVATE',
				'linkurl' => 'javascript:',
				'dataUrl' => 'index.php?module=' . $moduleModel->getName() . '&action=MassState&state=Active&sourceView=List',
				'linkclass' => 'massRecordEvent',
				'linkicon' => 'fas fa-undo-alt',
			];
		}
		if ($moduleModel->isPermitted('MassArchived')) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MASS_ARCHIVE',
				'linkurl' => 'javascript:',
				'dataUrl' => 'index.php?module=' . $moduleModel->getName() . '&action=MassState&state=Archived&sourceView=List',
				'linkclass' => 'massRecordEvent',
				'linkicon' => 'fas fa-archive',
			];
		}
		if ($moduleModel->isPermitted('MassTrash')) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MASS_MOVE_TO_TRASH',
				'linkurl' => 'javascript:',
				'dataUrl' => 'index.php?module=' . $moduleModel->getName() . '&action=MassState&state=Trash&sourceView=List',
				'linkclass' => 'massRecordEvent',
				'linkicon' => 'fas fa-trash-alt',
			];
		}
		if ($moduleModel->isPermitted('MassDelete')) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MASS_DELETE',
				'linkurl' => 'javascript:',
				'dataUrl' => 'index.php?module=' . $moduleModel->getName() . '&action=MassDelete&sourceView=List',
				'linkclass' => 'massRecordEvent',
				'linkicon' => 'fas fa-eraser',
			];
		}
		foreach ($massActionLinks as $massActionLink) {
			$links['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}

		return $links;
	}

	/**
	 * Function to get the list view entries.
	 *
	 * @param Vtiger_Paging_Model $pagingModel
	 *
	 * @return array - Associative array of record id mapped to Vtiger_Record_Model instance
	 */
	public function getListViewEntries(Vtiger_Paging_Model $pagingModel)
	{
		$queryGenerator = $this->get('query_generator');
		$queryGenerator->setField(['visibility', 'assigned_user_id', 'activitystatus']);
		$queryGenerator->setConcatColumn('date_start', "CONCAT(vtiger_activity.date_start, ' ', vtiger_activity.time_start)");
		$queryGenerator->setConcatColumn('due_date', "CONCAT(vtiger_activity.due_date, ' ', vtiger_activity.time_end)");

		return parent::getListViewEntries($pagingModel);
	}
}
