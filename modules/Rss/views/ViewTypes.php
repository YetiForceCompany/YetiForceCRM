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

class Rss_ViewTypes_View extends Vtiger_BasicModal_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getRssWidget');
		$this->exposeMethod('getRssAddForm');
	}

	public function process(Vtiger_Request $request)
	{
		$mode = $request->get('mode');
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	/**
	 * Function to display rss sidebar widget
	 * @param Vtiger_Request $request 
	 */
	public function getRssWidget(Vtiger_Request $request)
	{
		$module = $request->get('module');
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		$rssSources = $moduleModel->getRssSources();
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $module);
		$viewer->assign('RSS_SOURCES', $rssSources);
		$this->preProcess($request);
		$viewer->view('RssWidgetContents.tpl', $module);
		$this->postProcess($request);
	}

	/**
	 * Function to get the rss add form 
	 * @param Vtiger_Request $request
	 */
	public function getRssAddForm(Vtiger_Request $request)
	{
		$module = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $module);
		$this->preProcess($request);
		$viewer->view('RssAddForm.tpl', $module);
		$this->postProcess($request);
	}
}
