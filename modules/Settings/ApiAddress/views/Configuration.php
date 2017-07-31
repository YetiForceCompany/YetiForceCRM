<?php

/**
 * Settings ApiAddress Configuration view class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_ApiAddress_Configuration_View extends Settings_Vtiger_Index_View
{

	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request);
	}

	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);

		$viewer->assign('CONFIG', Settings_ApiAddress_Module_Model::getInstance('Settings:ApiAddress')->getConfig());
		$viewer->assign('MODULENAME', $request->getModule(false));
		echo $viewer->view('Configuration.tpl', $request->getModule(false), true);
	}
}
