<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class HelpDesk_Detail_View extends Vtiger_Detail_View
{
	/**
	 * {@inheritdoc}
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showRelatedRecords');
		$this->exposeMethod('showCharts');
	}

	/**
	 * {@inheritdoc}
	 */
	public function loadJsConfig(\App\Request $request)
	{
		parent::loadJsConfig($request);
		$jsEnv = [
			'checkIfRecordHasTimeControl' => (bool)\App\Config::module('HelpDesk', 'CHECK_IF_RECORDS_HAS_TIME_CONTROL'),
			'checkIfRelatedTicketsAreClosed' => (bool)\App\Config::module('HelpDesk', 'CHECK_IF_RELATED_TICKETS_ARE_CLOSED')
		];
		foreach($jsEnv as $key => $value) {
			\App\Config::setJsEnv($key, $value);
		}
	}

	public function showCharts(\App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$moduleName = $request->getModule();

		$viewer = $this->getViewer($request);
		if (!Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModulePermission('OSSTimeControl')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		$moduleModel = Vtiger_Module_Model::getInstance('OSSTimeControl');
		if ($moduleModel && $moduleModel->isActive()) {
			$data = $moduleModel->getTimeUsers($recordId, $moduleName);
		}
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		$viewer->view('charts/ShowTimeHelpDesk.tpl', $moduleName);
	}
}
