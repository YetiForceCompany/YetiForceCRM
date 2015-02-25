<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.Vtiger_Save_Action
 *************************************************************************************/

class Calculations_BasicAjax_Action extends Vtiger_BasicAjax_Action {

	function checkPermission(Vtiger_Request $request) {
		return;
	}

	public function process(Vtiger_Request $request) {
		$searchValue = $request->get('search_value');
		$searchModule = $request->get('search_module');
		$potentialId  = $request->get('potentialid');
var_dump($searchValue, $searchModule, $potentialId);
		$parentRecordId = $request->get('parent_id');
		$parentModuleName = $request->get('parent_module');
		$relatedModule = $request->get('module');

		$searchModuleModel = Vtiger_Module_Model::getInstance($searchModule);
		if( ($searchModule == 'Services' || $searchModule == 'Products') && Settings_SalesProcesses_Module_Model::checkRelatedToPotentialsLimit() ){
			$records = $this->searchRecord( $searchValue, $searchModule, $potentialId );
		}else{
			$records = $searchModuleModel->searchRecord($searchValue, $parentRecordId, $parentModuleName, $relatedModule);
		}
		

		$result = array();
		foreach($records as $moduleName=>$recordModels) {
			foreach($recordModels as $recordModel) {
				$result[] = array('label'=>decode_html($recordModel->getName()), 'value'=>decode_html($recordModel->getName()), 'id'=>$recordModel->getId());
			}
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
	
	public function searchRecord($searchValue, $searchModule, $potentialId) {
		$db = PearDatabase::getInstance();
		$potentialId = intval($potentialId);

		if ( $searchModule == 'Products' ) {
			$sql = "SELECT
						crm.*,
						prod.*
					FROM
						`vtiger_products` prod
						INNER JOIN `vtiger_crmentity` crm
							ON crm.`crmid` = prod.`productid`
						INNER JOIN `vtiger_seproductsrel` prodrel
							ON prod.`productid` = prodrel.`productid`
							AND prodrel.`setype` = 'Potentials'
					WHERE prod.`productname` LIKE '%$searchValue%'
						AND crm.`setype` = 'Products'
						AND crm.`deleted` = 0
						AND prodrel.`crmid` = '$potentialId';";
		}
		else {
			$sql = "SELECT
						crm.*,
						serv.*
					FROM
						`vtiger_service` serv
						INNER JOIN `vtiger_crmentity` crm
							ON crm.`crmid` = serv.`serviceid`
						INNER JOIN `vtiger_crmentityrel` crmrel
							ON serv.`serviceid` = crmrel.`relcrmid`
							AND crmrel.`module` = 'Potentials'
					WHERE serv.`servicename` LIKE '%$searchValue%'
						AND crm.`setype` = 'Services'
						AND crm.`deleted` = 0
						AND crmrel.`crmid` = '$potentialId';";
		}
		
		$result = $db->pquery($sql , array());
		$noOfRows = $db->num_rows($result);

		$moduleModels = array();
		$matchingRecords = array();
		for($i=0; $i<$noOfRows; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			if(Users_Privileges_Model::isPermitted($row['setype'], 'DetailView', $row['crmid'])){
				$row['id'] = $row['crmid'];
				$moduleName = $row['setype'];
				if(!array_key_exists($moduleName, $moduleModels)) {
					$moduleModels[$moduleName] = Vtiger_Module_Model::getInstance($moduleName);
				}
				$moduleModel = $moduleModels[$moduleName];
				$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $moduleName);
				$recordInstance = new $modelClassName();
				$matchingRecords[$moduleName][$row['id']] = $recordInstance->setData($row)->setModuleFromInstance($moduleModel);
			}
		}

		return $matchingRecords;
	}
}
