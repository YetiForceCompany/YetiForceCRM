<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ************************************************************************************/

class Potentials_Module_Model extends Vtiger_Module_Model {

	/**
	 * Function returns number of Open Potentials in each of the sales stage
	 * @param <Integer> $owner - userid
	 * @return <Array>
	 */
	public function getPotentialsCountBySalesStage($owner, $dateFilter) {
		$db = PearDatabase::getInstance();

		if (!$owner) {
			$currenUserModel = Users_Record_Model::getCurrentUserModel();
			$owner = $currenUserModel->getId();
		} else if ($owner === 'all') {
			$owner = '';
		}

		$params = array();
		if(!empty($owner)) {
			$ownerSql =  ' AND smownerid = ? ';
			$params[] = $owner;
		}
		if(!empty($dateFilter)) {
			$dateFilterSql = ' AND closingdate BETWEEN ? AND ? ';
			$params[] = $dateFilter['start'];
			$params[] = $dateFilter['end'];
		}
		$sql = 'SELECT COUNT(*) AS count, sales_stage FROM vtiger_potential 
				INNER JOIN vtiger_crmentity ON vtiger_potential.potentialid = vtiger_crmentity.crmid
				WHERE vtiger_crmentity.deleted = 0 AND vtiger_potential.sales_stage NOT IN ("Closed Won", "Closed Lost")'.$ownerSql . $dateFilterSql;
		
		$relatedModuleName = $this->getName();
		$instance = CRMEntity::getInstance($relatedModuleName);
		$securityParameter = $instance->getUserAccessConditionsQuerySR($relatedModuleName);
		if ($securityParameter != '')
			$sql .= $securityParameter;
		
		$sql .= ' GROUP BY vtiger_potential.sales_stage ORDER BY count desc';
		$result = $db->pquery($sql, $params);
		
		$response = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$saleStage = $db->query_result($result, $i, 'sales_stage');
			$response[$i][0] = $saleStage;
			$response[$i][1] = $db->query_result($result, $i, 'count');
			$response[$i][2] = vtranslate($saleStage, $this->getName());
		}
		return $response;
	}

	/**
	 * Function returns number of Open Potentials for each of the sales person
	 * @param <Integer> $owner - userid
	 * @return <Array>
	 */
	public function getPotentialsCountBySalesPerson() {
		$db = PearDatabase::getInstance();
		//TODO need to handle security
		$usersSqlFullName = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');
		$params = [];
		$result = $db->pquery('SELECT COUNT(*) AS count, '.$usersSqlFullName.' as last_name, vtiger_potential.sales_stage FROM vtiger_potential
						INNER JOIN vtiger_crmentity ON vtiger_potential.potentialid = vtiger_crmentity.crmid
						INNER JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid AND vtiger_users.status="ACTIVE"
						AND vtiger_crmentity.deleted = 0'.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()).'
						INNER JOIN vtiger_sales_stage ON vtiger_potential.sales_stage =  vtiger_sales_stage.sales_stage 
						GROUP BY smownerid, sales_stage ORDER BY vtiger_sales_stage.sortorderid', $params);

		$response = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$response[$i]['count'] = $row['count'];
			$response[$i]['last_name'] = decode_html($row['last_name']);
			$response[$i]['sales_stage'] = $row['sales_stage'];
			//$response[$i][2] = $row['']
 		}
		return $response;
	}

	/**
	 * Function returns Potentials sum_invoices for each Sales Person
	 * @return <Array>
	 */
	function getPotentialsPipelinedAmountPerSalesPerson() {
		$db = PearDatabase::getInstance();
		//TODO need to handle security
		$usersSqlFullName = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');
		$params = [];
		$result = $db->pquery('SELECT sum(sum_invoices) AS sum_invoices, '.$usersSqlFullName.' as last_name, vtiger_potential.sales_stage FROM vtiger_potential
						INNER JOIN vtiger_crmentity ON vtiger_potential.potentialid = vtiger_crmentity.crmid
						INNER JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid AND vtiger_users.status="ACTIVE"
						AND vtiger_crmentity.deleted = 0 '.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()).
						'INNER JOIN vtiger_sales_stage ON vtiger_potential.sales_stage =  vtiger_sales_stage.sales_stage 
						WHERE vtiger_potential.sales_stage NOT IN ("Closed Won", "Closed Lost")
						GROUP BY smownerid, sales_stage ORDER BY vtiger_sales_stage.sortorderid', $params);
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
                        $row['last_name'] = decode_html($row['last_name']);
			$data[] = $row;
		}
		return $data;
	}

	/**
	 * Function returns Total Revenue for each Sales Person
	 * @return <Array>
	 */
	function getTotalRevenuePerSalesPerson($dateFilter) {
		$db = PearDatabase::getInstance();
		//TODO need to handle security
		$params = array();
		$params[] = 'Closed Won';
		if(!empty($dateFilter)) {
			$dateFilterSql = ' AND createdtime BETWEEN ? AND ? ';
			//client is not giving time frame so we are appending it
			$params[] = $dateFilter['start']. ' 00:00:00';
			$params[] = $dateFilter['end']. ' 23:59:59';
		}
		$usersSqlFullName = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');
		$result = $db->pquery('SELECT sum(sum_invoices) sum_invoices, '.$usersSqlFullName.' as last_name,vtiger_users.id as id,DATE_FORMAT(closingdate, "%d-%m-%Y") AS closingdate  FROM vtiger_potential
						INNER JOIN vtiger_crmentity ON vtiger_potential.potentialid = vtiger_crmentity.crmid
						INNER JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid AND vtiger_users.status="ACTIVE"
						AND vtiger_crmentity.deleted = 0 '.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()).'WHERE sales_stage = ? '.' '.$dateFilterSql.' GROUP BY smownerid', $params);
		$data = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
                        $row['last_name'] = decode_html($row['last_name']);
			$data[] = $row;
		}
		return $data;
	}

	 /**
	 * Function returns Top Potentials Header
	 * 
	 */
	function getTopPotentialsHeader() {
		$headerArray = array('potentialname' => 'Potential Name');
		$fieldsToDisplay = array('sum_invoices', 'related_to');
		$moduleModel = Vtiger_Module_Model::getInstance('Potentials');
		foreach ($fieldsToDisplay as $value) {
			$fieldInstance = Vtiger_Field_Model::getInstance($value, $moduleModel);
			if ($fieldInstance->isViewable()) {
				$headerArray = array_merge($headerArray, array($value => $fieldInstance->label));
			}
		}
		return $headerArray;
	}

	/**
	 * Function returns Top Potentials
	 * @return <Array of Vtiger_Record_Model>
	 */
	function getTopPotentials($pagingModel) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();
        $moduleModel = Vtiger_Module_Model::getInstance('Potentials');
        $fieldsToDisplay=  array("sum_invoices","related_to");
         
        $query = "SELECT crmid , potentialname ";
		foreach ($fieldsToDisplay as $value) {
			$fieldInstance = Vtiger_Field_Model::getInstance($value, $moduleModel);
			if ($fieldInstance->isViewable()) {
				$query = $query . ', ' . $value;
			}
		}
		$query = $query . ' FROM vtiger_potential
						INNER JOIN vtiger_crmentity ON vtiger_potential.potentialid = vtiger_crmentity.crmid
							AND deleted = 0 ' . Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()) . "
						WHERE sales_stage NOT IN ('Closed Won', 'Closed Lost') AND sum_invoices > 0
						ORDER BY sum_invoices DESC LIMIT " . $pagingModel->getStartIndex() . ', ' . $pagingModel->getPageLimit();
		$result = $db->pquery($query, []);

		$models = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$modelInstance = Vtiger_Record_Model::getCleanInstance('Potentials');
			$modelInstance->setId($db->query_result($result, $i, 'crmid'));
			$modelInstance->set('sum_invoices', $db->query_result($result, $i, 'sum_invoices'));
			$modelInstance->set('potentialname', $db->query_result($result, $i, 'potentialname'));
			$modelInstance->set('related_to', $db->query_result($result, $i, 'related_to'));
			$models[] = $modelInstance;
		}
		return $models;
	}

	/**
	 * Function returns Potentials Forecast Amount
	 * @return <Array>
	 */
	function getForecast($closingdateFilter,$dateFilter) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		$params = array();
		$params[] = $currentUser->getId();
		if(!empty($closingdateFilter)) {
			$closingdateFilterSql = ' AND closingdate BETWEEN ? AND ? ';
			$params[] = $closingdateFilter['start'];
			$params[] = $closingdateFilter['end'];
		}
		
		if(!empty($dateFilter)) {
			$dateFilterSql = ' AND createdtime BETWEEN ? AND ? ';
			//client is not giving time frame so we are appending it
			$params[] = $dateFilter['start']. ' 00:00:00';
			$params[] = $dateFilter['end']. ' 23:59:59';
		}
		
		$result = $db->pquery('SELECT forecast_amount, DATE_FORMAT(closingdate, "%m-%d-%Y") AS closingdate FROM vtiger_potential
					INNER JOIN vtiger_crmentity ON vtiger_potential.potentialid = vtiger_crmentity.crmid
					AND deleted = 0 AND smownerid = ? WHERE closingdate >= CURDATE() AND sales_stage NOT IN ("Closed Won", "Closed Lost")'.
					' '.$closingdateFilterSql.$dateFilterSql,
					$params);

		$forecast = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$forecast[] = $row;
		}
		return $forecast;

	}

	/**
	 * Function to get relation query for particular module with function name
	 * @param <record> $recordId
	 * @param <String> $functionName
	 * @param Vtiger_Module_Model $relatedModule
	 * @return <String>
	 */
	public function getRelationQuery($recordId, $functionName, $relatedModule, $relationModel = false) {
		if ($functionName === 'get_activities') {
            $userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
			
			$query = "SELECT CASE WHEN (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END AS user_name,
						vtiger_crmentity.*, vtiger_activity.activitytype, vtiger_activity.subject, vtiger_activity.date_start, vtiger_activity.time_start,
						vtiger_activity.recurringtype, vtiger_activity.due_date, vtiger_activity.time_end, vtiger_activity.visibility,
						vtiger_activity.status AS status
						FROM vtiger_activity
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activity.activityid
						LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
						LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
						WHERE vtiger_crmentity.deleted = 0";
			$time = vtlib_purify($_REQUEST['time']);
			if ($time == 'current') {
				$stateActivityLabels = Calendar_Module_Model::getComponentActivityStateLabel('current');
				$query .= " AND (vtiger_activity.activitytype NOT IN ('Emails') AND vtiger_activity.status IN ('" . implode("','", $stateActivityLabels) . "'))";
			}
			if ($time == 'history') {
				$stateActivityLabels = Calendar_Module_Model::getComponentActivityStateLabel('history');
				$query .= " AND (vtiger_activity.activitytype NOT IN ('Emails') AND vtiger_activity.status IN ('" . implode("','", $stateActivityLabels) . "'))";
			}
			$query .= ' AND vtiger_activity.process = '.$recordId;
			$relatedModuleName = $relatedModule->getName();
			$query .= $this->getSpecificRelationQuery($relatedModuleName);
			$instance = CRMEntity::getInstance($relatedModuleName);
			$securityParameter = $instance->getUserAccessConditionsQuerySR($relatedModuleName, false, $recordId);
			if ($securityParameter != '')
				$query .= $securityParameter;
		} elseif ($functionName === 'get_mails' && $relatedModule->getName() == 'OSSMailView') {
			$query = OSSMailView_Record_Model::getMailsQuery($recordId, $relatedModule->getName());
		} else {
			$query = parent::getRelationQuery($recordId, $functionName, $relatedModule, $relationModel);
		}

		return $query;
	}
	
	/**
	 * Function returns Potentials sum_invoices for each Sales Stage
	 * @return <Array>
	 */
	function getPotentialTotalAmountBySalesStage() {
		//$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		$picklistValues = Vtiger_Util_Helper::getPickListValues('sales_stage');
		$data = array();
		foreach ($picklistValues as $key => $picklistValue) {
			$result = $db->pquery('SELECT SUM(sum_invoices) AS sum_invoices FROM vtiger_potential
								   INNER JOIN vtiger_crmentity ON vtiger_potential.potentialid = vtiger_crmentity.crmid
								   AND deleted = 0 '.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()).' WHERE sales_stage = ?', array($picklistValue));
			$num_rows = $db->num_rows($result);
			for($i=0; $i<$num_rows; $i++) {
				$values = array();
				$sum_invoices = $db->query_result($result, $i, 'sum_invoices');
				if(!empty($sum_invoices)){
					$values[0] = $db->query_result($result, $i, 'sum_invoices');
					$values[1] = vtranslate($picklistValue, $this->getName());
					$data[] = $values;
				}
				
			}
		}
		return $data;
	}

	/**
	 * Function to get list view query for popup window
	 * @param <String> $sourceModule Parent module
	 * @param <String> $field parent fieldname
	 * @param <Integer> $record parent id
	 * @param <String> $listQuery
	 * @return <String> Listview Query
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery) {
		if (in_array($sourceModule, array('Products', 'Services'))) {
			if ($sourceModule === 'Products') {
				$condition = " vtiger_potential.potentialid NOT IN (SELECT crmid FROM vtiger_seproductsrel WHERE productid = '$record')";
			} elseif ($sourceModule === 'Services') {
				$condition = " vtiger_potential.potentialid NOT IN (SELECT relcrmid FROM vtiger_crmentityrel WHERE crmid = '$record' UNION SELECT crmid FROM vtiger_crmentityrel WHERE relcrmid = '$record') ";
			}

			$pos = stripos($listQuery, 'where');
			if ($pos) {
				$overRideQuery = $listQuery. ' AND ' . $condition;
			} else {
				$overRideQuery = $listQuery . ' WHERE ' . $condition;
			}
			return $overRideQuery;
		}
	}

	/**
	 * Function returns query for module record's search
	 * @param <String> $searchValue - part of record name (label column of crmentity table)
	 * @param <Integer> $parentId - parent record id
	 * @param <String> $parentModule - parent module name
	 * @return <String> - query
	 */
	public function getSearchRecordsQuery($searchValue, $parentId=false, $parentModule=false) {
		if($parentId && in_array($parentModule, array('Accounts', 'Contacts'))) {
			$query = "SELECT * FROM vtiger_crmentity
						INNER JOIN vtiger_potential ON vtiger_potential.potentialid = vtiger_crmentity.crmid
						WHERE deleted = 0 AND vtiger_potential.related_to = $parentId AND label like '%$searchValue%'";
			return $query;
		}
		return parent::getSearchRecordsQuery($parentId, $parentModule);
	}
	public function getTimeEmployee($id) {
		$db = PearDatabase::getInstance();
		
		$QuoteId = array();
		$sumAllTime = 0;
		$SalesId = array();
		$PotentialId = array();
		$sales=array();
		$quote=array();
		$sql = "SELECT * FROM vtiger_crmentityrel WHERE crmid=$id ";
		$resultSource = $db->query($sql, true);
		$num = $db->num_rows( $resultSource );
		for($i=0; $i<$num; $i++) { 
			if($db->query_result( $resultSource, $i, 'relmodule' )=='SalesOrder'){
				$sales[]=$db->query_result( $resultSource, $i, 'relcrmid' );
			}elseif($db->query_result( $resultSource, $i, 'relmodule' )=='Quotes'){
				$quote[]=$db->query_result( $resultSource, $i, 'relcrmid' );
			}
		}
		$sqlTC="SELECT * FROM vtiger_osstimecontrol WHERE deleted = ? AND osstimecontrol_status = ?";
		$resultTC = $db->pquery($sqlTC, array(0,'Accepted'), true);
		$num = $db->num_rows( $resultTC );
		$q=0;
		for($i=0; $i<$num; $i++) { 
			$q=$i;
			if(($db->query_result( $resultTC, $i, 'quoteid' ) == 0) && ($db->query_result( $resultTC, $i, 'potentialid' ) == $id) && ($db->query_result( $resultTC, $i, 'salesorderid' ) == 0)){
				$PotentialId[] = $db->query_result( $resultTC, $i, 'osstimecontrolid' ) ;
				$q++;
			}
			if($q==$i && ($db->query_result( $resultTC, $i, 'quoteid' ) != 0) && ($db->query_result( $resultTC, $i, 'potentialid' ) == $id) ){
				$QuoteId[] = $db->query_result( $resultTC, $i, 'osstimecontrolid' ) ;
				$q++;
			}
			elseif($q==$i && $db->query_result( $resultTC, $i, 'quoteid' ) != 0 && ($db->query_result( $resultTC, $i, 'potentialid' ) != $id)){
				for($j=0; $j<count($quote); $j++) { 
					if($db->query_result( $resultTC, $i, 'quoteid' ) == $quote[$j]){
						$QuoteId[] = $db->query_result( $resultTC, $i, 'osstimecontrolid' ) ;
						$q++;
					}
				}
			}

			if($q==$i && ($db->query_result( $resultTC, $i, 'salesorderid' ) != 0) && ($db->query_result( $resultTC, $i, 'potentialid' ) == $id) ){
						$SalesId[] = $db->query_result( $resultTC, $i, 'osstimecontrolid' ) ;
			}
			elseif($q==$i && $db->query_result( $resultTC, $i, 'salesorderid' ) != 0 && ($db->query_result( $resultTC, $i, 'potentialid' ) != $id)){
				for($j=0; $j<count($sales); $j++) { 
					if($db->query_result( $resultTC, $i, 'quoteid' ) == $sales[$j]){
						$SalesId[] = $db->query_result( $resultTC, $i, 'osstimecontrolid' ) ;
					}
				}
			}
		
		}
		$Ids = array($QuoteId , $PotentialId , $SalesId);

		foreach($Ids as $module){
			foreach ($module as $moduleId){
				$idArray .= $moduleId . ',';
			}
		}
		
		
		$idArray = substr($idArray, 0, -1);
		$addSql='';
		if($idArray) {
		    $addSql=' WHERE vtiger_osstimecontrol.osstimecontrolid IN (' . $idArray . ') ';
		}
		$usersSqlFullName = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');
		//TODO need to handle security
		$result = $db->pquery('SELECT count(*) AS count, '.$usersSqlFullName.' as name, vtiger_users.id as id, SUM(vtiger_osstimecontrol.sum_time) as time  FROM vtiger_osstimecontrol
						INNER JOIN vtiger_crmentity ON vtiger_osstimecontrol.osstimecontrolid = vtiger_crmentity.crmid
						INNER JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid AND vtiger_users.status="ACTIVE"
						AND vtiger_crmentity.deleted = 0'.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()). $addSql 
						. ' GROUP BY smownerid', array());

		$data = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$data[] = $row;
		}
		return $data;
	}
	public function getTimePotentials($id) {
		$recordModel = Vtiger_Record_Model::getInstanceById($id, $this->getName());
		$response = array();
		$response[0][0] = $recordModel->get('sum_time');
		$response[0][1] = vtranslate('Total time [h]', $this->getName());
		$response[1][0] = $recordModel->get('sum_time_so');
		$response[1][1] = vtranslate('Total time [Sales Order]', $this->getName());
		$response[2][0] = $recordModel->get('sum_time_q');
		$response[2][1] = vtranslate('Total time [Quotes]', $this->getName());
		$response[3][0] = $recordModel->get('sum_time_k');
		$response[3][1] = vtranslate('Total time [Calculation]', $this->getName());
		$response[4][0] = $recordModel->get('sum_time_all');
		$response[4][1] = vtranslate('Total time [Sum]', $this->getName());
		return $response;
	}
	
	function getPotentialsList(Vtiger_Request $request) {
		$fromModule = $request->get('fromModule');
		$record = $request->get('record');
		$showtype = $request->get('showtype');
		$rqLimit = $request->get('limit');

		$db = PearDatabase::getInstance();
		$fields = ['id','potentialname','sales_stage','assigned_user_id'];
		$limit = 10;
		$params = [];
		if(!empty($rqLimit)){
			$limit = $rqLimit;
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
		
		$potentialSalesStageSearch = implode("','", $potentialSalesStage);
		$showtype = $request->get('showtype');
		if($showtype == 'archive'){
			$sql .=	" AND vtiger_potential.sales_stage IN ('$potentialSalesStageSearch')";
		}else{
			$sql .=	" AND vtiger_potential.sales_stage NOT IN ('$potentialSalesStageSearch')";
		}
		
		$sql .=	' AND vtiger_potential.related_to = ?';
		$params[] = $record;

		$sql.= ' LIMIT '.$limit;

		$result = $db->pquery($sql, $params);
		$returnData = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$returnData[] = $db->query_result_rowdata($result, $i);
		}
		return $returnData;
	}
}
