<?php

/**
 * Upload a logo
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class Settings_Roles_UploadLogo_Action extends Settings_Vtiger_Index_Action
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('upload');
	}

	public function upload(\App\Request $request)
	{
		$targetFile = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'public_html' . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'Logo' . DIRECTORY_SEPARATOR . 'logo';
		$uploadOk = 1;
		$fileInstance = \App\Fields\File::loadFromPath($_FILES['role_logo']['tmp_name']);

		if (!$fileInstance->validate('image')) {
			$uploadOk = 0;
		}
		if ($uploadOk && $fileInstance->getSize() > \AppConfig::main('upload_maxsize')) {
			$uploadOk = 0;
		}
		$response = new Vtiger_Response();
		if ($uploadOk) {
			if (file_exists($targetFile)) {
				unlink($targetFile);
			}
			if (!$fileInstance->moveFile($targetFile)) {
				$uploadOk = 0;
			}
		}
		if ($uploadOk) {
			$result = ['success' => true];
		} else {
			$result = ['success' => false, 'message' => \App\Language::translate('LBL_UPLOAD_ERROR', $request->getModule(false))];
		}
		$response->setResult($result);
		$response->emit();
	}
}
