<?php

/**
 * Modal View Class for SRequirementsCards
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class SRequirementsCards_Modal_View extends Vtiger_BasicModal_View
{

	function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		$recordPermission = Users_Privileges_Model::isPermitted($moduleName, 'Save', $recordId);
		if (!$recordPermission) {
			throw new NoPermittedToRecordException('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
		return true;
	}

	public function preProcess(Vtiger_Request $request)
	{
		echo '<div class="modal fade modalEditStatus" id="sRequirementsCardsModal"><div class="modal-dialog"><div class="modal-content">';
	}

	function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$id = $request->get('record');

		$recordModel = Vtiger_DetailView_Model::getInstance($moduleName, $id)->getRecord();
		$recordStrucure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
		$structuredValues = $recordStrucure->getStructure();

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('RECORD_STRUCTURE', $structuredValues);
		$viewer->assign('RESTRICTS_ITEM', ['PLL_DISCARDED', 'PLL_ACCEPTED']);
		$this->preProcess($request);
		$viewer->view('Modal.tpl', $moduleName);
		$this->postProcess($request);
	}
}
