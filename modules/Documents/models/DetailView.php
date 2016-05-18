<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Documents_DetailView_Model extends Vtiger_DetailView_Model
{

	/**
	 * Function to get the detail view links (links and widgets)
	 * @param <array> $linkParams - parameters which will be used to calicaulate the params
	 * @return <array> - array of link models in the format as below
	 *                   array('linktype'=>list of link models);
	 */
	public function getDetailViewLinks($linkParams)
	{
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$linkModelList = parent::getDetailViewLinks($linkParams);
		$recordModel = $this->getRecord();

		if ($recordModel->get('filestatus') && $recordModel->get('filename') && $recordModel->get('filelocationtype') === 'I') {
			$basicActionLink = array(
				'linktype' => 'DETAILVIEW',
				'linklabel' => 'LBL_DOWNLOAD_FILE',
				'linkurl' => $recordModel->getDownloadFileURL(),
				'linkicon' => 'glyphicon glyphicon-download-alt'
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}
		$basicActionLink = array(
			'linktype' => 'DETAILVIEW',
			'linklabel' => 'LBL_CHECK_FILE_INTEGRITY',
			'linkurl' => $recordModel->checkFileIntegrityURL(),
			'linkicon' => ' glyphicon glyphicon-file'
		);
		$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);

		if ($recordModel->get('filestatus') && $recordModel->get('filename') && $recordModel->get('filelocationtype') === 'I') {
			$emailModuleModel = Vtiger_Module_Model::getInstance('OSSMail');
			if ($currentUserModel->hasModulePermission($emailModuleModel->getId()) && AppConfig::main('isActiveSendingMails')) {
				$basicActionLink = array(
					'linktype' => 'DETAILVIEW',
					'linklabel' => vtranslate('LBL_EMAIL_FILE_AS_ATTACHMENT', 'Documents'),
					'linkhref' => true,
					'linktarget' => '_blank',
					'linkurl' => 'index.php?module=OSSMail&view=compose&type=new&crmModule=Documents&crmRecord=' . $recordModel->getId(),
					'linkicon' => 'glyphicon glyphicon-envelope'
				);
				$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
			}
		}

		return $linkModelList;
	}
}
