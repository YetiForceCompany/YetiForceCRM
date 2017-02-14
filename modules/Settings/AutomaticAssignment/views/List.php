<?php

/**
 * Automatic Assignment List View Class
 * @package YetiForce.Settings.View
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_AutomaticAssignment_List_View extends Settings_Vtiger_List_View
{

	/**
	 * Pre-process function
	 * @param Vtiger_Request $request
	 * @param boolean $display
	 */
	public function preProcess(Vtiger_Request $request, $display = true)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('SUPPORTED_MODULE_MODELS', Settings_AutomaticAssignment_Module_Model::getSupportedModules());
		parent::preProcess($request, $display);
	}
}
