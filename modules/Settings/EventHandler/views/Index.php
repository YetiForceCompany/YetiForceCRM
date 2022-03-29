<?php

/**
 * Settings event handler view file.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Settings event handler view class.
 */
class Settings_EventHandler_Index_View extends Settings_Vtiger_Index_View
{
	/** {@inheritdoc} */
	public function preProcess(App\Request $request, $display = true)
	{
		parent::preProcess($request);
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$activeTab = 'EditViewPreSave';
		if ($request->has('tab')) {
			$activeTab = $request->getByType('tab');
		}
		$qualifiedModuleName = $request->getModule(false);
		$handlers = [];
		foreach (\App\EventHandler::getAll(false) as $handler) {
			if (\App\EventHandler::SYSTEM !== (int) $handler['privileges']) {
				$handlers[$handler['event_name']][$handler['handler_class']] = $handler;
			}
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('HANDLERS', $handlers);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('ACTIVE_TAB', $activeTab);
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}
}
