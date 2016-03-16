<?php

/**
 * EditFieldByModal View Class for Assets
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Assets_EditFieldByModal_View extends Vtiger_EditFieldByModal_View
{

	function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$id = $request->get('record');

		$recordModel = Vtiger_DetailView_Model::getInstance($moduleName, $id)->getRecord();
		$recordStrucure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
		$structuredValues = $recordStrucure->getStructure();
		$fields = [];
		foreach ($structuredValues as $fildsInBlock) {
			$fields = array_merge($fields, $fildsInBlock);
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('FIELD_LIST', $fields);
		$viewer->assign('SHOW_FIELDS', array_keys($recordModel->getModule()->getQuickCreateFields()));
		$viewer->assign('RESTRICTS_ITEM', $this->getRestrictItems());
		$this->preProcess($request);
		$viewer->view('EditFieldByModal.tpl', $moduleName);
		$this->postProcess($request);
	}
}
