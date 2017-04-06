<?php
/*
 * Basic class to handle files
 * @package YetiForce.Files
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Basic class to handle files
 */
class Vtiger_Basic_File
{

	public $storageName = '';

	public function getCheckPermission(Vtiger_Request $request)
	{
		return true;
	}

	public function postCheckPermission(Vtiger_Request $request)
	{
		return true;
	}

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

	public function getStorageName()
	{
		return $this->storageName;
	}

	public function getFileType()
	{
		return $this->fileType;
	}
}
