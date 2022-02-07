<?php

/**
 * Settings map config view file.
 *
 * @package   Settings
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Settings map config view class.
 */
class Settings_Map_Config_View extends Settings_Vtiger_Index_View
{
	/** {@inheritdoc} */
	public function preProcess(App\Request $request, $display = true)
	{
		parent::preProcess($request);
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$activeTab = 'TileLayer';
		if ($request->has('tab')) {
			$activeTab = $request->getByType('tab');
		}
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('ACTIVE_TILE_LAYER', \App\Config::module('OpenStreetMap', 'tileLayerServer'));
		$viewer->assign('ACTIVE_COORDINATE', \App\Config::module('OpenStreetMap', 'coordinatesServer'));
		$viewer->assign('ACTIVE_ROUTING', \App\Config::module('OpenStreetMap', 'routingServer'));
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('ACTIVE_TAB', $activeTab);
		$viewer->view('Config.tpl', $qualifiedModuleName);
	}
}
