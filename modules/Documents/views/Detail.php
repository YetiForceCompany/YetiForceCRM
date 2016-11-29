<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Documents_Detail_View extends Vtiger_Detail_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showDocumentRelations');
	}

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		$recordId = $request->get('record');
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
		$fileType = $recordModel->get('filetype');
		$fileIcon = \App\Layout\Icon::getIconByFileType($fileType);

		$viewer = $this->getViewer($request);
		$viewer->assign('NO_SUMMARY', true);
		$viewer->assign('EXTENSION_ICON', $fileIcon);
		parent::preProcess($request);
	}

	/**
	 * Function to get Ajax is enabled or not
	 * @param Vtiger_Record_Model record model
	 * @return <boolean> true/false
	 */
	public function isAjaxEnabled($recordModel)
	{
		return false;
	}

	/**
	 * Function shows basic detail for the record
	 * @param <type> $request
	 */
	public function showModuleBasicView(Vtiger_Request $request)
	{
		return $this->showModuleDetailView($request);
	}

	public function showDocumentRelations(Vtiger_Request $request)
	{
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		$data = Documents_Record_Model::getReferenceModuleByDocId($recordId);
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORDID', $recordId);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('LIMIT', 'no_limit');
		$viewer->assign('DATA', $data);

		echo $viewer->view('DetailViewDocumentRelations.tpl', $moduleName, true);
	}
}
