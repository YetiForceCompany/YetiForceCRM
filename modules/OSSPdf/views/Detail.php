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

class OSSPdf_Detail_View extends Vtiger_Detail_View {
	function preProcess(Vtiger_Request $request, $display=true) {
		$db = PearDatabase::getInstance();
		parent::preProcess($request, false);
		$origModuleName = $request->getModule();

		$recordId = $request->get('record');
		if(!$this->record){
			$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		}
		$recordModel = $this->record->getRecord();
		$recordStrucure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
	
		$summaryInfo = array();
		// Take first block information as summary information
		$stucturedValues = $recordStrucure->getStructure();
		foreach($stucturedValues as $blockLabel=>$fieldList) {
			$summaryInfo[$blockLabel] = $fieldList;
			break;
		}

		$detailViewLinkParams = array('MODULE'=>$moduleName,'RECORD'=>$recordId);

		$detailViewLinks = $this->record->getDetailViewLinks($detailViewLinkParams);
		$navigationInfo = ListViewSession::getListViewNavigation($recordId);
		
		$viewer = $this->getViewer($request);
		
		$modid = $recordModel->get('moduleid');
		$moduleNameResult = $db->query( "select name from vtiger_tab where tabid = '$modid'", true );
		$moduleName = $db->query_result($moduleNameResult, 0, 'name');
        
        $tName = vtranslate($moduleName, $moduleName);
		
		$viewer->assign('MODULEID_NAME', $tName);
		$viewer->assign('RECORD', $recordModel);
		//var_dump($recordModel);
		//exit;
		///Conditions
		vimport('~~modules/OSSPdf/helpers/Conditions.php');
		$baseModule = Vtiger_Functions::getModuleName($modid);
		$Condition = Conditions::getConditionRelToRecordFieldInfo($request->get('record'), $baseModule ); 
		$viewer->assign('REQUIRED_CONDITIONS', $Condition['required_conditions']);
		$viewer->assign('OPTIONAL_CONDITIONS', $Condition['optional_conditions']);
		$viewer->assign('BASE_MODULE', $baseModule);
		///Conditions
		$viewer->assign('NAVIGATION', $navigationInfo);

		//Intially make the prev and next records as null
		$prevRecordId = null;
		$nextRecordId = null;
		$found = false;
		if ($navigationInfo) {
			foreach($navigationInfo as $page=>$pageInfo) {
				foreach($pageInfo as $index=>$record) {
					//If record found then next record in the interation
					//will be next record
					if($found) {
						$nextRecordId = $record;
						break;
					}
					if($record == $recordId) {
						$found = true;
					}
					//If record not found then we are assiging previousRecordId
					//assuming next record will get matched
					if(!$found) {
						$prevRecordId = $record;
					}
				}
				//if record is found and next record is not calculated we need to perform iteration
				if($found && !empty($nextRecordId)) {
					break;
				}
			}
		}

		$moduleModel = Vtiger_Module_Model::getInstance($origModuleName);
		if(!empty($prevRecordId)) {
			$viewer->assign('PREVIOUS_RECORD_URL', $moduleModel->getDetailViewUrl($prevRecordId));
		}
		if(!empty($nextRecordId)) {
			$viewer->assign('NEXT_RECORD_URL', $moduleModel->getDetailViewUrl($nextRecordId));
		}

		$viewer->assign('MODULE_MODEL', $this->record->getModule());
		$viewer->assign('DETAILVIEW_LINKS', $detailViewLinks);
		$viewer->assign('MODULE', $request->getModule());

		$viewer->assign('IS_EDITABLE', $this->record->getRecord()->isEditable($moduleName));
		$viewer->assign('IS_DELETABLE', $this->record->getRecord()->isDeletable($moduleName));
		$viewer->assign('URL_CONTENT', "index.php?module=OSSPdf&view=content&record=$recordId");
		$viewer->assign('URL_FOOTER', "index.php?module=OSSPdf&view=footer&record=$recordId");
		$viewer->assign('URL_HEADER', "index.php?module=OSSPdf&view=header&record=$recordId");
		$linkParams = array('MODULE'=>$moduleName, 'ACTION'=>$request->get('view'));
		$linkModels = $this->record->getSideBarLinks($linkParams);
		$viewer->assign('QUICK_LINKS', $linkModels);

		if($display) {
			$this->preProcessDisplay($request);
		}
	}

	function preProcessTplName(Vtiger_Request $request) {
		return 'DetailViewPreProcess.tpl';
	}

	function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
		echo $this->showModuleBasicView($request);
	}
    
    public function showDetailViewByMode(Vtiger_Request $request) {
        
        $viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $request->getModule());
        
        return parent::showDetailViewByMode($request);
    }

	public function postProcess(Vtiger_Request $request) {
		$recordId = $request->get('record');
		$moduleName = $request->getModule();
		if(!$this->record){
			$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		}
		$detailViewLinkParams = array('MODULE'=>$moduleName,'RECORD'=>$recordId);
		$detailViewLinks = $this->record->getDetailViewLinks($detailViewLinkParams);

		$selectedTabLabel = $request->get('tab_label');
		if(empty($selectedTabLabel) && !empty($detailViewLinks['DETAILVIEWTAB']) &&
				!empty($detailViewLinks['DETAILVIEWTAB'][0])) {
			$selectedTabLabel = $detailViewLinks['DETAILVIEWTAB'][0]->getLabel();
		}

		$viewer = $this->getViewer($request);

		$viewer->assign('SELECTED_TAB_LABEL', $selectedTabLabel);
		$viewer->assign('MODULE_MODEL', $this->record->getModule());
		$viewer->assign('DETAILVIEW_LINKS', $detailViewLinks);

		parent::postProcess($request);
	}
	
	/**
	 * Function shows the entire detail for the record
	 * @param Vtiger_Request $request
	 * @return <type>
	 */

}
