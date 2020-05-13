<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Documents_DetailView_Model extends Vtiger_DetailView_Model
{
	/**
	 * Function to get the detail view links (links and widgets).
	 *
	 * @param <array> $linkParams - parameters which will be used to calicaulate the params
	 *
	 * @return <array> - array of link models in the format as below
	 *                 array('linktype'=>list of link models);
	 */
	public function getDetailViewLinks($linkParams)
	{
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$linkModelList = parent::getDetailViewLinks($linkParams);
		$recordModel = $this->getRecord();

		if ($recordModel->get('filestatus') && $recordModel->get('filename') && 'I' === $recordModel->get('filelocationtype')) {
			$basicActionLink = [
				'linktype' => 'DETAIL_VIEW_BASIC',
				'linklabel' => 'LBL_DOWNLOAD_FILE',
				'linkurl' => $recordModel->getDownloadFileURL(),
				'linkicon' => 'fas fa-download',
				'linkclass' => 'btn-outline-dark btn-sm',
			];
			$linkModelList['DETAIL_VIEW_BASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}
		$basicActionLink = [
			'linktype' => 'DETAIL_VIEW_BASIC',
			'linklabel' => 'LBL_CHECK_FILE_INTEGRITY',
			'linkurl' => $recordModel->checkFileIntegrityURL(),
			'linkicon' => 'fas fa-check',
			'linkclass' => 'btn-outline-dark btn-sm',
		];
		$linkModelList['DETAIL_VIEW_BASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);

		if ($recordModel->get('filestatus') && $recordModel->get('filename') && 'I' === $recordModel->get('filelocationtype') && $currentUserModel->hasModulePermission('OSSMail') && App\Config::main('isActiveSendingMails')) {
			$basicActionLink = [
				'linktype' => 'DETAIL_VIEW_BASIC',
				'linklabel' => \App\Language::translate('LBL_EMAIL_FILE_AS_ATTACHMENT', 'Documents'),
				'linkhref' => true,
				'linktarget' => '_blank',
				'linkurl' => 'index.php?module=OSSMail&view=Compose&type=new&crmModule=Documents&crmRecord=' . $recordModel->getId(),
				'linkicon' => 'fas fa-envelope',
				'linkclass' => 'btn-outline-dark btn-sm',
			];
			$linkModelList['DETAIL_VIEW_BASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}
		return $linkModelList;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDetailViewRelatedLinks()
	{
		$recordModel = $this->getRecord();
		$relatedLinks = parent::getDetailViewRelatedLinks();
		$relatedLinks[] = [
			'linktype' => 'DETAILVIEWTAB',
			'linklabel' => \App\Language::translate('LBL_RELATIONS', $recordModel->getModuleName()),
			'linkKey' => 'LBL_RECORD_SUMMARY',
			'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showDocumentRelations',
			'linkicon' => '',
			'related' => \App\Json::encode(Documents_Record_Model::getReferenceModuleByDocId($recordModel->getId())),
			'countRelated' => App\Config::relation('SHOW_RECORDS_COUNT'),
		];
		return $relatedLinks;
	}
}
