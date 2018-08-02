<?php

/**
 * Vtiger Kpi dashboard class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_Kpi_Dashboard extends Vtiger_IndexAjax_View
{
	public function process(\App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$linkId = $request->getInteger('linkid');

		$kpiHelper = new Vtiger_Kpi_Helper($request);
		$data = $kpiHelper->getData($request);
		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		$viewer->assign('KPILIST', $kpiHelper->getKpiList());
		$viewer->assign('KPITYPES', $kpiHelper->getKpiTypes());
		$viewer->assign('DTYPE', $request->get('type'));
		$viewer->assign('DSERVICE', $request->get('service'));
		$viewer->assign('DTIME', $request->get('time'));
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);

		//Include special script and css needed for this widget
		$viewer->assign('CURRENTUSER', $currentUser);
		if ($request->has('content')) {
			$viewer->view('dashboards/KpiContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/Kpi.tpl', $moduleName);
		}
	}
}
