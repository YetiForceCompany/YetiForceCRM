<?php

/**
 * Upload a logo.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class Settings_Roles_UploadLogo_Action extends Settings_Vtiger_Index_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$targetFile = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'public_html' . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'Logo' . DIRECTORY_SEPARATOR . 'logo';
		$response = new Vtiger_Response();
		$result = ['success' => false, 'message' => \App\Language::translate('LBL_UPLOAD_ERROR', $request->getModule(false))];
		if (!empty($_FILES['role_logo'])) {
			$fileInstance = \App\Fields\File::loadFromRequest($_FILES['role_logo']);
			if ($fileInstance->validateAndSecure('image') && $fileInstance->getSize() < \App\Config::getMaxUploadSize()) {
				if (file_exists($targetFile)) {
					unlink($targetFile);
				}
				if ($fileInstance->moveFile($targetFile)) {
					$result = ['success' => true, 'message' => \App\Language::translate('LBL_UPLOAD_SUCCESS', $request->getModule(false))];
				}
			}
		}
		$response->setResult($result);
		$response->emit();
	}
}
