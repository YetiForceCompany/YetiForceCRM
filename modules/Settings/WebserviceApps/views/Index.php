<?php

/**
 * Configuration POS.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_WebserviceApps_Index_View extends Settings_Vtiger_Index_View
{
	/** {@inheritdoc} */
	public function preProcess(App\Request $request, $display = true)
	{
		parent::preProcess($request, $display);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('IndexPreProcess.tpl', $qualifiedModuleName);
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);

		$listServers = Settings_WebserviceApps_Module_Model::getServers(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('LIST_SERVERS', $listServers);
		$viewer->assign('IS_PORTAL', isset(array_column($listServers, 'type', 'type')['WebservicePremium']));
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}

	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'libraries.clipboard.dist.clipboard',
		]));
	}
}
