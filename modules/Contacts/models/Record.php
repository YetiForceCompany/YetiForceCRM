<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

/**
 * Class contacts record model.
 */
class Contacts_Record_Model extends Vtiger_Record_Model
{
	/** {@inheritdoc} */
	public function getRecordRelatedListViewLinksLeftSide(Vtiger_RelationListView_Model $viewModel)
	{
		$links = parent::getRecordRelatedListViewLinksLeftSide($viewModel);
		if (\App\Mail::checkMailClient()) {
			if (\App\Mail::checkInternalMailClient()) {
				$links['LBL_SEND_EMAIL'] = Vtiger_Link_Model::getInstanceFromValues([
					'linklabel' => 'LBL_SEND_EMAIL',
					'linkhref' => true,
					'linkurl' => OSSMail_Module_Model::getComposeUrl($this->getModuleName(), $this->getId(), 'Detail', 'new'),
					'linkicon' => 'fas fa-envelope',
					'linkclass' => 'btn-sm btn-default',
					'linktarget' => '_blank',
				]);
			} else {
				$urldata = OSSMail_Module_Model::getExternalUrl($this->getModuleName(), $this->getId(), 'Detail', 'new');
				if ($urldata && 'mailto:?' !== $urldata) {
					$links[] = Vtiger_Link_Model::getInstanceFromValues([
						'linklabel' => 'LBL_CREATEMAIL',
						'linkhref' => true,
						'linkurl' => $urldata,
						'linkicon' => 'fas fa-envelope',
						'linkclass' => 'btn-sm btn-default',
						'relatedModuleName' => 'OSSMailView',
					]);
				}
			}
		}
		return $links;
	}

	/**
	 * Function returns the details of IStorages Hierarchy.
	 *
	 * @return array
	 */
	public function getHierarchy()
	{
		$focus = CRMEntity::getInstance($this->getModuleName());
		$hierarchy = $focus->getHierarchy($this->getId());
		foreach ($hierarchy['entries'] as $competitionId => $data) {
			preg_match('/<a href="+/', $data[0], $matches);
			if (!empty($matches)) {
				preg_match('/[.\s]+/', $data[0], $dashes);
				preg_match('/<a(.*)>(.*)<\\/a>/i', $data[0], $name);

				$recordModel = Vtiger_Record_Model::getCleanInstance($this->getModuleName());
				$recordModel->setId($competitionId);
				$hierarchy['entries'][$competitionId][0] = $dashes[0] . '<a href=' . $recordModel->getDetailViewUrl() . '>' . $name[2] .
					'</a>';
			}
		}
		return $hierarchy;
	}
}
