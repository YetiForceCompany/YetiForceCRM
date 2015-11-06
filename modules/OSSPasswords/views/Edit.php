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
Class OSSPasswords_Edit_View extends Vtiger_Edit_View {
    /**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getFooterScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getFooterScripts($request);

		$jsFileNames = array(
			'modules.OSSPasswords.resources.gen_pass',
            'libraries.jquery.ZeroClipboard.ZeroClipboard',
            'modules.OSSPasswords.resources.zClipDetailView'
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($jsScriptInstances, $headerScriptInstances);
		return $headerScriptInstances;
	}

	public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();
		$record = $request->get('record');
        if(!empty($record) && $request->get('isDuplicate') == true) {
            $recordModel = $this->record?$this->record:Vtiger_Record_Model::getInstanceById($record, $moduleName);
            $viewer->assign('MODE', '');
        }else if(!empty($record)) {
            $recordModel = $this->record?$this->record:Vtiger_Record_Model::getInstanceById($record, $moduleName);
            $viewer->assign('RECORD_ID', $record);
            $viewer->assign('MODE', 'edit');
        } else {
            $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
            $viewer->assign('MODE', '');
        }
        if(!$this->record){
            $this->record = $recordModel;
        }
        
		$moduleModel = $recordModel->getModule();
		$fieldList = $moduleModel->getFields();
		$requestFieldList = array_intersect_key($request->getAll(), $fieldList);

		foreach($requestFieldList as $fieldName=>$fieldValue){
			$fieldModel = $fieldList[$fieldName];
			$specialField = false;
			// We collate date and time part together in the EditView UI handling 
			// so a bit of special treatment is required if we come from QuickCreate 
			if ($moduleName == 'Calendar' && empty($record) && $fieldName == 'time_start' && !empty($fieldValue)) { 
				$specialField = true; 
				// Convert the incoming user-picked time to GMT time 
				// which will get re-translated based on user-time zone on EditForm 
				$fieldValue = DateTimeField::convertToDBTimeZone($fieldValue)->format("H:i"); 
                
			}
            
            if ($moduleName == 'Calendar' && empty($record) && $fieldName == 'date_start' && !empty($fieldValue)) { 
                $startTime = Vtiger_Time_UIType::getTimeValueWithSeconds($requestFieldList['time_start']);
                $startDateTime = Vtiger_Datetime_UIType::getDBDateTimeValue($fieldValue." ".$startTime);
                list($startDate, $startTime) = explode(' ', $startDateTime);
                $fieldValue = Vtiger_Date_UIType::getDisplayDateValue($startDate);
            }
			if($fieldModel->isEditable() || $specialField) {
				$recordModel->set($fieldName, $fieldModel->getDBInsertValue($fieldValue));
			}
		}
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
		$picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($moduleName);

		$viewer->assign('PICKIST_DEPENDENCY_DATASOURCE',Zend_Json::encode($picklistDependencyDatasource));
		$mappingRelatedField = $moduleModel->getMappingRelatedField($moduleName);
		$viewer->assign('MAPPING_RELATED_FIELD',Zend_Json::encode($mappingRelatedField));
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('RECORD_MODEL', $recordModel);
		$isRelationOperation = $request->get('relationOperation');

		//if it is relation edit
		$viewer->assign('IS_RELATION_OPERATION', $isRelationOperation);
		if($isRelationOperation) {
			$viewer->assign('SOURCE_MODULE', $request->get('sourceModule'));
			$viewer->assign('SOURCE_RECORD', $request->get('sourceRecord'));
		}
		$viewer->assign('MAX_UPLOAD_LIMIT_MB', vglobal('upload_maxsize')/1000000);
		$viewer->assign('MAX_UPLOAD_LIMIT', vglobal('upload_maxsize'));
        
        // check if passwords are encrypted
        if ( file_exists( 'modules/OSSPasswords/config.ini' ) ) {   // encryption key exists so passwords are encrypted
            $config = parse_ini_file( 'modules/OSSPasswords/config.ini' );
            
            // let smarty know that passwords are encrypted
            $viewer->assign('ENCRYPTED', true);
            $viewer->assign('ENC_KEY', $config['key']);
            $viewer->assign('RECORD', $_GET['record']);
            $viewer->assign('VIEW', $_GET['view']);
        }
        else {
            $viewer->assign('ENCRYPTED', false);
            $viewer->assign('ENC_KEY', '');
            $viewer->assign('RECORD', $_GET['record']);
            $viewer->assign('VIEW', $_GET['view']);
        }
        
        // widget button
        // get min, max, allow_chars from vtiger_passwords_config
        $result = $adb->query( "SELECT * FROM vtiger_passwords_config WHERE 1 LIMIT 1", true );
        $min = $adb->query_result($result, 0, 'pass_length_min');
        $max = $adb->query_result($result, 0, 'pass_length_max');
        $allow_chars = $adb->query_result($result, 0, 'pass_allow_chars');
        
        $GenerateButton = 'Generate Password';
        $ConfigureButton = 'LBL_ConfigurePass';
		$viewer = $this->getViewer ($request);
		$viewer->assign('GENERATEPASS', $GenerateButton);
        $viewer->assign('GENERATEONCLICK', 'generate_password('.$min.','.$max.',\''.$allow_chars.'\');');        
        
		$viewer->view('EditView.tpl', $moduleName);
	}
}
