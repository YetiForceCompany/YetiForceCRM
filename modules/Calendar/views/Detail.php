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

class Calendar_Detail_View extends Vtiger_Detail_View
{
	/** {@inheritdoc} */
	public function preProcess(App\Request $request, $display = true)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('NO_SUMMARY', true);
		parent::preProcess($request);
	}

	/** {@inheritdoc} */
	public function showModuleDetailView(App\Request $request)
	{
		$recordModel = $this->record->getRecord();
		if (!$this->recordStructure) {
			$this->recordStructure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_STRUCTURE_MODEL', $this->recordStructure);
		$viewer->assign('INVITIES_SELECTED', $recordModel->getInvities());
		return parent::showModuleDetailView($request);
	}

	/** {@inheritdoc} */
	public function showModuleBasicView(App\Request $request)
	{
		return $this->showModuleDetailView($request);
	}
}
