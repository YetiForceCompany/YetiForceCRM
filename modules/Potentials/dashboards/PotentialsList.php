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

class Potentials_PotentialsList_Dashboard extends Vtiger_IndexAjax_View {
	function getScripts() {
		return $this->checkAndConvertJsScripts(array('modules.Potentials.dashboards.PotentialsList'));
	}
	public function process(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$linkId = $request->get('linkid');
		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('RELATED_MODULE', 'Potentials');
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', self::getData($request, $widget));
		$viewer->assign('SCRIPTS', $this->getScripts());
        
		//Include special script and css needed for this widget
		$viewer->assign('CURRENTUSER', $currentUser);
		$content = $request->get('content');
		if(!empty($content)) {
			$viewer->view('dashboards/PotentialsListContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/PotentialsList.tpl', $moduleName);
		}
	}
	
	public function getData(Vtiger_Request $request, $widget) {
		$db = PearDatabase::getInstance();
		$fields = ['id','potentialname','sales_stage','related_to','assigned_user_id'];
		$limit = 10;
		$params = [];
		if(!empty($widget->get('limit'))){
			$limit = $widget->get('limit');
		}
		$potentialConfig = Settings_SalesProcesses_Module_Model::getConfig('potential');
		$potentialSalesStage = $potentialConfig['salesstage'];
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$module = 'Potentials';
		$instance = CRMEntity::getInstance($module);
		$securityParameter = $instance->getUserAccessConditionsQuerySR($module, $currentUser);
		
		$queryGenerator = new QueryGenerator($module, $currentUser);
		$queryGenerator->setFields($fields);
		$sql = $queryGenerator->getQuery();
		
		if ($securityParameter != '')
			$sql.= $securityParameter;
		
		if(!empty($potentialSalesStage)){
			$potentialSalesStageSearch = implode("','", $potentialSalesStage);
			$sql .=	" AND vtiger_potential.sales_stage NOT IN ('$potentialSalesStageSearch')";
		}
		$showtype = $request->get('showtype');
		if($showtype == 'common'){
			$sql .= ' AND vtiger_crmentity.crmid IN (SELECT DISTINCT crmid FROM u_yf_crmentity_showners WHERE userid = ?)';
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
