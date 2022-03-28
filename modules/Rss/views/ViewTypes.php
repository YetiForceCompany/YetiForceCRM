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

class Rss_ViewTypes_View extends Vtiger_BasicModal_View
{
	use \App\Controller\ExposeMethod;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getRssWidget');
		$this->exposeMethod('getRssAddForm');
	}

	/**
	 * Function to display rss sidebar widget.
	 *
	 * @param \App\Request $request
	 */
	public function getRssWidget(App\Request $request)
	{
		$module = $request->getModule();
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
	 * Function to get the rss add form.
	 *
	 * @param \App\Request $request
	 */
	public function getRssAddForm(App\Request $request)
	{
		$module = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $module);
		$this->preProcess($request);
		$viewer->view('RssAddForm.tpl', $module);
		$this->postProcess($request);
	}
}
