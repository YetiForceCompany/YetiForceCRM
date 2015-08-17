<?php

/**
 * Supplies Edit View Class
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Supplies_Edit_View extends Vtiger_Edit_View
{
	public function preProcess (Vtiger_Request $request, $display=true) {
		parent::preProcess($request, $display);
		
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->view('TopEditView.tpl', Supplies_Module_Model::getModuleNameForTpl('TopEditView.tpl', $moduleName));
	}
	
	public function process(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$record = $request->get('record');
		if (!empty($record) && $request->get('isDuplicate') == true) {
			$recordModel = $this->record ? $this->record : Vtiger_Record_Model::getInstanceById($record, $moduleName);
			$viewer->assign('MODE', '');
			$recordModel->set('id', '');
			//While Duplicating record, If the related record is deleted then we are removing related record info in record model
			$mandatoryFieldModels = $recordModel->getModule()->getMandatoryFieldModels();
			foreach ($mandatoryFieldModels as $fieldModel) {
				if ($fieldModel->isReferenceField()) {
					$fieldName = $fieldModel->get('name');
					if (Vtiger_Util_Helper::checkRecordExistance($recordModel->get($fieldName))) {
						$recordModel->set($fieldName, '');
					}
				}
			}
		} else if (!empty($record)) {
			$recordModel = $this->record ? $this->record : Vtiger_Record_Model::getInstanceById($record, $moduleName);
			$viewer->assign('RECORD_ID', $record);
			$viewer->assign('MODE', 'edit');
		} else {
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			$referenceId = $request->get('reference_id');
			if ($referenceId) {
				$parentRecordModel = Vtiger_Record_Model::getInstanceById($referenceId);
				$recordModel->setRecordFieldValues($parentRecordModel);
			}
			$viewer->assign('MODE', '');
		}
		if (!$this->record) {
			$this->record = $recordModel;
		}

		$moduleModel = $recordModel->getModule();
		$fieldList = $moduleModel->getFields();
		$requestFieldList = array_intersect_key($request->getAll(), $fieldList);

		foreach ($requestFieldList as $fieldName => $fieldValue) {
			$fieldModel = $fieldList[$fieldName];
			$specialField = false;
			if ($fieldModel->isEditable() || $specialField) {
				$recordModel->set($fieldName, $fieldModel->getDBInsertValue($fieldValue));
			}
		}
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
		$picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($moduleName);

		$viewer->assign('PICKIST_DEPENDENCY_DATASOURCE', Zend_Json::encode($picklistDependencyDatasource));
		$mappingRelatedField = $moduleModel->getMappingRelatedField($moduleName);
		$viewer->assign('MAPPING_RELATED_FIELD', Zend_Json::encode($mappingRelatedField));
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('APIADDRESS', Settings_ApiAddress_Module_Model::getInstance('Settings:ApiAddress')->getConfig());

		$isRelationOperation = $request->get('relationOperation');

		//if it is relation edit
		$viewer->assign('IS_RELATION_OPERATION', $isRelationOperation);
		if ($isRelationOperation) {
			$viewer->assign('SOURCE_MODULE', $request->get('sourceModule'));
			$viewer->assign('SOURCE_RECORD', $request->get('sourceRecord'));
		}

		$viewer->assign('MAX_UPLOAD_LIMIT_MB', Vtiger_Util_Helper::getMaxUploadSize());
		$viewer->assign('MAX_UPLOAD_LIMIT', vglobal('upload_maxsize'));
		
		// Supplies block
		$viewer->assign('SUPFIELD_MODEL', Supplies_SupField_Model::getCleanInstance());
		$viewer->assign('ACCOUNT_REFERENCE_FIELD', $this->getReferenceField($moduleName));
		$viewer->assign('DISCOUNTS_CONFIG', Products_Record_Model::getDiscountsConfig());
		$viewer->assign('TAXS_CONFIG', Products_Record_Model::getTaxsConfig());
		$viewer->view('EditView.tpl', $moduleModel->getModuleNameForTpl('EditView.tpl', $moduleName));
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);

		$moduleName = $request->getModule();
		$moduleEditFile = 'modules.' . $moduleName . '.resources.Edit';
		unset($headerScriptInstances[$moduleEditFile]);

		$jsFileNames = array(
			'modules.Supplies.resources.Edit',
		);
		$jsFileNames[] = $moduleEditFile;
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
	
	public function getReferenceField($moduleName)
	{
		$mainModule = 'Accounts';

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$modelFields = $moduleModel->getFields();
		$referenceField = '';
		foreach ($modelFields as $fieldName => $fieldModel) {
			if ($fieldModel->getFieldDataType() == Vtiger_Field_Model::REFERENCE_TYPE) {
				$referenceList = $fieldModel->getReferenceList();
				if (in_array($mainModule, $referenceList)) {
					$referenceField = $fieldName;
					break;
				}
			}
		}

		return $referenceField;
	}
}
