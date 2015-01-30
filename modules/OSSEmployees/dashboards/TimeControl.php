<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
class OSSEmployees_TimeControl_Dashboard extends Vtiger_IndexAjax_View {

	public function process(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$linkId = $request->get('linkid');
		$user = $request->get('user');
		$time = $request->get('time');

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		
		$data = $moduleModel->getWidgetTimeControl($user, $time);
		$workDays = $moduleModel->getWorkingDays($time['start'], $time['end']);
		$selectedDays = (strtotime($time['end']) - strtotime($time['start'])) / (60*60*24) + 1;
		
		$viewer->assign('SELECTEDDAYS', $selectedDays);
		$viewer->assign('WORKDAYS', $workDays);	
		$viewer->assign('AVERAGE', $data['average']);
		$viewer->assign('COUNTDAYS', $data['countDays']);
		$viewer->assign('USERID', $user );
		$viewer->assign('DTIME', $time );
		$viewer->assign('DATA', $data['data']);
		
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('CURRENTUSER', $currentUser);
		$content = $request->get('content');
		if(!empty($content)) {
			$viewer->view('dashboards/TimeControlContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/TimeControl.tpl', $moduleName);
		}
	}
}