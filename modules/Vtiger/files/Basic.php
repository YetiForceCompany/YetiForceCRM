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
	 * @param Vtiger_Request $request
	 * @return boolean
	 */
	public function getCheckPermission(Vtiger_Request $request)
	{
		return true;
	}

	/**
	 * Checking permission in post method
	 * @param Vtiger_Request $request
	 * @return boolean
	 */
	public function postCheckPermission(Vtiger_Request $request)
	{
		return true;
	}

	/**
	 * Get and save files
	 * @param Vtiger_Request $request
	 */
	public function post(Vtiger_Request $request)
	{
		$attachIds = [];
		$files = Vtiger_Util_Helper::transformUploadedFiles($_FILES, true);
		foreach ($files as $key => $file) {
			foreach ($file as $key => $fileData) {
				$result = \Vtiger_Files_Model::uploadAndSave($fileData, $this->getFileType(), $this->getStorageName());
				if ($result) {
					$attachIds[] = $result;
				}
			}
		}
		if ($request->isAjax()) {
			$response = new Vtiger_Response();
			$response->setResult([
				'inputName' => $request->get('inputName'),
				'module' => $request->getModule(),
				'attachIds' => $attachIds
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
