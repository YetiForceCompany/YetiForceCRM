<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

class ModTracker
{
	/**
	 * Constant variables which indicates the status of the changed record.
	 */
	public static $UPDATED = 0;
	public static $TRASH = 1;
	public static $CREATED = 2;
	public static $ACTIVE = 3;
	public static $LINK = 4;
	public static $UNLINK = 5;
	public static $CONVERTTOACCOUNT = 6;
	public static $DISPLAYED = 7;
	public static $ARCHIVED = 8;
	public static $DELETED = 9;
	public static $TRANSFER_EDIT = 10;
	public static $TRANSFER_DELETE = 11;
	public static $TRANSFER_UNLINK = 12;
	public static $TRANSFER_LINK = 13;
	public static $SHOW_HIDDEN_DATA = 14;

	/**
	 * Icon actions.
	 *
	 * @var array
	 */
	public static $iconActions = [
		0 => 'yfi yfi-full-editing-view',
		1 => 'fas fa-trash-alt',
		2 => 'fas fa-plus',
		3 => 'fas fa-undo-alt',
		4 => 'fas fa-link',
		5 => 'fas fa-unlink',
		6 => 'fas fa-exchange-alt',
		7 => 'fas fa-th-list',
		8 => 'fas fa-archive',
		9 => 'fas fa-eraser',
		10 => 'yfi yfi-full-editing-view',
		11 => 'fas fa-trash-alt',
		12 => 'fas fa-unlink',
		13 => 'fas fa-link',
		14 => 'fas fa-eye',
	];

	/**
	 * Colors actions.
	 *
	 * @var array
	 */
	public static $colorsActions = [
		0 => '#9c27b0',
		1 => '#ab0505',
		2 => '#607d8b',
		3 => '#009405',
		4 => '#009cb9',
		5 => '#de9100',
		6 => '#e2e3e5',
		7 => '#65a9ff',
		8 => '#0032a2',
		9 => '#000',
		10 => '#000',
		11 => '#000',
		12 => '#000',
		13 => '#000',
		14 => '#000',
	];

	public static function getAllActionsTypes()
	{
		return [
			static::$UPDATED => 'LBL_AT_UPDATE',
			static::$TRASH => 'LBL_AT_TRASH',
			static::$CREATED => 'LBL_AT_CREATE',
			static::$ACTIVE => 'LBL_AT_ACTIVE',
			static::$LINK => 'LBL_AT_LINK',
			static::$UNLINK => 'LBL_AT_UNLINK',
			static::$CONVERTTOACCOUNT => 'LBL_AT_CONVERTTOACCOUNT',
			static::$DISPLAYED => 'LBL_AT_DISPLAY',
			static::$ARCHIVED => 'LBL_AT_ARCHIVED',
			static::$DELETED => 'LBL_AT_DELETE',
			static::$TRANSFER_EDIT => 'LBL_AT_TRANSFER_EDIT',
			static::$TRANSFER_DELETE => 'LBL_AT_TRANSFER_DELETE',
			static::$TRANSFER_UNLINK => 'LBL_AT_TRANSFER_UNLINK',
			static::$TRANSFER_LINK => 'LBL_AT_TRANSFER_LINK',
			static::$SHOW_HIDDEN_DATA => 'LBL_SHOW_HIDDEN_DATA',
		];
	}

	/**
	 * Invoked when special actions are performed on the module.
	 *
	 * @param string $moduleName
	 * @param string $eventType
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		if ('module.postinstall' === $eventType) {
		} elseif ('module.disabled' === $eventType) {
			\App\EventHandler::setInActive('ModTracker_ModTrackerHandler_Handler');
		} elseif ('module.enabled' === $eventType) {
			\App\EventHandler::setActive('ModTracker_ModTrackerHandler_Handler');
		}
	}

	/**
	 * Invoked to disable tracking for the module.
	 *
	 * @param int $tabid
	 */
	public function disableTrackingForModule($tabid)
	{
		$db = \App\Db::getInstance();
		if (!static::isModulePresent($tabid)) {
			$db->createCommand()->insert('vtiger_modtracker_tabs', ['tabid' => $tabid, 'visible' => 0])->execute();
		} else {
			$db->createCommand()->update('vtiger_modtracker_tabs', ['visible' => 0], ['tabid' => $tabid])->execute();
		}
		$moduleInstance = vtlib\Module::getInstance($tabid);
		if (static::isModtrackerLinkPresent($tabid)) {
			$moduleInstance->deleteLink('DETAIL_VIEW_ADDITIONAL', 'View History');
		}
		$moduleInstance->disableTools('ModTracker');
		$db->createCommand()
			->update('vtiger_field', ['presence' => 1], ['tabid' => $tabid, 'fieldname' => 'was_read'])
			->execute();
		App\Cache::save('isTrackingEnabledForModule', $tabid, false, App\Cache::LONG);
		\App\Cache::delete('getTrackingModules', 'all');
	}

	/**
	 * Invoked to enable tracking for the module.
	 *
	 * @param int $tabid
	 */
	public function enableTrackingForModule($tabid)
	{
		if (!static::isModulePresent($tabid)) {
			\App\Db::getInstance()->createCommand()->insert('vtiger_modtracker_tabs', ['tabid' => $tabid, 'visible' => 1])->execute();
		} else {
			\App\Db::getInstance()->createCommand()->update('vtiger_modtracker_tabs', ['visible' => 1], ['tabid' => $tabid])->execute();
		}
		\App\Db::getInstance()->createCommand()->update('vtiger_field', ['presence' => 2], ['tabid' => $tabid, 'fieldname' => 'was_read'])->execute();
		$moduleInstance = vtlib\Module::getInstance($tabid);
		if (static::isModtrackerLinkPresent($tabid)) {
			$moduleInstance->addLink('DETAIL_VIEW_ADDITIONAL', 'View History', "javascript:ModTrackerCommon.showhistory('\$RECORD\$')", '', '', ['path' => 'modules/ModTracker/ModTracker.php', 'class' => 'ModTracker', 'method' => 'isViewPermitted']);
		}
		$moduleInstance->enableTools('ModTracker');
		App\Cache::save('isTrackingEnabledForModule', $tabid, true, App\Cache::LONG);
		\App\Cache::delete('getTrackingModules', 'all');
	}

