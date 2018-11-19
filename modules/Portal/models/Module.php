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
	/**
	 * {@inheritdoc}
	 */
	public function getSideBarLinks($linkParams)
	{
		$links = [];
		$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_OUR_SITES_LIST',
				'linkurl' => $this->getListViewUrl(),
				'linkicon' => '',
		]);

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
				'createdtime' => date('Y-m-d H:i:s'),
			])->execute();
		} else {
			$db->createCommand()->update('vtiger_portal', [
				'portalname' => $bookmarkName,
				'portalurl' => $bookmarkUrl,
				], ['portalid' => $recordId])->execute();
		}
		return true;
	}

	/**
	 * Function to get infomation about bookmark.
	 *
	 * @param int $recordId
	 *
	 * @return array
	 */
	public static function getRecord($recordId)
	{
		return (new App\Db\Query())->select(['bookmarkName' => 'portalname', 'bookmarkUrl' => 'portalurl'])
			->from('vtiger_portal')
			->where(['portalid' => $recordId])
			->one();
	}

	/**
	 * Delete record.
	 *
	 * @param $recordId
	 *
	 * @throws \yii\db\Exception
	 */
	public function deleteRecord($recordId)
	{
		\App\Db::getInstance()->createCommand()->delete('vtiger_portal', ['portalid' => $recordId])->execute();
	}

	/**
	 * Get website url.
	 *
	 * @param $recordId
	 *
	 * @return false|null|string
	 */
	public static function getWebsiteUrl($recordId)
	{
		return (new \App\Db\Query())->select(['portalurl'])->from(['vtiger_portal'])
			->where(['portalid' => $recordId])
			->scalar();
	}

	/**
	 * Get all records.
	 *
	 * @return array
	 */
	public static function getAllRecords()
	{
		return (new \App\Db\Query())->select(['id' => 'portalid', 'portalname'])->from(['vtiger_portal'])->all();
	}

	/**
	 * Delete records.
	 *
	 * @param \App\Request $request
	 */
	public static function deleteRecords(\App\Request $request)
	{
		$searchValue = $request->getForSql('search_value');
		$selectedIds = $request->getArray('selected_ids', 2);
		$excludedIds = $request->getArray('excluded_ids', 2);
		$params = [];
		if (!empty($selectedIds) && $selectedIds[0] != 'all' && count($selectedIds) > 0) {
			$params = ['portalid' => $selectedIds];
		} elseif ($selectedIds[0] == 'all') {
			if (empty($searchValue) && count($excludedIds) > 0) {
				$params = ['not in', 'portalid', $excludedIds];
			} elseif (!empty($searchValue) && count($excludedIds) < 1) {
				$params = ['like', 'portalname', $searchValue];
			} elseif (!empty($searchValue) && count($excludedIds) > 0) {
				$params = ['and'];
				$params[] = ['like', 'portalname', $searchValue];
				$params[] = ['not in', 'portalid', $excludedIds];
			}
		}
		App\Db::getInstance()->createCommand()->delete('vtiger_portal', $params)->execute();
	}
}
