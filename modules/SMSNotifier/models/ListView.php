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

class SMSNotifier_ListView_Model extends Vtiger_ListView_Model
{
	public function getAdvancedLinks()
	{
		$moduleModel = $this->getModule();
		$advancedLinks = [];
		$exportPermission = \App\Privilege::isPermitted($moduleModel->getName(), 'Export');
		if ($exportPermission) {
			$advancedLinks[] = [
				'linktype' => 'LISTVIEW',
				'linklabel' => 'LBL_EXPORT',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerExportAction("' . $this->getModule()->getExportUrl() . '")',
				'linkicon' => 'fas fa-upload',
			];
		}

		if (!Settings_ModuleManager_Library_Model::checkLibrary('mPDF') && \App\Privilege::isPermitted($moduleModel->getName(), 'ExportPdf')) {
			$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'PDF', $moduleModel->getName());
			$pdfModel = new $handlerClass();
			$templates = $pdfModel->getActiveTemplatesForModule($moduleModel->getName(), 'List');
			if (count($templates) > 0) {
				$advancedLinks[] = [
					'linktype' => 'DETAIL_VIEW_ADDITIONAL',
					'linklabel' => \App\Language::translate('LBL_EXPORT_PDF'),
					'linkurl' => 'javascript:Vtiger_Header_Js.getInstance().showPdfModal("index.php?module=' . $moduleModel->getName() . '&view=PDF&fromview=List");',
					'linkicon' => 'fas fa-file-excel',
					'title' => \App\Language::translate('LBL_EXPORT_PDF'),
				];
			}
		}

		$quickExportToExcelPermission = \App\Privilege::isPermitted($moduleModel->getName(), 'QuickExportToExcel');
		if ($quickExportToExcelPermission) {
			$advancedLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_QUICK_EXPORT_TO_EXCEL',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerQuickExportToExcel("' . $moduleModel->getName() . '")',
				'linkicon' => 'fas fa-file-excel',
			];
		}

		return $advancedLinks;
	}

	/*
	 * Function to get Basic links
	 * @return array of Basic links
	 */

	public function getBasicLinks()
	{
		$basicLinks = [];
		$moduleModel = $this->getModule();
		if (!Settings_ModuleManager_Library_Model::checkLibrary('mPDF') && \App\Privilege::isPermitted($moduleModel->getName(), 'ExportPdf')) {
			$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'PDF', $moduleModel->getName());
			$pdfModel = new $handlerClass();
			$templates = $pdfModel->getActiveTemplatesForModule($moduleModel->getName(), 'List');
			if (count($templates) > 0) {
				$basicLinks[] = [
					'linktype' => 'LISTVIEWBASIC',
					'linkurl' => 'javascript:Vtiger_Header_Js.getInstance().showPdfModal("index.php?module=' . $moduleModel->getName() . '&view=PDF&fromview=List");',
					'linkicon' => 'fas fa-file-excel',
					'title' => \App\Language::translate('LBL_EXPORT_PDF'),
				];
			}
		}

		return $basicLinks;
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
		$links = Vtiger_Link_Model::getAllByType($moduleModel->getId(), ['LISTVIEWMASSACTION'], $linkParams);
		$massActionLink = [];
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
}
