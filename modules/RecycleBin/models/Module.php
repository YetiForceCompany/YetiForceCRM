<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class RecycleBin_Module_Model extends Vtiger_Module_Model
{

	/**
	 * Function to get the url for list view of the module
	 * @return string - url
	 */
	public function getDefaultUrl()
	{
		return 'index.php?module=' . $this->get('name') . '&view=' . $this->getListViewName();
	}

	/**
	 * Function to get the list of listview links for the module
	 * @return <Array> - Associate array of Link Type to List of Vtiger_Link_Model instances
	 */
	public function getListViewLinks()
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$basicLinks = [];
		if ($currentUserModel->isAdminUser()) {
			$basicLinks = array(
				array(
					'linktype' => 'LISTVIEWBASIC',
					'linklabel' => 'LBL_EMPTY_RECYCLEBIN',
					'linkurl' => 'javascript:RecycleBin_List_Js.emptyRecycleBin("index.php?module=' . $this->get('name') . '&action=RecycleBinAjax")',
					'linkicon' => ''
				)
			);
		}

		foreach ($basicLinks as $basicLink) {
			$links['LISTVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicLink);
		}

		return $links;
	}

	/**
	 * Function to get the list of Mass actions for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associative array of Link type to List of  Vtiger_Link_Model instances for Mass Actions
	 */
	public function getListViewMassActions()
	{
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$massActionLinks = [];
		if ($currentUserModel->isAdminUser()) {
			$massActionLinks[] = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_DELETE',
				'linkurl' => 'javascript:RecycleBin_List_Js.deleteRecords("index.php?module=' . $this->get('name') . '&action=RecycleBinAjax")',
				'linkicon' => ''
			);
		}

		$massActionLinks[] = array(
			'linktype' => 'LISTVIEWMASSACTION',
			'linklabel' => 'LBL_RESTORE',
			'linkurl' => 'javascript:RecycleBin_List_Js.restoreRecords("index.php?module=' . $this->get('name') . '&action=RecycleBinAjax")',
			'linkicon' => ''
		);


		foreach ($massActionLinks as $massActionLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}

		return $links;
	}

	/**
	 * Function to get the Quick Links for the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Vtiger_Link_Model instances
	 */
	public function getSideBarLinks($linkParams)
	{
		$linkTypes = array('SIDEBARLINK', 'SIDEBARWIDGET');
		$links = Vtiger_Link_Model::getAllByType($this->getId(), $linkTypes, $linkParams);

		$quickLinks = array(
			array(
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_RECORDS_LIST',
				'linkurl' => $this->getDefaultUrl(),
				'linkicon' => '',
			),
		);
		foreach ($quickLinks as $quickLink) {
			$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
		}
		return $links;
	}

	/**
	 * Function to get all entity modules
	 * @return <array>
	 */
	public function getAllModuleList()
	{
		$moduleModels = parent::getEntityModules();
		$restrictedModules = array('ProjectMilestone', 'ModComments', 'Rss', 'Portal', 'Integration', 'PBXManager', 'Dashboard', 'Home');
		foreach ($moduleModels as $key => $moduleModel) {
			if (in_array($moduleModel->getName(), $restrictedModules) || $moduleModel->get('isentitytype') != 1) {
				unset($moduleModels[$key]);
			}
		}
		return $moduleModels;
	}

	/**
	 * Function to delete the reccords perminently in vitger CRM database
	 */
	public function emptyRecycleBin()
	{
		$recordIds = (new \App\Db\Query())->select('crmid')->from('vtiger_crmentity')->where(['deleted' => 1])->column();
		if ($recordIds) {
			$this->deletePerminently($recordIds);
			$this->deleteFiles($recordIds);
		}
		\App\Db::getInstance()->createCommand()
			->delete('vtiger_crmentity', ['deleted' => 1, 'crmid' => $recordIds])
			->execute();
		return true;
	}

	/**
	 * Function to deleted the records perminently in CRM
	 * @param type $reocrdIds
	 */
	public function deleteRecords($recordIds)
	{
		$this->deletePerminently($recordIds);
		//Delete the records in vtiger crmentity and relatedlists.
		\App\Db::getInstance()->createCommand()
			->delete('vtiger_crmentity', ['deleted' => 1, 'crmid' => $recordIds])
			->execute();
		// Delete entries of attachments from vtiger_attachments and vtiger_seattachmentsrel
		$this->deleteFiles($recordIds);
		\Vtiger_Files_Model::getRidOfTrash(['crmid' => $recordIds]);
	}
	/*	 * Function to delete files from CRM.
	 * @param type $recordIds
	 */

	public function deletePerminently($recordIds)
	{
		foreach ($recordIds as &$recordId) {
			$moduleName = App\Record::getType($recordId);
			$entity = CRMEntity::getInstance($moduleName);
			$entity->deletePerminently($moduleName, $recordId);
		}
	}

	/**
	 * Delete files
	 * @param int[] $recordIds
	 */
	public function deleteFiles($recordIds)
	{
		$db = \App\Db::getInstance();
		$attachmentsIds = (new \App\Db\Query())->select(['attachmentsid'])->from('vtiger_seattachmentsrel')->where(['crmid' => $recordIds])->column($db);
		if (!empty($attachmentsIds)) {
			$dataReader = (new \App\Db\Query())->select(['path', 'attachmentsid'])->from('vtiger_attachments')->where(['attachmentsid' => $attachmentsIds])->createCommand($db)->query();
			while ($row = $dataReader->read()) {
				$fileName = $row['path'] . $row['attachmentsid'];
				if (file_exists($fileName)) {
					chmod($fileName, 0750);
					unlink($fileName);
				}
			}
			$db->createCommand()->delete('vtiger_seattachmentsrel', ['crmid' => $recordIds])->execute();
			$db->createCommand()->delete('vtiger_attachments', ['attachmentsid' => $attachmentsIds])->execute();
			$db->createCommand()->delete('vtiger_crmentity', ['crmid' => $attachmentsIds])->execute();
		}
	}

	/**
	 * Function to restore the deleted records.
	 * @param string $sourceModule
	 * @param int[] $recordIds
	 */
	public function restore($sourceModule, $recordIds)
	{
		$focus = CRMEntity::getInstance($sourceModule);
		foreach (array_filter($recordIds) as $id) {
			$focus->restore($sourceModule, $id);
		}
	}

	public function getDeletedRecordsTotalCount()
	{
		$db = PearDatabase::getInstance();
		$totalCount = $db->pquery('select count(*) as count from vtiger_crmentity where deleted=1', []);
		return $db->query_result($totalCount, 0, 'count');
	}
}
