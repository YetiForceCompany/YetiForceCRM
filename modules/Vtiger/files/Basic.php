<?php
/**
 * Basic class to handle files
 * @package YetiForce.Files
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Basic class to handle files
 */
abstract class Vtiger_Basic_File
{

	/**
	 * Storage name
	 * @var string 
	 */
	public $storageName = '';

	/**
	 * Checking permission in get method
	 * @param \App\Request $request
	 * @return boolean
	 */
	public function getCheckPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->get('record');
		$field = $request->get('field');
		if ($record) {
			if (!\App\Privilege::isPermitted($moduleName, 'DetailView', $record) || !\App\Field::getFieldPermission($moduleName, $field)) {
				throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
			}
		} else {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
		return true;
	}

	/**
	 * Checking permission in post method
	 * @param \App\Request $request
	 * @return boolean
	 */
	public function postCheckPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->get('record');
		$field = $request->get('field');
		if (!empty($record)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
			if (!$recordModel->isEditable() || !\App\Field::getFieldPermission($moduleName, $field, false)) {
				throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
			}
		} else {
			if (!\App\Field::getFieldPermission($moduleName, $field, false) || !\App\Privilege::isPermitted($moduleName, 'CreateView')) {
				throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
			}
		}
		return true;
	}

	/**
	 * Get and save files
	 * @param \App\Request $request
	 */
	public function post(\App\Request $request)
	{
		$attachIds = [];
		$files = Vtiger_Util_Helper::transformUploadedFiles($_FILES, true);
		foreach ($files as $key => $file) {
			foreach ($file as $key => $fileData) {
				$result = \Vtiger_Files_Model::uploadAndSave($fileData, $this->getFileType(), $this->getStorageName());
				if ($result) {
					$attach[] = ['id' => $result, 'name' => $fileData['name']];
				}
			}
		}
		if ($request->isAjax()) {
			$response = new Vtiger_Response();
			$response->setResult([
				'field' => $request->get('field'),
				'module' => $request->getModule(),
				'attach' => $attach
			]);
			$response->emit();
		}
	}

	/**
	 * Get storage name
	 * @return string
	 */
	public function getStorageName()
	{
		return $this->storageName;
	}

	/**
	 * Get file type
	 * @return string
	 */
	public function getFileType()
	{
		return $this->fileType;
	}
}
