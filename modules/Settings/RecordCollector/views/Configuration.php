<?php

/**
 * Settings RecordCollector Configuration view class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_RecordCollector_Configuration_View extends Settings_Vtiger_Index_View
{
	public function preProcess(App\Request $request, $display = true)
	{
		parent::preProcess($request);
	}

	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);

		$viewer->assign('MODULENAME', $request->getModule(false));
		$viewer->assign('COLLECTORS', Settings_RecordCollector_Module_Model::getInstance('Settings:RecordCollector')->getCollectors());
		echo $viewer->view('Configuration.tpl', $request->getModule(false), true);
	}
}
