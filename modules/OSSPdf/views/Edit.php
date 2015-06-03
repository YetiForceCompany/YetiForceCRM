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
class OSSPdf_Edit_View extends Vtiger_Edit_View {
    public function process(Vtiger_Request $request) {
		$db = PearDatabase::getInstance();
        $viewer = $this->getViewer ($request);
        $moduleName = $request->getModule();
        $record = $request->get('record');

        if(!empty($record) && $request->get('isDuplicate') == true) {
            $recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
            $viewer->assign('MODE', '');
        }
        else if(!empty($record)) {
            $recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
            $viewer->assign('RECORD_ID', $record);
            $viewer->assign('MODE', 'edit');
        }
        else {
            $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
            $viewer->assign('MODE', '');
        }

        $moduleModel = $recordModel->getModule();
        $fieldList = $moduleModel->getFields();
        $requestFieldList = array_intersect_key($request->getAll(), $fieldList);

        foreach($requestFieldList as $fieldName=>$fieldValue){
            $fieldModel = $fieldList[$fieldName];
            if($fieldModel->isEditable()) {
                $recordModel->set($fieldName, $fieldValue);
            }
        }
		
		if($request->get('record') != ''){
			$rid = $request->get('record');
			$mresult = $db->query("SELECT moduleid FROM vtiger_osspdf WHERE osspdfid = '$rid'", true);
			$moduleid = $db->query_result($mresult, 0, 'moduleid');			
		}
		else {
			$moduleid = '2';
		}
		$pobierz = $db->query( "select name from vtiger_tab where tabid = '$moduleid'", true );
        $modulename = $db->query_result( $pobierz,0,"name" );

        $chosen_module = $modulename;

        $pobierz_bloki = $db->query( "select blockid, blocklabel from vtiger_blocks where tabid = '$moduleid'", true );
		
        $field_list = Array();
        for( $k=0; $k< $db->num_rows( $pobierz_bloki ); $k++ ) {
            $blockid = $db->query_result( $pobierz_bloki, $k, "blockid" );
            $label = $db->query_result( $pobierz_bloki, $k, "blocklabel" );
            $pobierz_pola = $db->query( "select fieldname, fieldlabel from vtiger_field where block = '$blockid' and tabid = '$moduleid'", true );

            for( $i=0; $i< $db->num_rows( $pobierz_pola ); $i++ )
            {
                $field_list[vtranslate( $label, $modulename )][$i]['name'] = $db->query_result( $pobierz_pola, $i, "fieldname" );
                $field_list[vtranslate( $label, $modulename )][$i]['label'] = vtranslate($db->query_result( $pobierz_pola, $i, "fieldlabel" ), $modulename);
            }
        }		

        $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
		///Conditions
		vimport('~~modules/OSSPdf/helpers/Conditions.php');
		$baseModule = Vtiger_Functions::getModuleName($moduleid);
		$Condition = Conditions::getConditionRelToRecordFieldInfo($request->get('record'), $baseModule ); 
		$viewer->assign('CONDITION_BY_TYPE', Conditions::getConditionByType());
		$viewer->assign('REQUIRED_CONDITIONS', $Condition['required_conditions']);
		$viewer->assign('OPTIONAL_CONDITIONS', $Condition['optional_conditions']);
		$viewer->assign('FIELD_LIST', Conditions::getListBaseModuleField($baseModule));
		$viewer->assign('BASE_MODULE', $baseModule);
		$viewer->assign('RECORD', $recordModel);
		///Conditions
        $viewer->assign('DEFAULT_FIELDS', $field_list);
        $viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
        $viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('CURRENTDATE', date('Y-n-j'));
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$mappingRelatedField = $moduleModel->getMappingRelatedField($moduleName);
		$viewer->assign('MAPPING_RELATED_FIELD',Zend_Json::encode($mappingRelatedField));
        $isRelationOperation = $request->get('relationOperation');

        //if it is relation edit
        $viewer->assign('IS_RELATION_OPERATION', $isRelationOperation);
        if($isRelationOperation) {
            $viewer->assign('SOURCE_MODULE', $request->get('sourceModule'));
            $viewer->assign('SOURCE_RECORD', $request->get('sourceRecord'));
        }

        $viewer->assign('MAX_UPLOAD_LIMIT_MB', vglobal('upload_maxsize')/1000000);
        $viewer->assign('MAX_UPLOAD_LIMIT', vglobal('upload_maxsize'));
        $viewer->view('EditView.tpl', $moduleName);
    }
    
     /**
     * Function to get the list of Script models to be included
     * @param Vtiger_Request $request
     * @return <Array> - List of Vtiger_JsScript_Model instances
     */
    function getFooterScripts(Vtiger_Request $request) {
        $headerScriptInstances = parent::getFooterScripts($request);
        $moduleName = $request->getModule();
        
        $jsFileNames = array(
            "libraries.jquery.ZeroClipboard.ZeroClipboard",
            "modules.$moduleName.resources.PDFUtils",
            "modules.$moduleName.resources.utils",
			"modules.$moduleName.resources.Condition",
            "modules.$moduleName.resources.ckeditor.ckeditor",
            "modules.$moduleName.resources.ckeditor.adapters.jquery",
            "libraries.jquery.jquery_windowmsg"
        );
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
        return $headerScriptInstances;
    }
}
