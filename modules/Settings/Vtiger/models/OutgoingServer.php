<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_Vtiger_OutgoingServer_Model extends Settings_Vtiger_Systems_Model
{

	private $defaultLoaded = false;

	public function loadDefaultValues()
	{
		$defaultOutgoingServerDetails = VtigerConfig::getOD('DEFAULT_OUTGOING_SERVER_DETAILS');
		foreach ($defaultOutgoingServerDetails as $key => $value) {
			$this->set($key, $value);
		}
		$this->defaultLoaded = true;
	}

	/**
	 * Function to get CompanyDetails Menu item
	 * @return menu item Model
	 */
	public function getMenuItem()
	{
		$menuItem = Settings_Vtiger_MenuItem_Model::getInstance('LBL_MAIL_SERVER_SETTINGS');
		return $menuItem;
	}

	public function getEditViewUrl()
	{
		$menuItem = $this->getMenuItem();
		return '?module=Vtiger&parent=Settings&view=OutgoingServerEdit&block=' . $menuItem->get('blockid') . '&fieldid=' . $menuItem->get('fieldid');
	}

	public function getDetailViewUrl()
	{
		$menuItem = $this->getMenuItem();
		return '?module=Vtiger&parent=Settings&view=OutgoingServerDetail&block=' . $menuItem->get('blockid') . '&fieldid=' . $menuItem->get('fieldid');
	}

	public function isDefaultSettingLoaded()
	{
		return $this->defaultLoaded;
	}

	public function save($request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$toEmail = $currentUser->get('email1');

		if (!empty($toEmail)) {
			$data = [
				'sysname' => 'TestMailAboutTheMailServerConfiguration',
				'to_email' => $toEmail,
				'module' => 'Users',
				'record' => $currentUser->getId(),
			];
			$recordModel = Vtiger_Record_Model::getCleanInstance('OSSMailTemplates');
			$mailStatus = $recordModel->sendMailFromTemplate($data);
		}
		if ($mailStatus != 1 && !$this->isDefaultSettingLoaded()) {
			throw new Exception('Error occurred while sending mail');
		}
		return parent::save();
	}
}
