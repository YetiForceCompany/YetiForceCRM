<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_Vtiger_UpdateCompanyLogo_Action extends Settings_Vtiger_Basic_Action
{

	public function process(Vtiger_Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$moduleModel = Settings_Vtiger_CompanyDetails_Model::getInstance();

		$saveLogo = $securityError = false;
		$logoDetails = $_FILES['logo'];
		$fileType = explode('/', $logoDetails['type']);
		$fileType = $fileType[1];

		$fileInstance = \includes\fields\File::loadFromRequest($logoDetails);
		if ($fileInstance->validate('image') && in_array($fileType, Settings_Vtiger_CompanyDetails_Model::$logoSupportedFormats)) {
			$saveLogo = true;
		}
		if ($saveLogo) {
			$moduleModel->saveLogo();
			$moduleModel->set('logoname', ltrim(basename(' ' . \includes\fields\File::sanitizeUploadFileName($logoDetails['name']))));
			$moduleModel->save();
		}

		$reloadUrl = $moduleModel->getIndexViewUrl();
		if ($securityError) {
			$reloadUrl .= '&error=LBL_IMAGE_CORRUPTED';
		} else if (!$saveLogo) {
			$reloadUrl .= '&error=LBL_INVALID_IMAGE';
		}
		header('Location: ' . $reloadUrl);
	}

	public function validateRequest(Vtiger_Request $request)
	{
		$request->validateWriteAccess();
	}
}
