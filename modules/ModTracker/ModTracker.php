<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */
include_once 'include/Webservices/GetUpdates.php';

class ModTracker
{

	/**
	 * Constant variables which indicates the status of the changed record.
	 */
	public static $UPDATED = '0';
	public static $DELETED = '1';
	public static $CREATED = '2';
	public static $RESTORED = '3';
	public static $LINK = '4';
	public static $UNLINK = '5';
	public static $CONVERTTOACCOUNT = '6';
	public static $DISPLAYED = '7';

	static function getAllActionsTypes()
	{
		return [
			self::$UPDATED => 'LBL_AT_UPDATE',
			self::$DELETED => 'LBL_AT_DELETE',
			self::$CREATED => 'LBL_AT_CREATE',
			self::$RESTORED => 'LBL_AT_RESTORE',
			self::$LINK => 'LBL_AT_LINK',
			self::$UNLINK => 'LBL_AT_UNLINK',
			self::$CONVERTTOACCOUNT => 'LBL_AT_CONVERTTOACCOUNT',
			self::$DISPLAYED => 'LBL_AT_DISPLAY'
		];
	}

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type
	 */
	public function vtlib_handler($moduleName, $eventType)
	{
		global $currentModule;
		$adb = PearDatabase::getInstance();
		$modtrackerModule = vtlib\Module::getInstance($currentModule);
		$otherModuleNames = $this->getModTrackerEnabledModules();

		if ($eventType == 'module.postinstall') {
			$adb->pquery('UPDATE vtiger_tab SET customized=0 WHERE name=?', array($moduleName));

			$fieldid = $adb->getUniqueID('vtiger_settings_field');
			$blockid = \vtlib\Deprecated::getSettingsBlockId('LBL_OTHER_SETTINGS');
			$seq_res = $adb->pquery("SELECT max(sequence) AS max_seq FROM vtiger_settings_field WHERE blockid = ?", array($blockid));
			if ($adb->num_rows($seq_res) > 0) {
				$cur_seq = $adb->query_result($seq_res, 0, 'max_seq');
				if ($cur_seq != null)
					$seq = $cur_seq + 1;
			}

			$adb->pquery('INSERT INTO vtiger_settings_field(fieldid, blockid, name, iconpath, description, linkto, sequence)
				VALUES (?,?,?,?,?,?,?)', array($fieldid, $blockid, 'ModTracker', 'set-IcoLoginHistory.gif', 'LBL_MODTRACKER_DESCRIPTION',
				'index.php?module=ModTracker&action=BasicSettings&parenttab=Settings&formodule=ModTracker', $seq));
		} else if ($eventType == 'module.disabled') {

			$em = new VTEventsManager($adb);
			$em->setHandlerInActive('ModTrackerHandler');
		} else if ($eventType == 'module.enabled') {
			$em = new VTEventsManager($adb);
			$em->setHandlerActive('ModTrackerHandler');
		} else if ($eventType == 'module.preuninstall') {

		} else if ($eventType == 'module.preupdate') {

		} else if ($eventType == 'module.postupdate') {

		}
	}

	/**
	 * function gives an array of module names for which modtracking is enabled
	 */
	public function getModTrackerEnabledModules()
	{
		$adb = PearDatabase::getInstance();
		$moduleResult = $adb->pquery('SELECT * FROM vtiger_modtracker_tabs', array());
		for ($i = 0; $i < $adb->num_rows($moduleResult); $i++) {
			$tabId = $adb->query_result($moduleResult, $i, 'tabid');
			$visible = $adb->query_result($moduleResult, $i, 'visible');
			self::updateCache($tabId, $visible);
			if ($visible == 1) {
				$modules[] = getTabModuleName($tabId);
			}
		}
		return $modules;
	}

	// cache variable
	static $__cache_modtracker = array();

	/**
	 * Invoked to disable tracking for the module.
	 * @param Integer $tabid
	 */
	static function disableTrackingForModule($tabid)
	{
		$adb = PearDatabase::getInstance();
		if (!self::isModulePresent($tabid)) {
			$res = $adb->pquery("INSERT INTO vtiger_modtracker_tabs VALUES(?,?)", array($tabid, 0));
			self::updateCache($tabid, 0);
		} else {
			$updatevisibility = $adb->pquery("UPDATE vtiger_modtracker_tabs SET visible = 0 WHERE tabid = ?", array($tabid));
			self::updateCache($tabid, 0);
		}
		if (self::isModtrackerLinkPresent($tabid)) {
			$moduleInstance = vtlib\Module::getInstance($tabid);
			$moduleInstance->deleteLink('DETAILVIEWBASIC', 'View History');
		}
		$adb->pquery("UPDATE vtiger_field SET presence = 1 WHERE tabid = ? && fieldname = ?", array($tabid, 'was_read'));
	}

	/**
	 * Invoked to enable tracking for the module.
	 * @param Integer $tabid
	 */
	static function enableTrackingForModule($tabid)
	{
		$adb = PearDatabase::getInstance();
		if (!self::isModulePresent($tabid)) {
			$res = $adb->pquery("INSERT INTO vtiger_modtracker_tabs VALUES(?,?)", array($tabid, 1));
			self::updateCache($tabid, 1);
		} else {
			$updatevisibility = $adb->pquery("UPDATE vtiger_modtracker_tabs SET visible = 1 WHERE tabid = ?", array($tabid));
			self::updateCache($tabid, 1);
		}
		$adb->pquery("UPDATE vtiger_field SET presence = 2 WHERE tabid = ? && fieldname = ?", array($tabid, 'was_read'));
	}

	/**
	 * Invoked to check if tracking is enabled or disabled for the module.
	 * @param String $moduleName
	 */
	static function isTrackingEnabledForModule($moduleName)
	{
		$adb = PearDatabase::getInstance();
		$tracking = Vtiger_Cache::get('isTrackingEnabledForModule', $moduleName);
		if ($tracking !== false) {
			return $tracking ? true : false;
		}
		$tabid = \includes\Modules::getModuleId($moduleName);
		if (!self::getVisibilityForModule($tabid) || self::getVisibilityForModule($tabid) !== 0) {
			$query = $adb->pquery('SELECT 1 FROM vtiger_modtracker_tabs WHERE vtiger_modtracker_tabs.visible = 1 && vtiger_modtracker_tabs.tabid=?', array($tabid));

			if ($adb->num_rows($query) < 1) {
				self::updateCache($tabid, 0);
				Vtiger_Cache::set('isTrackingEnabledForModule', $moduleName, 0);
				return false;
			} else {
				self::updateCache($tabid, 1);
				Vtiger_Cache::set('isTrackingEnabledForModule', $moduleName, 1);
				return true;
			}
		} else if (self::getVisibilityForModule($tabid) === 0) {
			Vtiger_Cache::set('isTrackingEnabledForModule', $moduleName, 0);
			return false;
		} else {
			Vtiger_Cache::set('isTrackingEnabledForModule', $moduleName, 1);
			return true;
		}
	}

	/**
	 * Invoked to check if the module is present in the table or not.
	 * @param Integer $tabid
	 */
	static function isModulePresent($tabid)
	{
		$adb = PearDatabase::getInstance();
		if (!self::checkModuleInModTrackerCache($tabid)) {
			$query = $adb->pquery("SELECT * FROM vtiger_modtracker_tabs WHERE tabid = ?", array($tabid));
			$rows = $adb->num_rows($query);
			if ($rows) {
				$tabid = $adb->query_result($query, 0, 'tabid');
				$visible = $adb->query_result($query, 0, 'visible');
				self::updateCache($tabid, $visible);
				return true;
			} else
				return false;
		} else
			return true;
	}

	/**
	 * Invoked to check if ModTracker links are enabled for the module.
	 * @param Integer $tabid
	 */
	static function isModtrackerLinkPresent($tabid)
	{
		$adb = PearDatabase::getInstance();
		$query1 = $adb->pquery("SELECT * FROM vtiger_links WHERE linktype='DETAILVIEWBASIC' AND
							  linklabel = 'View History' && tabid = ?", array($tabid));
		$row = $adb->num_rows($query1);
		if ($row >= 1)
			return true;
		else
			return false;
	}

	/**
	 * Invoked to update cache.
	 * @param Integer $tabid
	 * @param Boolean $visible
	 */
	static function updateCache($tabid, $visible)
	{
		self::$__cache_modtracker[$tabid] = array(
			'tabid' => $tabid,
			'visible' => $visible
		);
	}

	/**
	 * Invoked to check the ModTracker cache.
	 * @param Integer $tabid
	 */
	static function checkModuleInModTrackerCache($tabid)
	{
		if (isset(self::$__cache_modtracker[$tabid])) {
			return true;
		} else
			return false;
	}

	/**
	 * Invoked to fetch the visibility for the module from the cache.
	 * @param Integer $tabid
	 */
	static function getVisibilityForModule($tabid)
	{
		if (isset(self::$__cache_modtracker[$tabid])) {
			return self::$__cache_modtracker[$tabid]['visible'];
		}
		return false;
	}

	/**
	 * Get the list of changed record after $mtime
	 * @param <type> $mtime
	 * @param <type> $user
	 * @param <type> $limit 
	 */
	public function getChangedRecords($uniqueId, $mtime, $limit = 100)
	{
		$current_user = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$adb = PearDatabase::getInstance();
		$datetime = date('Y-m-d H:i:s', $mtime);

		$accessibleModules = $this->getModTrackerEnabledModules();

		if (empty($accessibleModules))
			throw new Exception('Modtracker not enabled for any modules');

		$query = sprintf('SELECT id, module, modifiedtime, vtiger_crmentity.crmid, smownerid, vtiger_modtracker_basic.status
                FROM vtiger_modtracker_basic
                INNER JOIN vtiger_crmentity ON vtiger_modtracker_basic.crmid = vtiger_crmentity.crmid
                    && vtiger_modtracker_basic.changedon = vtiger_crmentity.modifiedtime
                WHERE id > ? && changedon >= ? && module IN (%s)
                ORDER BY id', generateQuestionMarks($accessibleModules));

		$params = array($uniqueId, $datetime);
		foreach ($accessibleModules as $entityModule) {
			$params[] = $entityModule;
		}

		if ($limit != false)
			$query .=" LIMIT $limit";

		$result = $adb->pquery($query, $params);

		$modTime = array();
		$rows = $adb->num_rows($result);

		for ($i = 0; $i < $rows; $i++) {
			$status = $adb->query_result($result, $i, 'status');

			$record['uniqueid'] = $adb->query_result($result, $i, 'id');
			$record['modifiedtime'] = $adb->query_result($result, $i, 'modifiedtime');
			$record['module'] = $adb->query_result($result, $i, 'module');
			$record['crmid'] = $adb->query_result($result, $i, 'crmid');
			$record['assigneduserid'] = $adb->query_result($result, $i, 'smownerid');

			if ($status == ModTracker::$DELETED) {
				$deletedRecords[] = $record;
			} elseif ($status == ModTracker::$CREATED) {
				$createdRecords[] = $record;
			} elseif ($status == ModTracker::$UPDATED) {
				$updatedRecords[] = $record;
			}

			$modTime[] = $record['modifiedtime'];
			$uniqueIds[] = $record['uniqueid'];
		}

		if (!empty($uniqueIds))
			$maxUniqueId = max($uniqueIds);

		if (empty($maxUniqueId)) {
			$maxUniqueId = $uniqueId;
		}

		if (!empty($modTime)) {
			$maxModifiedTime = max($modTime);
		}
		if (!$maxModifiedTime) {
			$maxModifiedTime = $datetime;
		}

		$output['created'] = $createdRecords;
		$output['updated'] = $updatedRecords;
		$output['deleted'] = $deletedRecords;

		$moreQuery = sprintf('SELECT * FROM vtiger_modtracker_basic WHERE id > ? && changedon >= ? && module
            IN(%s)', generateQuestionMarks($accessibleModules));

		$param = array($maxUniqueId, $maxModifiedTime);
		foreach ($accessibleModules as $entityModule) {
			$param[] = $entityModule;
		}

		$result = $adb->pquery($moreQuery, $param);

		if ($adb->num_rows($result) > 0) {
			$output['more'] = true;
		} else {
			$output['more'] = false;
		}

		$output['uniqueid'] = $maxUniqueId;

		if (!$maxModifiedTime) {
			$modifiedtime = $mtime;
		} else {
			$modifiedtime = vtws_getSeconds($maxModifiedTime);
		}
		if (is_string($modifiedtime)) {
			$modifiedtime = intval($modifiedtime);
		}
		$output['lastModifiedTime'] = $modifiedtime;

		return $output;
	}

	static function getRecordFieldChanges($crmid, $time, $decodeHTML = false)
	{
		$adb = PearDatabase::getInstance();

		$date = date('Y-m-d H:i:s', $time);

		$fieldResult = $adb->pquery('SELECT * FROM vtiger_modtracker_detail
                        INNER JOIN vtiger_modtracker_basic ON vtiger_modtracker_basic.id = vtiger_modtracker_detail.id
                        WHERE crmid = ? && changedon >= ?', array($crmid, $date));
		for ($i = 0; $i < $adb->num_rows($fieldResult); $i++) {
			$fieldName = $adb->query_result($fieldResult, $i, 'fieldname');
			if ($fieldName == 'record_id' || $fieldName == 'record_module' ||
				$fieldName == 'createdtime')
				continue;

			$field['postvalue'] = $adb->query_result($fieldResult, $i, 'postvalue');
			$field['prevalue'] = $adb->query_result($fieldResult, $i, 'prevalue');
			if ($decodeHTML) {
				$field['postvalue'] = decode_html($field['postvalue']);
				$field['prevalue'] = decode_html($field['prevalue']);
			}
			$fields[$fieldName] = $field;
		}
		return $fields;
	}

	static function isViewPermitted($linkData)
	{
		$moduleName = $linkData->getModule();
		$recordId = $linkData->getInputParameter('record');
		if (isPermitted($moduleName, 'DetailView', $recordId) == 'yes') {
			return true;
		}
		return false;
	}

	static function trackRelation($sourceModule, $sourceId, $targetModule, $targetId, $type)
	{
		$adb = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$currentTime = date('Y-m-d H:i:s');

		$id = $adb->getUniqueId('vtiger_modtracker_basic');
		$adb->insert('vtiger_modtracker_basic', [
			'id' => $id,
			'crmid' => $sourceId,
			'module' => $sourceModule,
			'whodid' => $currentUser->getRealId(),
			'changedon' => $currentTime,
			'status' => $type,
			'last_reviewed_users' => '#' . $currentUser->getRealId() . '#'
		]);
		ModTracker_Record_Model::unsetReviewed($sourceId, $currentUser->getRealId(), $id);
		$adb->insert('vtiger_modtracker_relations', [
			'id' => $id,
			'targetmodule' => $targetModule,
			'targetid' => $targetId,
			'changedon' => $currentTime,
		]);
		$isMyRecord = $adb->pquery('SELECT crmid FROM vtiger_crmentity WHERE smownerid <> ? && crmid = ?', array($currentUser->getRealId(), $sourceId));

		if ($adb->num_rows($isMyRecord) > 0)
			$adb->pquery("UPDATE vtiger_crmentity SET was_read = 0 WHERE crmid = ?;", array($sourceId));
	}

	static function linkRelation($sourceModule, $sourceId, $targetModule, $targetId)
	{
		self::trackRelation($sourceModule, $sourceId, $targetModule, $targetId, self::$LINK);
	}

	static function unLinkRelation($sourceModule, $sourceId, $targetModule, $targetId)
	{
		self::trackRelation($sourceModule, $sourceId, $targetModule, $targetId, self::$UNLINK);
	}
}
