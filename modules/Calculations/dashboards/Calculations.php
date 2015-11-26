<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************************************************************/

class Calculations_Calculations_Dashboard extends Vtiger_IndexAjax_View {
	function getFooterScripts() {
		return $this->checkAndConvertJsScripts(array('modules.Calculations.dashboards.Calculations'));
	}
	public function process(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$linkId = $request->get('linkid');
		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('RELATED_MODULE', 'Calculations');
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', self::getData($request, $widget));
		$viewer->assign('SCRIPTS', $this->getFooterScripts());
        
		//Include special script and css needed for this widget
		$viewer->assign('CURRENTUSER', $currentUser);
		$content = $request->get('content');
		if(!empty($content)) {
			$viewer->view('dashboards/CalculationsContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/Calculations.tpl', $moduleName);
		}
	}
	
	public function getData(Vtiger_Request $request, $widget) {
		$db = PearDatabase::getInstance();
		$fields = ['id','name','calculationsstatus','relatedid','hdnGrandTotal','assigned_user_id'];
		$limit = 10;
		$params = [];
		if(!empty($widget->get('limit'))){
			$limit = $widget->get('limit');
		}
		$calculationConfig = Settings_SalesProcesses_Module_Model::getConfig('calculation');
		$calculationsStatus = $calculationConfig['calculationsstatus'];
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$module = 'Calculations';
		$instance = CRMEntity::getInstance($module);
		$securityParameter = $instance->getUserAccessConditionsQuerySR($module, $currentUser);
		
		$queryGenerator = new QueryGenerator($module, $currentUser);
		$queryGenerator->setFields($fields);
		$sql = $queryGenerator->getQuery();
		
		if ($securityParameter != '')
			$sql.= $securityParameter;
		
		if(!empty($calculationsStatus)){
			$calculationsStatusSearch = implode("','", $calculationsStatus);
			$sql .=	" AND vtiger_calculations.calculationsstatus NOT IN ('$calculationsStatusSearch')";
		}
		$showtype = $request->get('showtype');
		if($showtype == 'common'){
			$shownersTable = Vtiger_SharedOwner_UIType::getShownerTable($module);
			$sql .= ' AND vtiger_crmentity.crmid IN (SELECT DISTINCT crmid FROM '.$shownersTable.' WHERE userid = ?';
		}else{
			$sql .=	' AND vtiger_crmentity.smownerid = ?';
		}
		$params[] = $currentUser->getId();
		$sql.= ' LIMIT '.$limit;

		$result = $db->pquery($sql, $params);
		$returnData = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$returnData[] = $db->query_result_rowdata($result, $i);
		}
		return $returnData;
	}
}
