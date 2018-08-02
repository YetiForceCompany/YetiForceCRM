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

class Home_DashBoard_View extends Vtiger_DashBoard_View
{
	/**
	 * {@inheritdoc}
	 */
	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request, false);
		$viewer = $this->getViewer($request);
		$modulesWithWidget = Vtiger_DashBoard_Model::getModulesWithWidgets($request->getModule(), $this->getDashboardId($request));
		$viewer->assign('MODULES_WITH_WIDGET', $modulesWithWidget);
		$this->preProcessDisplay($request);
	}
}
