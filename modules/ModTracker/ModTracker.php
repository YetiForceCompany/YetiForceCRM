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
		$adb = PearDatabase::getInstance();
		if ($eventType == 'module.postinstall') {
			$adb->pquery('UPDATE vtiger_tab SET customized=0 WHERE name=?', array($moduleName));
			Settings_Vtiger_Module_Model::addSettingsField('LBL_OTHER_SETTINGS', [
				'name' => 'ModTracker',
				'iconpath' => 'adminIcon-modules-track-chanegs',
				'description' => 'LBL_MODTRACKER_DESCRIPTION',
				'linkto' => 'index.php?module=ModTracker&action=BasicSettings&parenttab=Settings&formodule=ModTracker'
			]);
		} else if ($eventType == 'module.disabled') {
			\App\EventHandler::setInActive('ModTracker_ModTrackerHandler_Handler');
		} else if ($eventType == 'module.enabled') {
			\App\EventHandler::setActive('ModTracker_ModTrackerHandler_Handler');
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
		$rows = (new \App\Db\Query())->from('vtiger_modtracker_tabs')->all();
		foreach ($rows as &$row) {
			if ($row['visible'] === 1) {
				App\Cache::save('isTrackingEnabledForModule', $row['tabid'], true, App\Cache::LONG);
				$modules[] = \App\Module::getModuleName($row['tabid']);
			} else {
				App\Cache::save('isTrackingEnabledForModule', $row['tabid'], false, App\Cache::LONG);
			}
		}
		return $modules;
	}

	/**
	 * Invoked to disable tracking for the module.
	 * @param Integer $tabid
	 */
	public function disableTrackingForModule($tabid)
	{
		$db = \App\Db::getInstance();
		if (!static::isModulePresent($tabid)) {
			$db->createCommand()->insert('vtiger_modtracker_tabs', ['tabid' => $tabid, 'visible' => 0])->execute();
		} else {
			$db->createCommand()->update('vtiger_modtracker_tabs', ['visible' => 0], ['tabid' => $tabid])->execute();
		}
		if (static::isModtrackerLinkPresent($tabid)) {
			$moduleInstance = vtlib\Module::getInstance($tabid);
			$moduleInstance->deleteLink('DETAILVIEWBASIC', 'View History');
		}
		$db->createCommand()
			->update('vtiger_field', ['presence' => 1], ['tabid' => $tabid, 'fieldname' => 'was_read'])
			->execute();
		App\Cache::save('isTrackingEnabledForModule', $tabid, false, App\Cache::LONG);
	}

	/**
	 * Invoked to enable tracking for the module.
	 * @param Integer $tabid
	 */
	public function enableTrackingForModule($tabid)
	{
		if (!static::isModulePresent($tabid)) {
			\App\Db::getInstance()->createCommand()->insert('vtiger_modtracker_tabs', ['tabid' => $tabid, 'visible' => 1])->execute();
		} else {
			\App\Db::getInstance()->createCommand()->update('vtiger_modtracker_tabs', ['visible' => 1], ['tabid' => $tabid])->execute();
		}
		\App\Db::getInstance()->createCommand()->update('vtiger_field', ['presence' => 2], ['tabid' => $tabid, 'fieldname' => 'was_read'])->execute();
		if (static::isModtrackerLinkPresent($tabid)) {
			$moduleInstance = vtlib\Module::getInstance($tabid);
			$moduleInstance->addLink('DETAILVIEWBASIC', 'View History', "javascript:ModTrackerCommon.showhistory('\$RECORD\$')", '', '', array('path' => 'modules/ModTracker/ModTracker.php', 'class' => 'ModTracker', 'method' => 'isViewPermitted'));
		}
		App\Cache::save('isTrackingEnabledForModule', $tabid, true, App\Cache::LONG);
	}

	/**
	 * Invoked to check if tracking is enabled or disabled for the module.
	 * @param String $moduleName
	 */
	public static function isTrackingEnabledForModule($moduleName)
	{
		$tabId = \App\Module::getModuleId($moduleName);
		if (App\Cache::has('isTrackingEnabledForModule', $tabId)) {
			return App\Cache::get('isTrackingEnabledForModule', $tabId);
		}
		$isExists = (new \App\Db\Query())->from('vtiger_modtracker_tabs')
			->where(['vtiger_modtracker_tabs.visible' => 1, 'vtiger_modtracker_tabs.tabid' => $tabId])
			->exists();
		App\Cache::save('isTrackingEnabledForModule', $tabId, $isExists, App\Cache::LONG);
		return $isExists;
	}

	/**
	 * Invoked to check if the module is present in the table or not.
	 * @param int $tabId
	 */
	public static function isModulePresent($tabId)
	{
		if (!App\Cache::has('isTrackingEnabledForModule', $tabId)) {
			$row = (new \App\Db\Query())->from('vtiger_modtracker_tabs')->where(['tabid' => $tabId])->one();
			if ($row) {
				App\Cache::save('isTrackingEnabledForModule', $tabId, (bool) $row['visible'], App\Cache::LONG);
				return true;
			} else {
				return false;
			}
		}
		return true;
	}

	/**
	 * Invoked to check if ModTracker links are enabled for the module.
	 * @param Integer $tabid
	 */
	public static function isModtrackerLinkPresent($tabid)
	{
		return (new \App\Db\Query())->from('vtiger_links')
				->where(['linktype' => 'DETAILVIEWBASIC', 'linklabel' => 'View History', 'tabid' => $tabid])
				->exists();
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
			$query .= " LIMIT $limit";

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

	public static function getRecordFieldChanges($crmid, $time, $decodeHTML = false)
	{
		$adb = PearDatabase::getInstance();

		$date = date('Y-m-d H:i:s', $time);

		$fieldResult = $adb->pquery('SELECT * FROM vtiger_modtracker_detail
                        INNER JOIN vtiger_modtracker_basic ON vtiger_modtracker_basic.id = vtiger_modtracker_detail.id
                        WHERE crmid = ? && changedon >= ?', array($crmid, $date));
		$countFieldResult = $adb->num_rows($fieldResult);
		for ($i = 0; $i < $countFieldResult; $i++) {
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

	public static function isViewPermitted($linkData)
	{
		$moduleName = $linkData->getModule();
		$recordId = $linkData->getInputParameter('record');
		if (isPermitted($moduleName, 'DetailView', $recordId) == 'yes') {
			return true;
		}
		return false;
	}

	public static function trackRelation($sourceModule, $sourceId, $targetModule, $targetId, $type)
	{
		$db = App\Db::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$currentTime = date('Y-m-d H:i:s');
		$db->createCommand()->insert('vtiger_modtracker_basic', [
			'crmid' => $sourceId,
			'module' => $sourceModule,
			'whodid' => $currentUser->getRealId(),
			'changedon' => $currentTime,
			'status' => $type,
			'last_reviewed_users' => '#' . $currentUser->getRealId() . '#'
		])->execute();
		$id = $db->getLastInsertID('vtiger_modtracker_basic_id_seq');
		ModTracker_Record_Model::unsetReviewed($sourceId, $currentUser->getRealId(), $id);
		$db->createCommand()->insert('vtiger_modtracker_relations', [
			'id' => $id,
			'targetmodule' => $targetModule,
			'targetid' => $targetId,
			'changedon' => $currentTime,
		])->execute();
		$isMyRecord = (new App\Db\Query())->from('vtiger_crmentity')
			->where(['<>', 'smownerid', $currentUser->getRealId()])
			->andWhere(['crmid' => $sourceId])
			->exists();
		if ($isMyRecord) {
			$db->createCommand()
				->update('vtiger_crmentity', ['was_read' => 0], ['crmid' => $sourceId])
				->execute();
		}
	}

	/**
	 * Function is executed when adding related record
	 * @param string $sourceModule
	 * @param int $sourceId
	 * @param string $targetModule
	 * @param int $targetId
	 */
	public static function linkRelation($sourceModule, $sourceId, $targetModule, $targetId)
	{
		self::trackRelation($sourceModule, $sourceId, $targetModule, $targetId, self::$LINK);
		if (in_array($sourceModule, AppConfig::module('ModTracker', 'SHOW_TIMELINE_IN_LISTVIEW')) && \App\Privilege::isPermitted($sourceModule, 'TimeLineList')) {
			ModTracker_Record_Model::setLastRelation($sourceId, $sourceModule);
		}
	}

	/**
	 * Function is executed when removing related record
	 * @param string $sourceModule
	 * @param int $sourceId
	 * @param string $targetModule
	 * @param int $targetId
	 */
	public static function unLinkRelation($sourceModule, $sourceId, $targetModule, $targetId)
	{
		self::trackRelation($sourceModule, $sourceId, $targetModule, $targetId, self::$UNLINK);
		if (in_array($sourceModule, AppConfig::module('ModTracker', 'SHOW_TIMELINE_IN_LISTVIEW')) && \App\Privilege::isPermitted($sourceModule, 'TimeLineList')) {
			ModTracker_Record_Model::setLastRelation($sourceId, $sourceModule);
		}
	}
}
