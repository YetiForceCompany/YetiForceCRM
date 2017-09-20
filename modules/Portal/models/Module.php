<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Portal_Module_Model extends Vtiger_Module_Model
{

	public function getSideBarLinks($linkParams)
	{
		$quickLink = array(
			'linktype' => 'SIDEBARLINK',
			'linklabel' => 'LBL_OUR_SITES_LIST',
			'linkurl' => $this->getListViewUrl(),
			'linkicon' => '',
		);
		$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
		return $links;
	}

	public static function savePortalRecord($recordId, $bookmarkName, $bookmarkUrl)
	{
		$db = App\Db::getInstance();
		if (empty($recordId)) {
			$db->createCommand()->insert('vtiger_portal', [
				'portalname' => $bookmarkName,
				'portalurl' => $bookmarkUrl,
				'sequence' => 0,
				'setdefault' => 0,
				'createdtime' => date('Y-m-d H:i:s')
			])->execute();
		} else {
			$db->createCommand()->update('vtiger_portal', [
				'portalname' => $bookmarkName,
				'portalurl' => $bookmarkUrl,
				], ['portalid' => $recordId])->execute();
		}
		return true;
	}

	public function getRecord($recordId)
	{
		$db = PearDatabase::getInstance();

		$result = $db->pquery('SELECT portalname, portalurl FROM vtiger_portal WHERE portalid = ?', array($recordId));

		$data['bookmarkName'] = $db->queryResult($result, 0, 'portalname');
		$data['bookmarkUrl'] = $db->queryResult($result, 0, 'portalurl');

		return $data;
	}

	public function deleteRecord($recordId)
	{
		\App\Db::getInstance()->createCommand()->delete('vtiger_portal', ['portalid' => $recordId])->execute();
	}

	public function getWebsiteUrl($recordId)
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT portalurl FROM vtiger_portal WHERE portalid=?', array($recordId));

		return $db->queryResult($result, 0, 'portalurl');
	}

	public function getAllRecords()
	{
		$db = PearDatabase::getInstance();
		$record = [];

		$result = $db->pquery('SELECT portalid, portalname FROM vtiger_portal', []);

		while ($row = $db->fetchByAssoc($result)) {
			$record[] = [
				'id' => $row['portalid'],
				'portalname' => $row['portalname']
			];
		}

		return $record;
	}

	/**
	 * Delete records
	 * @param \App\Request $request
	 */
	public function deleteRecords(\App\Request $request)
	{
		$searchValue = $request->getForSql('search_value');
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');
		$params = [];
		if (!empty($selectedIds) && $selectedIds != 'all' && count($selectedIds) > 0) {
			$params = ['portalid' => $selectedIds];
		} else if ($selectedIds == 'all') {
			if (empty($searchValue) && count($excludedIds) > 0) {
				$params = ['not in', 'portalid', $excludedIds];
			} else if (!empty($searchValue) && count($excludedIds) < 1) {
				$params = ['like', 'portalname', $searchValue];
			} else if (!empty($searchValue) && count($excludedIds) > 0) {
				$params = ['and'];
				$params [] = ['like', 'portalname', $searchValue];
				$params [] = ['not in', 'portalid', $excludedIds];
			}
		}
		App\Db::getInstance()->createCommand()->delete('vtiger_portal', $params)->execute();
	}
}
