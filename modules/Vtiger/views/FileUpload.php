<?php
/**
 * FileUpload View Class
 * @package YetiForce.ModalView
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * FileUpload view class
 */
class Vtiger_FileUpload_View extends Vtiger_BasicModal_View
{

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermitted
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->getInteger('record');
		$fieldName = $request->getByType('inputName', 1);
		if (!empty($record)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
			if (!$recordModel->isEditable() || !\App\Field::getFieldPermission($moduleName, $fieldName, false)) {
				throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		} else {
			if (!\App\Field::getFieldPermission($moduleName, $fieldName, false) || !\App\Privilege::isPermitted($moduleName, 'CreateView')) {
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
			}
		}
	}

	/**
	 * Process
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$this->preProcess($request);
		$viewer->assign('INPUT_NAME', $request->getByType('inputName', 1));
		$viewer->assign('FILE_TYPE', $request->get('fileType'));
		$viewer->assign('RECORD', $request->getInteger('record'));
		$viewer->view('FileUpload.tpl', $moduleName);
		$this->postProcess($request);
	}

	/**
	 * Get scripts for modal window
	 * @param \App\Request $request
	 * @return Vtiger_JsScript_Model[]
	 */
	public function getModalScripts(\App\Request $request)
	{
		$headerScriptInstances = parent::getModalScripts($request);
		$scripts = [
			'libraries.js.multiplefileupload.jquery_MultiFile'
		];
		$scriptInstances = $this->checkAndConvertJsScripts($scripts);
		return array_merge($scriptInstances, $headerScriptInstances);
	}
}
