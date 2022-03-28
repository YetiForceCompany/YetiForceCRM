<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

class Documents_Detail_View extends Vtiger_Detail_View
{
	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showDocumentRelations');
	}

	/** {@inheritdoc} */
	public function preProcess(App\Request $request, $display = true)
	{
		$fileIcon = \App\Layout\Icon::getIconByFileType($this->record->getRecord()->get('filetype'));

		$viewer = $this->getViewer($request);
		$viewer->assign('NO_SUMMARY', true);
		$viewer->assign('EXTENSION_ICON', $fileIcon);
		parent::preProcess($request);
	}

	/** {@inheritdoc} */
	public function isAjaxEnabled($recordModel)
	{
		return false;
	}

	/** {@inheritdoc} */
	public function showModuleBasicView(App\Request $request)
	{
		return $this->showModuleDetailView($request);
	}

	public function showDocumentRelations(App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$moduleName = $request->getModule();

		$data = Documents_Record_Model::getReferenceModuleByDocId($recordId);
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORDID', $recordId);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('LIMIT', 0);
		$viewer->assign('DATA', $data);

		return $viewer->view('DetailViewDocumentRelations.tpl', $moduleName, true);
	}
}
