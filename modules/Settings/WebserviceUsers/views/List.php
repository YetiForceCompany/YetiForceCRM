<?php

/**
 * WebserviceUsers List View Class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_WebserviceUsers_List_View extends Settings_Vtiger_List_View
{

	/**
	 * Initiate data values for listview
	 * @param \App\Request $request
	 * @param Vtiger_Viewer $viewer
	 */
	public function initializeListViewContents(\App\Request $request, Vtiger_Viewer $viewer)
	{
		$qualifiedModuleName = $request->getModule(false);
		if (!$request->has('typeApi')) {
			$request->set('typeApi', current(Settings_WebserviceApps_Module_Model::getTypes()));
		}
		$typeApi = $request->get('typeApi');
		$this->listViewModel = Settings_Vtiger_ListView_Model::getInstance($qualifiedModuleName);
		$this->listViewModel->getModule()->typeApi = $typeApi;
		parent::initializeListViewContents($request, $viewer);
		$viewer->assign('TYPE_API', $typeApi);
	}
}
