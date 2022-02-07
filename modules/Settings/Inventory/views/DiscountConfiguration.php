<?php

/**
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_Inventory_DiscountConfiguration_View extends Settings_Vtiger_Index_View
{
	public function getView()
	{
		return 'DiscountConfiguration';
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		\App\Log::trace('Start ' . __METHOD__);
		$qualifiedModule = $request->getModule(false);
		$view = $this->getView();
		$config = Settings_Inventory_Module_Model::getConfig($view);

		$viewer = $this->getViewer($request);
		$viewer->assign('PAGE_LABELS', $this->getPageLabels($request));
		$viewer->assign('VIEW', $view);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$viewer->assign('CONFIG', $config);
		$viewer->view('Config.tpl', $qualifiedModule);
		\App\Log::trace('End ' . __METHOD__);
	}

	public function getPageLabels(App\Request $request)
	{
		$view = $this->getView();
		$translations = [];
		$translations['title'] = 'LBL_' . strtoupper($view);
		$translations['title_single'] = 'LBL_' . strtoupper($view) . '_SINGLE';
		$translations['description'] = 'LBL_' . strtoupper($view) . '_DESCRIPTION';

		return $translations;
	}

	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts(['modules.Settings.' . $request->getModule() . '.resources.Config']));
	}
}
