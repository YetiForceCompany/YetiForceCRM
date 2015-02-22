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

	function getSearchParams($assignedto = '',$date) {	
		$conditions = array();
		$listSearchParams = array();
		if($assignedto != '') array_push($conditions,array('assigned_user_id','e',getUserFullName($assignedto)));
		if(!empty($date)){
			array_push($conditions,array('due_date','bw',$date.','.$date.''));
		}
		$listSearchParams[] = $conditions;
		return '&search_params='. json_encode($listSearchParams);
	}

	public function process(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$loggedUserId = $currentUser->get('id');
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$linkId = $request->get('linkid');
		$user = $request->get('user');
		$time = $request->get('time');
		if ( $time == NULL ) {
			$time['start'] = date('Y-m-d', strtotime("-1 week"));
			$time['end'] = date("Y-m-d");
		}
		else {
			// date parameters passed, convert them to YYYY-mm-dd
			$time['start'] = Vtiger_Functions::currentUserDisplayDate( $time['start'] );
			$time['end'] = Vtiger_Functions::currentUserDisplayDate( $time['end'] );
		}

		if($user == NULL)
			$user = $loggedUserId;
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		
		$data = $moduleModel->getWidgetTimeControl($user, $time);
		$workDays = $moduleModel->getWorkingDays($time['start'], $time['end']);
		$selectedDays = (strtotime($time['end']) - strtotime($time['start'])) / (60*60*24) + 1;

		$listViewUrl = 'index.php?module=OSSTimeControl&view=List';
		for($i = 0;$i<count($data['data']);$i++){
			$data['data'][$i]["links"] = $listViewUrl.$this->getSearchParams($user,$data['data'][$i][1]);
		}

		
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
		$viewer->assign('LOGGEDUSERID', $loggedUserId);
		$content = $request->get('content');
		if(!empty($content)) {
			$viewer->view('dashboards/TimeControlContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/TimeControl.tpl', $moduleName);
		}
	}
}