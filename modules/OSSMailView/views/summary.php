<?php

/**
 * OSSMailView summary view class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
Class OSSMailView_summary_View extends Vtiger_Edit_View
{

	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
	}

	public function preProcess(\App\Request $request)
	{
		
	}

	public function process(\App\Request $request)
	{
		
	}
}
