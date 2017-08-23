<?php

/**
 * EditFieldByModal View Class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_EditFieldByModal_View extends Vtiger_BasicModal_View
{

	protected $showFields = [];
	protected $restrictItems = [];

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('record');
		if (!$recordId) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
		if (!\App\Privilege::isPermitted($moduleName, 'Save', $recordId)) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
	}

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('record');

		$recordModel = Vtiger_DetailView_Model::getInstance($moduleName, $recordId)->getRecord();
		$recordStrucure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_SUMMARY);
		$structuredValues = $recordStrucure->getStructure();

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('SHOW_FIELDS', $this->getFieldsToShow());
		$viewer->assign('RECORD_STRUCTURE', $structuredValues);
		$viewer->assign('RESTRICTS_ITEM', $this->getRestrictItems());
		$viewer->assign('CONDITION_TO_RESTRICTS', $this->getConditionToRestricts($moduleName, $recordId));
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