	/**
	 * Invoked to check if tracking is enabled or disabled for the module.
	 *
	 * @param string $moduleName
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
	 *
	 * @param int $tabId
	 */
	public static function isModulePresent($tabId)
	{
		if (!App\Cache::has('isTrackingEnabledForModule', $tabId)) {
			$row = (new \App\Db\Query())->from('vtiger_modtracker_tabs')->where(['tabid' => $tabId])->one();
			if ($row) {
				App\Cache::save('isTrackingEnabledForModule', $tabId, (bool) $row['visible'], App\Cache::LONG);

				return true;
			}
			return false;
		}
		return true;
	}

	/**
	 * Invoked to check if ModTracker links are enabled for the module.
	 *
	 * @param int $tabid
	 */
	public static function isModtrackerLinkPresent($tabid)
	{
		return (new \App\Db\Query())->from('vtiger_links')
			->where(['linktype' => 'DETAIL_VIEW_ADDITIONAL', 'linklabel' => 'View History', 'tabid' => $tabid])
			->exists();
	}

	/**
	 * This function checks access to the view.
	 *
	 * @param \vtlib\LinkData $linkData
	 *
	 * @return bool
	 */
	public static function isViewPermitted(vtlib\LinkData $linkData)
	{
		$moduleName = $linkData->getModule();
		$recordId = $linkData->getInputParameter('record');
		if (\App\Privilege::isPermitted($moduleName, 'DetailView', $recordId)) {
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
	 * Function is executed when adding related record.
	 *
	 * @param string $sourceModule
	 * @param int    $sourceId
	 * @param string $targetModule
	 * @param int    $targetId
	 */
	public static function linkRelation($sourceModule, $sourceId, $targetModule, $targetId)
	{
		self::trackRelation($sourceModule, $sourceId, $targetModule, $targetId, self::$LINK);
		if (\in_array($targetModule, Vtiger_HistoryRelation_Widget::getActions()) && \in_array($sourceModule, App\Config::module('ModTracker', 'SHOW_TIMELINE_IN_LISTVIEW')) && \App\Privilege::isPermitted($sourceModule, 'TimeLineList')) {
			ModTracker_Record_Model::setLastRelation($sourceId, $sourceModule);
		}
	}

	/**
	 * Function is executed when removing related record.
	 *
	 * @param string $sourceModule
	 * @param int    $sourceId
	 * @param string $targetModule
	 * @param int    $targetId
	 */
	public static function unLinkRelation($sourceModule, $sourceId, $targetModule, $targetId)
	{
		self::trackRelation($sourceModule, $sourceId, $targetModule, $targetId, self::$UNLINK);
		if (\in_array($targetModule, Vtiger_HistoryRelation_Widget::getActions()) && \in_array($sourceModule, App\Config::module('ModTracker', 'SHOW_TIMELINE_IN_LISTVIEW')) && \App\Privilege::isPermitted($sourceModule, 'TimeLineList')) {
			ModTracker_Record_Model::setLastRelation($sourceId, $sourceModule);
		}
	}

	/**
	 * Transfer relation.
	 *
	 * @param string $sourceModule
	 * @param int    $sourceId
	 * @param string $targetModule
	 * @param int    $targetId
	 * @param int    $process
	 */
	public static function transferRelation(string $sourceModule, int $sourceId, string $targetModule, int $targetId, int $process)
	{
		self::trackRelation($sourceModule, $sourceId, $targetModule, $targetId, $process);
		if (\in_array($targetModule, Vtiger_HistoryRelation_Widget::getActions()) && \in_array($sourceModule, App\Config::module('ModTracker', 'SHOW_TIMELINE_IN_LISTVIEW')) && \App\Privilege::isPermitted($sourceModule, 'TimeLineList')) {
			ModTracker_Record_Model::setLastRelation($sourceId, $sourceModule);
		}
	}

	/**
	 * Function gives an array of module names for which tracking is enabled.
	 *
	 * @return string[]
	 */
	public static function getTrackingModules(): array
	{
		$cacheName = 'all';
		if (App\Cache::has(__FUNCTION__, $cacheName)) {
			$modules = App\Cache::get(__FUNCTION__, $cacheName);
		} else {
			$dataReader = (new \App\Db\Query())
				->select(['vtiger_tab.tabid', 'vtiger_tab.name'])
				->from('vtiger_tab')
				->innerJoin('vtiger_modtracker_tabs', 'vtiger_modtracker_tabs.tabid = vtiger_tab.tabid')
				->where(['vtiger_modtracker_tabs.visible' => 1, 'vtiger_tab.presence' => 0, 'vtiger_tab.isentitytype' => 1])
				->createCommand()->query();
			$modules = [];
			while ($row = $dataReader->read()) {
				App\Cache::save('isTrackingEnabledForModule', $row['tabid'], true, App\Cache::LONG);
				$modules[$row['tabid']] = $row['name'];
			}
			$dataReader->close();
			App\Cache::save(__FUNCTION__, $cacheName, $modules);
		}
		return $modules;
	}
}
