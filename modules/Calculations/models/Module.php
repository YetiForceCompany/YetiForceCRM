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
class Calculations_Module_Model extends Inventory_Module_Model{
	var $modules_fields_ids = Array();
	var $widget_no_rows = 5;

	public function isSummaryViewSupported() {
		return true;
	}
	
	function getCalculations(Vtiger_Request $request) {
		$fromModule = $request->get('fromModule');
		$record = $request->get('record');
		$showtype = $request->get('showtype');

		$db = PearDatabase::getInstance();
		$fields = ['id','name','calculationsstatus'];
		$limit = 10;
		$params = [];
		if(!empty($request->get('limit'))){
			$limit = $request->get('limit');
		}
		if($fromModule =='Accounts'){
			$fields[] = 'potentialid';
		}elseif ($fromModule =='Potentials') {
			$fields[] = 'assigned_user_id';
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
		
		$calculationsStatusSearch = implode("','", $calculationsStatus);
		$showtype = $request->get('showtype');
		if($showtype == 'archive'){
			$sql .=	" AND vtiger_calculations.calculationsstatus IN ('$calculationsStatusSearch')";
		}else{
			$sql .=	" AND vtiger_calculations.calculationsstatus NOT IN ('$calculationsStatusSearch')";
		}
		
		if($fromModule =='Accounts'){
			$sql .=	' AND vtiger_calculations.relatedid = ?';
			$params[] = $record;
		}elseif ($fromModule =='Potentials') {
			$sql .=	' AND vtiger_calculations.potentialid = ?';
			$params[] = $record;
		}
		$sql.= ' LIMIT '.$limit;

		$result = $db->pquery($sql, $params);
		$returnData = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$returnData[] = $db->query_result_rowdata($result, $i);
		}
		return $returnData;
	}
}
