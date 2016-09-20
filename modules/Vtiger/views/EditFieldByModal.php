<?php

/**
 * EditFieldByModal View Class
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_EditFieldByModal_View extends Vtiger_BasicModal_View
{

	protected $showFields = [];
	protected $restrictItems = [];

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		$recordPermission = Users_Privileges_Model::isPermitted($moduleName, 'Save', $recordId);
		if (!$recordPermission) {
			throw new \Exception\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
		return true;
	}

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$ID = $request->get('record');

		$recordModel = Vtiger_DetailView_Model::getInstance($moduleName, $ID)->getRecord();
		$recordStrucure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_SUMMARY);
		$structuredValues = $recordStrucure->getStructure();

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('SHOW_FIELDS', $this->getFieldsToShow());
		$viewer->assign('RECORD_STRUCTURE', $structuredValues);
		$viewer->assign('RESTRICTS_ITEM', $this->getRestrictItems());
		$viewer->assign('CONDITION_TO_RESTRICTS', $this->getConditionToRestricts($moduleName, $ID));
		$this->preProcess($request);
		$viewer->view('EditFieldByModal.tpl', $moduleName);
		$this->postProcess($request);
	}

	public function getFieldsToShow()
	{
		return $this->showFields;
	}

	public function getRestrictItems()
	{
		return $this->restrictItems;
	}

	public function getConditionToRestricts($moduleName, $ID)
	{
		return true;
	}
}
