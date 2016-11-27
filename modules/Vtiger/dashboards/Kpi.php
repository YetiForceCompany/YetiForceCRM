<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************************************************************** */

class Vtiger_Kpi_Dashboard extends Vtiger_IndexAjax_View
{

	public function process(Vtiger_Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$linkId = $request->get('linkid');

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
		$content = $request->get('content');
		if (!empty($content)) {
			$viewer->view('dashboards/KpiContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/Kpi.tpl', $moduleName);
		}
	}
}
