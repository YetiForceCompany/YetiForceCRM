<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_MassActionAjax_View extends Vtiger_IndexAjax_View {
	function __construct() {
		parent::__construct();
		$this->exposeMethod('showMassEditForm');
		$this->exposeMethod('showAddCommentForm');
		$this->exposeMethod('showComposeEmailForm');
		$this->exposeMethod('showSendSMSForm');
		$this->exposeMethod('showDuplicatesSearchForm');
		$this->exposeMethod('transferOwnership');
	}

	function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	/**
	 * Function returns the mass edit form
	 * @param Vtiger_Request $request
	 */
	function showMassEditForm (Vtiger_Request $request){
		$moduleName = $request->getModule();
		$cvId = $request->get('viewname');
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');

		$viewer = $this->getViewer($request);

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_MASSEDIT);
		$fieldInfo = array();
		$fieldList = $moduleModel->getFields();
		foreach ($fieldList as $fieldName => $fieldModel) {
			$fieldInfo[$fieldName] = $fieldModel->getFieldInfo();
		}
		$picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($moduleName);

		$viewer->assign('PICKIST_DEPENDENCY_DATASOURCE',Zend_Json::encode($picklistDependencyDatasource));
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('MODE', 'massedit');
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('CVID', $cvId);
		$viewer->assign('SELECTED_IDS', $selectedIds);
		$viewer->assign('EXCLUDED_IDS', $excludedIds);
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('MODULE_MODEL',$moduleModel); 
		$viewer->assign('MASS_EDIT_FIELD_DETAILS',$fieldInfo); 
		$viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('MODULE_MODEL', $moduleModel);
        $searchKey = $request->get('search_key');
        $searchValue = $request->get('search_value');
		$operator = $request->get('operator');
        if(!empty($operator)) {
			$viewer->assign('OPERATOR',$operator);
			$viewer->assign('ALPHABET_VALUE',$searchValue);
            $viewer->assign('SEARCH_KEY',$searchKey);
		}
        $searchParams = $request->get('search_params');
        if(!empty($searchParams)) {
            $viewer->assign('SEARCH_PARAMS',$searchParams);
        }

		echo $viewer->view('MassEditForm.tpl',$moduleName,true);
	}
	
	/**
	 * Function returns the Add Comment form
	 * @param Vtiger_Request $request
	 */
	function showAddCommentForm(Vtiger_Request $request){
		$sourceModule = $request->getModule();
		$moduleName = 'ModComments';
		$cvId = $request->get('viewname');
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');

		$viewer = $this->getViewer($request);
		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('CVID', $cvId);
		$viewer->assign('SELECTED_IDS', $selectedIds);
		$viewer->assign('EXCLUDED_IDS', $excludedIds);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
        
        $searchKey = $request->get('search_key');
        $searchValue = $request->get('search_value');
		$operator = $request->get('operator');
        if(!empty($operator)) {
			$viewer->assign('OPERATOR',$operator);
			$viewer->assign('ALPHABET_VALUE',$searchValue);
            $viewer->assign('SEARCH_KEY',$searchKey);
		}

        $searchParams = $request->get('search_params');
        if(!empty($searchParams)) {
            $viewer->assign('SEARCH_PARAMS',$searchParams);
        }

		echo $viewer->view('AddCommentForm.tpl',$moduleName,true);
	}

	/**
	 * Function returns the Compose Email form
	 * @param Vtiger_Request $request
	 */
	function showComposeEmailForm(Vtiger_Request $request) {
		$moduleName = 'Emails';
		$sourceModule = $request->getModule();
		$cvId = $request->get('viewname');
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');
		$step = $request->get('step');
		$selectedFields = $request->get('selectedFields');
		$relatedLoad = $request->get('relatedLoad');

		$moduleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$emailFields = $moduleModel->getFieldsByType('email');
        $accesibleEmailFields = array();
        $emailColumnNames = array();
        $emailColumnModelMapping = array();

        foreach($emailFields as $index=>$emailField) {
            $fieldName = $emailField->getName();
            if($emailField->isViewable()) {
                $accesibleEmailFields[] = $emailField;
                $emailColumnNames[] = $emailField->get('column');
                $emailColumnModelMapping[$emailField->get('column')] = $emailField;
            }
        }
        $emailFields = $accesibleEmailFields;

        $emailFieldCount = count($emailFields);
        $tableJoined = array();
        if($emailFieldCount > 1) {
            $recordIds = $this->getRecordsListFromRequest($request);

            $moduleMeta = $moduleModel->getModuleMeta();
            $wsModuleMeta = $moduleMeta->getMeta();
            $tabNameIndexList = $wsModuleMeta->getEntityTableIndexList();

            $queryWithFromClause = 'SELECT '. implode(',',$emailColumnNames). ' FROM vtiger_crmentity ';
            foreach($emailFields as $emailFieldModel) {
                $fieldTableName = $emailFieldModel->table;
                if(in_array($fieldTableName, $tableJoined)){
                    continue;
                }

                $tableJoined[] = $fieldTableName;
                $queryWithFromClause .= ' INNER JOIN '.$fieldTableName .
                            ' ON '.$fieldTableName.'.'.$tabNameIndexList[$fieldTableName].'= vtiger_crmentity.crmid';
            }
            $query =  $queryWithFromClause . ' WHERE vtiger_crmentity.deleted = 0 AND crmid IN ('.  generateQuestionMarks($recordIds).') AND (';

            for($i=0; $i<$emailFieldCount;$i++) {
                for($j=($i+1);$j<$emailFieldCount;$j++){
                    $query .= ' (' . $emailFields[$i]->getName() .' != \'\' and '. $emailFields[$j]->getName().' != \'\')';
                    if(!($i == ($emailFieldCount-2) && $j == ($emailFieldCount-1))) {
                        $query .= ' or ';
                    }
                }
            }
            $query .=') LIMIT 1';

            $db = PearDatabase::getInstance();
            $result = $db->pquery($query,$recordIds);

            $num_rows = $db->num_rows($result);

            if($num_rows == 0) {
                $query = $queryWithFromClause . ' WHERE vtiger_crmentity.deleted = 0 AND crmid IN ('.  generateQuestionMarks($recordIds).') AND (';
                foreach($emailColumnNames as $index =>$columnName) {
                    $query .= " $columnName != ''";
                    //add glue or untill unless it is the last email field
                    if($index != ($emailFieldCount -1 ) ){
                        $query .= ' or ';
                    }
                }
                $query .= ') LIMIT 1';
                $result = $db->pquery($query, $recordIds);
                if($db->num_rows($result) > 0) {
                    //Expecting there will atleast one row 
                    $row = $db->query_result_rowdata($result,0);

                    foreach($emailColumnNames as $emailColumnName) {
                        if(!empty($row[$emailColumnName])) {
                            //To send only the single email field since it is only field which has value
                            $emailFields = array($emailColumnModelMapping[$emailColumnName]);
                            break;
                        }
                    }
                }else{
                    //No Record which has email field value
                    foreach($emailColumnNames as $emailColumnName) {
                        //To send only the single email field since it has no email value
                        $emailFields = array($emailColumnModelMapping[$emailColumnName]);
                        break;
                    }
                }
            }
        }

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
        $viewer->assign('SOURCE_MODULE',$sourceModule);
		$viewer->assign('VIEWNAME', $cvId);
		$viewer->assign('SELECTED_IDS', $selectedIds);
		$viewer->assign('EXCLUDED_IDS', $excludedIds);
		$viewer->assign('EMAIL_FIELDS', $emailFields);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
        
        $searchKey = $request->get('search_key');
        $searchValue = $request->get('search_value');
		$operator = $request->get('operator');
        if(!empty($operator)) {
			$viewer->assign('OPERATOR',$operator);
			$viewer->assign('ALPHABET_VALUE',$searchValue);
            $viewer->assign('SEARCH_KEY',$searchKey);
		}
        
        $searchParams = $request->get('search_params');
        if(!empty($searchParams)) {
            $viewer->assign('SEARCH_PARAMS',$searchParams);
        }

		$to = $request->get('to');
		if (!$to) {
			$to = array();
		}
		$viewer->assign('TO', $to);

		$parentModule = $request->get('sourceModule');
		$parentRecord = $request->get('sourceRecord');
		if (!empty($parentModule)) {
			$viewer->assign('PARENT_MODULE', $parentModule);
			$viewer->assign('PARENT_RECORD', $parentRecord);
			$viewer->assign('RELATED_MODULE', $sourceModule);
		}
		if($relatedLoad){
			$viewer->assign('RELATED_LOAD', true);
		}

		if($step == 'step1') {
			echo $viewer->view('SelectEmailFields.tpl', $moduleName, true);
			exit;
		}
	}

	/**
	 * Function shows form that will lets you send SMS
	 * @param Vtiger_Request $request
	 */
	function showSendSMSForm(Vtiger_Request $request) {

		$sourceModule = $request->getModule();
		$moduleName = 'SMSNotifier';
		$selectedIds = $this->getRecordsListFromRequest($request);
		$excludedIds = $request->get('excluded_ids');
		$cvId = $request->get('viewname');

		$user = Users_Record_Model::getCurrentUserModel();
        $moduleModel = Vtiger_Module_Model::getInstance($sourceModule);
        $phoneFields = $moduleModel->getFieldsByType('phone');
		$viewer = $this->getViewer($request);
		
		if(count($selectedIds) == 1){
			$recordId = $selectedIds[0];
			$selectedRecordModel = Vtiger_Record_Model::getInstanceById($recordId, $sourceModule);
			$viewer->assign('SINGLE_RECORD', $selectedRecordModel);
		}
		$viewer->assign('VIEWNAME', $cvId);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->assign('SELECTED_IDS', $selectedIds);
		$viewer->assign('EXCLUDED_IDS', $excludedIds);
		$viewer->assign('USER_MODEL', $user);
		$viewer->assign('PHONE_FIELDS', $phoneFields);
        
        $searchKey = $request->get('search_key');
        $searchValue = $request->get('search_value');
		$operator = $request->get('operator');
        if(!empty($operator)) {
			$viewer->assign('OPERATOR',$operator);
			$viewer->assign('ALPHABET_VALUE',$searchValue);
            $viewer->assign('SEARCH_KEY',$searchKey);
		}

        $searchParams = $request->get('search_params');
        if(!empty($searchParams)) {
            $viewer->assign('SEARCH_PARAMS',$searchParams);
        }
        
		echo $viewer->view('SendSMSForm.tpl', $moduleName, true);
	}

	/**
	 * Function returns the record Ids selected in the current filter
	 * @param Vtiger_Request $request
	 * @return integer
	 */
	function getRecordsListFromRequest(Vtiger_Request $request, $module = false) {
		$cvId = $request->get('viewname');
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');
        if(empty($module)) {
            $module = $request->getModule();
        }
		if(!empty($selectedIds) && $selectedIds != 'all') {
			if(!empty($selectedIds) && count($selectedIds) > 0) {
				return $selectedIds;
			}
		}
		
		$sourceRecord = $request->get('sourceRecord');
		$sourceModule = $request->get('sourceModule');
		if ($sourceRecord && $sourceModule) {
			$sourceRecordModel = Vtiger_Record_Model::getInstanceById($sourceRecord, $sourceModule);
			return $sourceRecordModel->getSelectedIdsList($module, $excludedIds);
		}

		$customViewModel = CustomView_Record_Model::getInstanceById($cvId);
		if($customViewModel) {
			$searchKey = $request->get('search_key');
			$searchValue = $request->get('search_value');
			$operator = $request->get('operator');
			if(!empty($operator)) {
				$customViewModel->set('operator', $operator);
				$customViewModel->set('search_key', $searchKey);
				$customViewModel->set('search_value', $searchValue);
			}
            $customViewModel->set('search_params', $request->get('search_params'));
			return $customViewModel->getRecordIds($excludedIds,$module);
		}
	}

	/**
	 * Function shows the List of Mail Merge Templates
	 * @param Vtiger_Request $request
	 */
	function showMailMergeTemplates(Vtiger_Request $request) {
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');
		$cvId = $request->get('viewname');
		$module = $request->getModule();
		$templates = Settings_MailMerge_Record_Model::getByModule($module);

		$viewer = $this->getViewer($request);
		$viewer->assign('TEMPLATES', $templates);
		$viewer->assign('SELECTED_IDS', $selectedIds);
		$viewer->assign('EXCLUDED_IDS', $excludedIds);
		$viewer->assign('VIEWNAME', $cvId);
		$viewer->assign('MODULE', $module);

		return $viewer->view('showMergeTemplates.tpl', $module);
	}

	/**
	 * Function shows the duplicate search form
	 * @param Vtiger_Request $request
	 */
	function showDuplicatesSearchForm(Vtiger_Request $request) {
		$module = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		$fields = $moduleModel->getFields();

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $module);
		$viewer->assign('FIELDS', $fields);
		$viewer->view('showDuplicateSearch.tpl', $module);
	}
	
	function transferOwnership(Vtiger_Request $request){
		$module = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($module);

		$relatedModules = $moduleModel->getRelations();
		//User doesn't have the permission to edit related module,
		//then don't show that module in related module list.
		foreach ($relatedModules as $key => $relModule) {
			if (!Users_Privileges_Model::isPermitted($relModule->get('relatedModuleName'), 'EditView')) {
				unset($relatedModules[$key]);
			}
		}
		
		$viewer = $this->getViewer($request);
		$skipModules = array('Emails');
		$viewer->assign('MODULE',$module);
		$viewer->assign('RELATED_MODULES', $relatedModules);
		$viewer->assign('SKIP_MODULES', $skipModules);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('TransferRecordOwnership.tpl', $module);
	}
}
