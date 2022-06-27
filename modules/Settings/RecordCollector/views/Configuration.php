<?php

/**
 * Settings RecordCollector Configuration view file.
 *
 * @package Settings.Views
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Sławomir Rembiesa <s.rembiesa@yetiforce.com>
 */

/**
 * Settings RecordCollector Configuration view class.
 */
class Settings_RecordCollector_Configuration_View extends Settings_Vtiger_Index_View
{
	/** {@inheritdoc} */
	public function preProcess(App\Request $request, $display = true)
	{
		parent::preProcess($request);
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULENAME', $request->getModule(false));
		$viewer->assign('COLLECTORS', Settings_RecordCollector_Module_Model::getInstance('Settings:RecordCollector')->getCollectors());
		$viewer->view('List.tpl', $request->getModule(false));
	}
}
