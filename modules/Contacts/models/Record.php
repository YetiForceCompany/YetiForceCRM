<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/**
 * Class contacts record model.
 */
class Contacts_Record_Model extends Vtiger_Record_Model
{
	/**
	 * Function to get List of Fields which are related from Contacts to Inventory Record.
	 *
	 * @return array
	 */
	public function getInventoryMappingFields()
	{
		return [
			['parentField' => 'parent_id', 'inventoryField' => 'account_id', 'defaultValue' => ''],
			['parentField' => 'buildingnumbera', 'inventoryField' => 'buildingnumbera', 'defaultValue' => ''],
			['parentField' => 'localnumbera', 'inventoryField' => 'localnumbera', 'defaultValue' => ''],
			['parentField' => 'addresslevel1a', 'inventoryField' => 'addresslevel1a', 'defaultValue' => ''],
			['parentField' => 'addresslevel2a', 'inventoryField' => 'addresslevel2a', 'defaultValue' => ''],
			['parentField' => 'addresslevel3a', 'inventoryField' => 'addresslevel3a', 'defaultValue' => ''],
			['parentField' => 'addresslevel4a', 'inventoryField' => 'addresslevel4a', 'defaultValue' => ''],
			['parentField' => 'addresslevel5a', 'inventoryField' => 'addresslevel5a', 'defaultValue' => ''],
			['parentField' => 'addresslevel6a', 'inventoryField' => 'addresslevel6a', 'defaultValue' => ''],
			['parentField' => 'addresslevel7a', 'inventoryField' => 'addresslevel7a', 'defaultValue' => ''],
			['parentField' => 'addresslevel8a', 'inventoryField' => 'addresslevel8a', 'defaultValue' => ''],
			['parentField' => 'buildingnumberb', 'inventoryField' => 'buildingnumberb', 'defaultValue' => ''],
			['parentField' => 'localnumberb', 'inventoryField' => 'localnumberb', 'defaultValue' => ''],
			['parentField' => 'addresslevel1b', 'inventoryField' => 'addresslevel1b', 'defaultValue' => ''],
			['parentField' => 'addresslevel2b', 'inventoryField' => 'addresslevel2b', 'defaultValue' => ''],
			['parentField' => 'addresslevel3b', 'inventoryField' => 'addresslevel3b', 'defaultValue' => ''],
			['parentField' => 'addresslevel4b', 'inventoryField' => 'addresslevel4b', 'defaultValue' => ''],
			['parentField' => 'addresslevel5b', 'inventoryField' => 'addresslevel5b', 'defaultValue' => ''],
			['parentField' => 'addresslevel6b', 'inventoryField' => 'addresslevel6b', 'defaultValue' => ''],
			['parentField' => 'addresslevel7b', 'inventoryField' => 'addresslevel7b', 'defaultValue' => ''],
			['parentField' => 'addresslevel8b', 'inventoryField' => 'addresslevel8b', 'defaultValue' => ''],
		];
	}

	/**
	 * The function decide about mandatory save record.
	 *
	 * @return bool
	 */
	public function isMandatorySave()
	{
		return $_FILES ? true : false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete()
	{
		parent::delete();
		\App\Db::getInstance()->createCommand()->update('vtiger_customerdetails', [
			'portal' => 0,
			'support_start_date' => null,
			'support_end_date' => null,
			], ['customerid' => $this->getId()])->execute();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRecordRelatedListViewLinksLeftSide(Vtiger_RelationListView_Model $viewModel)
	{
		$links = parent::getRecordRelatedListViewLinksLeftSide($viewModel);
		if (AppConfig::main('isActiveSendingMails') && \App\Privilege::isPermitted('OSSMail')) {
			if (Users_Record_Model::getCurrentUserModel()->get('internal_mailer') == 1) {
				$links['LBL_SEND_EMAIL'] = Vtiger_Link_Model::getInstanceFromValues([
						'linklabel' => 'LBL_SEND_EMAIL',
						'linkhref' => true,
						'linkurl' => OSSMail_Module_Model::getComposeUrl($this->getModuleName(), $this->getId(), 'Detail', 'new'),
						'linkicon' => 'fas fa-envelope',
						'linkclass' => 'btn-xs btn-default',
						'linktarget' => '_blank',
				]);
			} else {
				$urldata = OSSMail_Module_Model::getExternalUrl($this->getModuleName(), $this->getId(), 'Detail', 'new');
				if ($urldata && $urldata !== 'mailto:?') {
					$links[] = Vtiger_Link_Model::getInstanceFromValues([
							'linklabel' => 'LBL_CREATEMAIL',
							'linkhref' => true,
							'linkurl' => $urldata,
							'linkicon' => 'fas fa-envelope',
							'linkclass' => 'btn-xs btn-default',
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
				preg_match("/<a(.*)>(.*)<\/a>/i", $data[0], $name);

				$recordModel = Vtiger_Record_Model::getCleanInstance($this->getModuleName());
				$recordModel->setId($competitionId);
				$hierarchy['entries'][$competitionId][0] = $dashes[0] . '<a href=' . $recordModel->getDetailViewUrl() . '>' . $name[2] .
					'</a>';
			}
		}
		return $hierarchy;
	}
}
