<?php

/**
 * Time counter dashboard file.
 *
 * @package Dashboard
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Sołek <a.solek@yetiforce.com>
 */
/**
 * Time counter dashboard Class.
 */
class OSSTimeControl_TimeCounter_Dashboard extends Vtiger_IndexAjax_View
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$currentUserId = \App\User::getCurrentUserId();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$widget = Vtiger_Widget_Model::getInstance($request->getInteger('linkid'), $currentUserId);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		if ($request->has('content')) {
			$viewer->view('dashboards/TimeCounterContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/TimeCounter.tpl', $moduleName);
		}
	}
}
