<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_Vtiger_CompanyDetailsSave_Action extends Settings_Vtiger_Basic_Action
{

	public function process(Vtiger_Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$moduleModel = Settings_Vtiger_CompanyDetails_Model::getInstance();
		$status = false;
		$images = ['logo', 'panellogo'];
		if ($request->get('organizationname')) {
			foreach ($images as $image) {
				$saveLogo[$image] = $status = true;
				if (!empty($_FILES[$image]['name'])) {
					$logoDetails[$image] = $_FILES[$image];
					$fileInstance = \includes\fields\File::loadFromRequest($logoDetails[$image]);
					if (!$fileInstance->validate()) {
						$saveLogo[$image] = false;
					}
					//mime type check
					if ($fileInstance->getShortMimeType(0) != 'image' || !in_array($fileInstance->getShortMimeType(1), Settings_Vtiger_CompanyDetails_Model::$logoSupportedFormats)) {
						$saveLogo[$image] = false;
					}
					if ($saveLogo[$image]) {
						$moduleModel->saveLogo($image);
					}
				} else {
					$saveLogo[$image] = true;
				}
			}
			$fields = $moduleModel->getFields();
			foreach ($fields as $fieldName => $fieldType) {
				$fieldValue = $request->get($fieldName);
				if ($fieldName === 'logoname') {
					if (!empty($logoDetails['logo']['name'])) {
						$fieldValue = ltrim(basename(" " . $logoDetails['logo']['name']));
					} else {
						$fieldValue = $moduleModel->get($fieldName);
					}
				}
				if ($fieldName === 'panellogoname') {
					if (!empty($logoDetails['panellogo']['name'])) {
						$fieldValue = ltrim(basename(" " . $logoDetails['panellogo']['name']));
					} else {
						$fieldValue = $moduleModel->get($fieldName);
					}
				}
				$moduleModel->set($fieldName, $fieldValue);
			}
			$moduleModel->save();
		}

		$reloadUrl = $moduleModel->getIndexViewUrl();
		if ($saveLogo['panellogo'] && $saveLogo['logo'] && $status) {
			
		} else if (!($saveLogo['panellogo'] && $saveLogo['logo'])) {
			$reloadUrl .= '&error=LBL_INVALID_IMAGE';
		} else {
			$reloadUrl = $moduleModel->getEditViewUrl() . '&error=LBL_FIELDS_INFO_IS_EMPTY';
		}
		header('Location: ' . $reloadUrl);
	}

	public function validateRequest(Vtiger_Request $request)
	{
		$request->validateWriteAccess();
	}
}
