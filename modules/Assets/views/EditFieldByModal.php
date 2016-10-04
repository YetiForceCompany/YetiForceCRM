<?php

/**
 * EditFieldByModal View Class for Assets
 * @package YetiForce.ModalView
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Assets_EditFieldByModal_View extends Vtiger_EditFieldByModal_View
{

	public function getSize(Vtiger_Request $request)
	{
		return 'modal-fullscreen';
	}

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$ID = $request->get('record');

		$recordModel = Vtiger_DetailView_Model::getInstance($moduleName, $ID)->getRecord();
		$recordStrucure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
		$structuredValues = $recordStrucure->getStructure();
		$fields = [];
		foreach ($structuredValues as $fildsInBlock) {
			$fields = array_merge($fields, $fildsInBlock);
		}
		$showFields = array_keys($recordModel->getModule()->getQuickCreateFields());
		$configureFields = AppConfig::module($moduleName, 'SHOW_FIELD_IN_MODAL');
		if($configureFields){
			$showFields = array_merge($showFields, $configureFields);
		}

		$relationData = AppConfig::module($moduleName, 'SHOW_RELATION_IN_MODAL');
		$relationsModules = [];
		$relationModels = [];
		if ($relationData) {
			$relatedModuleBasicName = $relationData['module'];
			$relationsModuleName = $relationData['relatedModule'];
			$relatedRecord = $recordModel->get($relationData['relationField']);
			$metaData = vtlib\Functions::getCRMRecordMetadata($relatedRecord);
			if ($relatedRecord && $metaData && $metaData['setype'] == $relatedModuleBasicName && $metaData['deleted'] == 0 && Users_Privileges_Model::isPermitted($relatedModuleBasicName, 'DetailView', $relatedRecord)) {
				$relatedModuleBasic = Vtiger_Module_Model::getInstance($relatedModuleBasicName);
				foreach ($relationsModuleName as $relationModuleName) {
					$relatedModuleModel = Vtiger_Module_Model::getInstance($relationModuleName);
					$relationModels[$relationModuleName] = Vtiger_Relation_Model::getInstance($relatedModuleBasic, $relatedModuleModel);
					if (!empty($relationModels[$relationModuleName])) {
						$relationsModules[] = $relationModuleName;
					}
				}
			}
		}
		$hierarchy = AppConfig::module($moduleName, 'SHOW_HIERARCHY_IN_MODAL');
		$hierarchyId = '';
		if ($hierarchy !== false) {
			$hierarchyModuleName = 'Accounts';
			foreach ($fields as $fieldName => $fieldModel) {
				if ($fieldModel->isReferenceField() && in_array($hierarchyModuleName, $fieldModel->getReferenceList())) {
					$hierarchyId = $recordModel->has($fieldModel->getName()) ? $recordModel->get($fieldModel->getName()) : '';
				}
			}
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('FIELD_LIST', $fields);
		$viewer->assign('SHOW_FIELDS', $showFields);
		$viewer->assign('RESTRICTS_ITEM', $this->getRestrictItems());
		$viewer->assign('RELATED_RECORD', $relatedRecord);
		$viewer->assign('RELATED_RECORD_METADATA', $metaData);
		$viewer->assign('RELATED_MODULE_BASIC', $relatedModuleBasicName);
		$viewer->assign('RELATED_MODULE', $relationsModules);
		$viewer->assign('RELATED_EXISTS', $relationsModules ? true : false);
		$viewer->assign('HIERARCHY_ID', $hierarchyId);
		$viewer->assign('HIERARCHY_FIELD', $hierarchy);
		$this->preProcess($request);
		$viewer->view('EditFieldByModal.tpl', $moduleName);
		$this->postProcess($request);
	}
}
