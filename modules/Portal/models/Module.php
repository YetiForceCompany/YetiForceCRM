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

		$data['bookmarkName'] = $db->query_result($result, 0, 'portalname');
		$data['bookmarkUrl'] = $db->query_result($result, 0, 'portalurl');

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

		return $db->query_result($result, 0, 'portalurl');
	}

	public function getAllRecords()
	{
		$db = PearDatabase::getInstance();
		$record = array();

		$result = $db->pquery('SELECT portalid, portalname FROM vtiger_portal', array());

		while ($row = $db->fetchByAssoc($result)) {
			$record[] = [
				'id' => $row['portalid'],
				'portalname' => $row['portalname']
			];
		}

		return $record;
	}

	public function deleteRecords(Vtiger_Request $request)
	{
		$searchValue = $request->get('search_value');
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');

		$db = PearDatabase::getInstance();

		$query = 'DELETE FROM vtiger_portal';
		$params = array();

		if (!empty($selectedIds) && $selectedIds != 'all' && count($selectedIds) > 0) {
			$query .= sprintf(' WHERE portalid IN (%s)', generateQuestionMarks($selectedIds));
			$params = $selectedIds;
		} else if ($selectedIds == 'all') {
			if (empty($searchValue) && count($excludedIds) > 0) {
				$query .= sprintf(' WHERE portalid NOT IN ()', generateQuestionMarks($excludedIds));
				$params = $excludedIds;
			} else if (!empty($searchValue) && count($excludedIds) < 1) {
				$query .= sprintf(" WHERE portalname LIKE '%s'", "%$searchValue%");
			} else if (!empty($searchValue) && count($excludedIds) > 0) {
				$query .= sprintf(" WHERE portalname LIKE '%s' && portalid NOT IN (%s)", "%$searchValue%", generateQuestionMarks($excludedIds));
				$params = $excludedIds;
			}
		}
		$db->pquery($query, $params);
	}
}
