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

class Documents_ListView_Model extends Vtiger_ListView_Model
{
	public function getAdvancedLinks()
	{
		$moduleModel = $this->getModule();
		$moduleName = $moduleModel->getName();
		$advancedLinks = [];

		if ($moduleModel->isPermitted('Export')) {
			$exportUrl = $this->getModule()->getExportUrl();
			$advancedLinks[] = [
				'linktype' => 'LISTVIEW',
				'linklabel' => 'LBL_EXPORT',
				'linkurl' => "javascript:Vtiger_List_Js.triggerExportAction('$exportUrl')",
				'linkicon' => 'fas fa-upload',
			];
		}

		if ($moduleModel->isPermitted('ExportPdf')) {
			$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'PDF', $moduleName);
			$pdfModel = new $handlerClass();
			$templates = $pdfModel->getActiveTemplatesForModule($moduleName, 'List');
			if (count($templates) > 0) {
				$advancedLinks[] = [
					'linktype' => 'DETAIL_VIEW_ADDITIONAL',
					'linklabel' => \App\Language::translate('LBL_EXPORT_PDF'),
					'linkdata' => ['url' => 'index.php?module=' . $moduleName . '&view=PDF&fromview=List', 'type' => 'modal'],
					'linkclass' => 'js-mass-action',
					'linkicon' => 'fas fa-file-pdf',
					'title' => \App\Language::translate('LBL_EXPORT_PDF'),
				];
			}
		}

		if ($moduleModel->isPermitted('QuickExportToExcel')) {
			$advancedLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_QUICK_EXPORT_TO_EXCEL',
				'linkurl' => "javascript:Vtiger_List_Js.triggerQuickExportToExcel('$moduleName')",
				'linkicon' => 'fas fa-file-excel',
			];
		}
		if ($moduleModel->isPermitted('RecordMappingList')) {
			$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'MappedFields', $moduleName);
			$mfModel = new $handlerClass();
			$templates = $mfModel->getActiveTemplatesForModule($moduleName, 'List');
			if (count($templates) > 0) {
				$advancedLinks[] = [
					'linktype' => 'LISTVIEW',
					'linklabel' => 'LBL_GENERATE_RECORDS',
					'linkurl' => 'javascript:Vtiger_List_Js.triggerGenerateRecords();',
				];
			}
		}
		return $advancedLinks;
	}

	/**
	 * Function to get the list of Mass actions for the module.
	 *
	 * @param <Array> $linkParams
	 *
	 * @return <Array> - Associative array of Link type to List of  Vtiger_Link_Model instances for Mass Actions
	 */
	public function getListViewMassActions($linkParams)
	{
		$moduleModel = $this->getModule();
		$moduleName = $moduleModel->getName();

		$linkTypes = ['LISTVIEWMASSACTION'];
		$links = Vtiger_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);

		//Opensource fix to make documents module mass editable
		$massActionLinks = [];
		if ($moduleModel->isPermitted('MassEdit')) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_EDIT',
				'linkurl' => "javascript:Vtiger_List_Js.triggerMassEdit('index.php?module=$moduleName&view=MassActionAjax&mode=showMassEditForm');",
				'linkicon' => 'fas fa-edit',
			];
		}
		if ($moduleModel->isPermitted('MassMoveDocuments')) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MOVE',
				'linkurl' => "javascript:Documents_List_Js.massMove('index.php?module=$moduleName&view=MoveDocuments');",
				'linkicon' => 'fas fa-folder-open',
			];
		}
		if ($moduleModel->isPermitted('MassTransferOwnership')) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_TRANSFER_OWNERSHIP',
				'linkurl' => "javascript:Vtiger_List_Js.triggerTransferOwnership('index.php?module=$moduleName&view=MassActionAjax&mode=transferOwnership')",
				'linkicon' => 'fas fa-user',
			];
		}
		if ($moduleModel->isPermitted('CreateView')) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MASS_ADD',
				'linkurl' => "javascript:Vtiger_Index_Js.massAddDocuments('index.php?module=$moduleName&view=MassAddDocuments')",
				'linkicon' => 'adminIcon-document-templates',
			];
		}
		if ($moduleModel->isPermitted('MassActive')) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MASS_ACTIVATE',
				'linkurl' => 'javascript:',
				'dataUrl' => "index.php?module=$moduleName&action=MassState&state=Active&sourceView=List",
				'linkclass' => 'js-mass-record-event',
				'linkicon' => 'fas fa-undo-alt',
			];
		}
		if ($moduleModel->isPermitted('MassArchived')) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MASS_ARCHIVE',
				'linkurl' => 'javascript:',
				'dataUrl' => "index.php?module=$moduleName'&action=MassState&state=Archived&sourceView=List",
				'linkclass' => 'js-mass-record-event',
				'linkicon' => 'fas fa-archive',
			];
		}
		if ($moduleModel->isPermitted('MassTrash')) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MASS_MOVE_TO_TRASH',
				'linkurl' => 'javascript:',
				'dataUrl' => "index.php?module=$moduleName&action=MassState&state=Trash&sourceView=List",
				'linkclass' => 'js-mass-record-event',
				'linkicon' => 'fas fa-trash-alt',
			];
		}
		if ($moduleModel->isPermitted('MassDelete')) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MASS_DELETE',
				'linkurl' => 'javascript:',
				'dataUrl' => "index.php?module=$moduleName&action=MassDelete&sourceView=List",
				'linkclass' => 'js-mass-record-event',
				'linkicon' => 'fas fa-eraser',
			];
		}
		foreach ($massActionLinks as $massActionLink) {
			$links['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}
		return $links;
	}

	public function loadListViewCondition()
	{
		$queryGenerator = $this->get('query_generator');
		$queryGenerator->setField('filetype');
		$folderValue = $this->get('folder_value');
		if (!empty($folderValue)) {
			$queryGenerator->addCondition($this->get('folder_id'), $folderValue, 'e');
		}
		parent::loadListViewCondition();
	}
}
