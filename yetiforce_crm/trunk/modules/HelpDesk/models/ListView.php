<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class HelpDesk_ListView_Model extends Vtiger_ListView_Model {
	/*
	 * Function to get Setting links
	 * @return array of setting links
	 */

	public function getSettingLinks() {
		$settingsLinks = parent::getSettingLinks();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$moduleModel = $this->getModule();
		if ($currentUserModel->isAdminUser()) {
			$settingsLinks[] = array(
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_EDIT_MAILSCANNER',
				'linkurl' => 'index.php?module=MailScanner&parent=Settings&view=List',
				'linkicon' => ''
			);
			return $settingsLinks;
		}
	}

}