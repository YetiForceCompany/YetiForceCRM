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

class Calendar_Record_Model extends Vtiger_Record_Model
{
	public static function getNameByReference($refModuleName)
	{
		$fieldName = Vtiger_Cache::get('NameRelatedField', $refModuleName . '-Calendar');
		if (!empty($fieldName)) {
			return $fieldName;
		}
		$parentModuleModel = Vtiger_Module_Model::getInstance($refModuleName);
		$relatedModule = Vtiger_Module_Model::getInstance('Calendar');
		$relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModule);
		if ($relationModel && $relationModel->getRelationField()) {
			$fieldName = $relationModel->getRelationField()->getFieldName();
			Vtiger_Cache::set('NameRelatedField', $refModuleName . '-Calendar', $fieldName);
		}

		return $fieldName;
	}

	/**
	 * Set crm activity.
	 *
	 * @param array  $referenceIds
	 * @param string $refModuleName
	 */
	public static function setCrmActivity($referenceIds, $refModuleName = false)
	{
		$db = \App\Db::getInstance();
		foreach ($referenceIds as $id => $fieldName) {
			if (empty($fieldName)) {
				$fieldName = self::getNameByReference($refModuleName);
			}
			if (empty($fieldName)) {
				continue;
			}
			$row = (new \App\Db\Query())->select(['vtiger_activity.status', 'vtiger_activity.date_start'])
				->from('vtiger_activity')
				->innerJoin('vtiger_crmentity', 'vtiger_activity.activityid=vtiger_crmentity.crmid')
				->where(['vtiger_crmentity.deleted' => 0, "vtiger_activity.$fieldName" => $id, 'vtiger_activity.status' => Calendar_Module_Model::getComponentActivityStateLabel('current')])
				->orderBy(['vtiger_activity.date_start' => SORT_ASC])->one();
			if ($row) {
				$db->createCommand()->update('vtiger_entity_stats', ['crmactivity' => (int) \App\Fields\Date::getDiff(date('Y-m-d'), $row['date_start'], '%r%a')], ['crmid' => $id])->execute();
			} else {
				$db->createCommand()->update(('vtiger_entity_stats'), ['crmactivity' => null], ['crmid' => $id])->execute();
			}
		}
	}

	/**
	 * Function returns the Entity Name of Record Model.
	 *
	 * @return string
	 */
	public function getName()
	{
		$name = \App\Purifier::encodeHtml($this->get('subject'));
		if (empty($name)) {
			$name = parent::getName();
		}

		return $name;
	}

	/**
	 * Function to insert details about reminder in to Database.
	 *
	 * @param <Date>    $reminderSent
	 * @param <integer> $recurId
	 * @param string    $reminderMode like edit/delete
	 */
	public function setActivityReminder($reminderSent = 0, $recurId = '', $reminderMode = '')
	{
		$moduleInstance = CRMEntity::getInstance($this->getModuleName());
		$moduleInstance->activityReminder($this->getId(), $this->get('reminder_time'), $reminderSent, $recurId, $reminderMode);
	}

	/**
	 * Function returns the Module Name based on the activity type.
	 *
	 * @return string
	 */
	public function getType()
	{
		$activityType = $this->get('activitytype');
		if ($activityType == 'Task') {
			return 'Calendar';
		}

		return 'Events';
	}

	/**
	 * Function to get the Detail View url for the record.
	 *
	 * @return string - Record Detail View Url
	 */
	public function getDetailViewUrl()
	{
		$module = $this->getModule();

		return 'index.php?module=Calendar&view=' . $module->getDetailViewName() . '&record=' . $this->getId();
	}

	public function saveToDb()
	{
		//Time should changed to 24hrs format
		\App\Request::_set('time_start', Vtiger_Time_UIType::getTimeValueWithSeconds(\App\Request::_get('time_start')));
		\App\Request::_set('time_end', Vtiger_Time_UIType::getTimeValueWithSeconds(\App\Request::_get('time_end')));
		parent::saveToDb();
		$this->updateActivityReminder();
		$this->insertIntoInviteTable();
		$this->insertIntoActivityReminderPopup();
	}

	/**
	 * Prepare value to save.
	 *
	 * @return array
	 */
	public function getValuesForSave()
	{
		$forSave = parent::getValuesForSave();
		if ($this->isNew()) {
			$forSave['vtiger_crmentity']['setype'] = 'Calendar';
		}
		if (isset($forSave['vtiger_crmentity']['smownerid'])) {
			$forSave['vtiger_activity']['smownerid'] = $forSave['vtiger_crmentity']['smownerid'];
		}
		unset($forSave['vtiger_activity_reminder']);

		return $forSave;
	}

	/**
	 * Update cctivity reminder.
	 */
	public function updateActivityReminder()
	{
		if ($this->get('set_reminder') === null) {
			return false;
		}
		$db = \App\Db::getInstance();
		if ($this->get('set_reminder') !== false) {
			$reminderTime = $this->get('set_reminder');
			$activityReminderExists = (new \App\Db\Query())->select(['activity_id'])
				->from('vtiger_activity_reminder')
				->where(['activity_id' => $this->getId()])
				->exists();
			if ($activityReminderExists) {
				$db->createCommand()->update('vtiger_activity_reminder', [
					'reminder_time' => $reminderTime,
					'reminder_sent' => 0,
					], ['activity_id' => $this->getId()])->execute();
			} else {
				$db->createCommand()->insert('vtiger_activity_reminder', [
					'reminder_time' => $reminderTime,
					'reminder_sent' => 0,
					'activity_id' => $this->getId(),
				])->execute();
			}
		} else {
			$db->createCommand()->delete('vtiger_activity_reminder', ['activity_id' => $this->getId()])->execute();
		}
	}

	/**
	 * Function to insert values in u_yf_activity_invitation table for the specified module,tablename ,invitees_array.
	 */
	public function insertIntoInviteTable()
	{
		if (!\App\Request::_has('inviteesid')) {
			\App\Log::info('No invitations in request, Exiting insertIntoInviteeTable method ...');

			return;
		}
		\App\Log::trace('Entering ' . __METHOD__);
		$db = App\Db::getInstance();
		$inviteesRequest = \App\Request::_getArray('inviteesid');
		$dataReader = (new \App\Db\Query())->from('u_#__activity_invitation')->where(['activityid' => $this->getId()])->createCommand()->query();
		$invities = [];
		while ($row = $dataReader->read()) {
			$invities[$row['inviteesid']] = $row;
		}
		$dataReader->close();
		if (!empty($inviteesRequest)) {
			foreach ($inviteesRequest as &$invitation) {
				if (isset($invities[$invitation[2]])) {
					unset($invities[$invitation[2]]);
				} else {
					$db->createCommand()->insert('u_#__activity_invitation', [
						'email' => $invitation[0],
						'crmid' => $invitation[1],
						'activityid' => $this->getId(),
					])->execute();
				}
			}
		}
		foreach ($invities as &$invitation) {
			$db->createCommand()->delete('u_#__activity_invitation', ['inviteesid' => $invitation['inviteesid']])->execute();
		}
		\App\Log::trace('Exiting ' . __METHOD__);
	}

	/**
	 * Update event in popup reminder.
	 */
	public function insertIntoActivityReminderPopup()
	{
		$cbrecord = $this->getId();
		if (!empty($cbrecord)) {
			$cbdate = \App\Fields\Date::formatToDb($this->get('date_start'));
			$cbtime = $this->get('time_start');
			$reminderid = (new \App\Db\Query())->select(['reminderid'])->from('vtiger_activity_reminder_popup')
				->where(['recordid' => $cbrecord])
				->scalar();
			$currentStates = Calendar_Module_Model::getComponentActivityStateLabel('current');
			$state = Calendar_Module_Model::getCalendarState($this->getData());
			if (in_array($state, $currentStates)) {
				$status = 0;
			} else {
				$status = 1;
			}
			if (!empty($reminderid)) {
				\App\Db::getInstance()->createCommand()->update('vtiger_activity_reminder_popup', [
					'datetime' => "$cbdate $cbtime",
					'status' => $status,
					], ['reminderid' => $reminderid]
				)->execute();
			} else {
				\App\Db::getInstance()->createCommand()->insert('vtiger_activity_reminder_popup', [
					'recordid' => $cbrecord,
					'datetime' => "$cbdate $cbtime",
					'status' => $status,
				])->execute();
			}
		}
	}

	/**
	 * Function updates the Calendar Reminder popup's status.
	 */
	public function updateReminderStatus($status = 1)
	{
		\App\Db::getInstance()->createCommand()
			->update('vtiger_activity_reminder_popup', [
				'status' => $status,
				], ['recordid' => $this->getId()])
				->execute();
	}

	/**
	 * Update reminder postpone.
	 *
	 * @param string $time
	 */
	public function updateReminderPostpone($time)
	{
		switch ($time) {
			case '15m':
				$datatime = date('Y-m-d H:i:s', strtotime('+15 min'));
				break;
			case '30m':
				$datatime = date('Y-m-d H:i:s', strtotime('+30 min'));
				break;
			case '1h':
				$datatime = date('Y-m-d H:i:s', strtotime('+60 min'));
				break;
			case '2h':
				$datatime = date('Y-m-d H:i:s', strtotime('+120 min'));
				break;
			case '6h':
				$datatime = date('Y-m-d H:i:s', strtotime('+6 hour'));
				break;
			case '1d':
				$datatime = date('Y-m-d H:i:s', strtotime('+1 day'));
				break;
		}
		$datatimeSTR = strtotime($datatime);
		$timeStart = date('H:i:s', $datatimeSTR);
		$dateStart = date('Y-m-d', $datatimeSTR);
		\App\Db::getInstance()->createCommand()
			->update('vtiger_activity_reminder_popup', [
				'status' => 0,
				'datetime' => date('Y-m-d H:i:s', $datatimeSTR),
				], ['recordid' => $this->getId()])
				->execute();
		if ((new App\Db\Query())->select(['value'])->from('vtiger_calendar_config')
			->where(['type' => 'reminder', 'name' => 'update_event', 'value' => 1])
			->exists()) {
			$row = (new App\Db\Query())->select(['date_start', 'time_start', 'due_date', 'time_end'])
				->from('vtiger_activity')
				->where(['activityid' => $this->getId()])->one();
			$dueDateRecord = $row['due_date'];
			$timeEndRecord = $row['time_end'];
			$duration = strtotime($dueDateRecord . ' ' . $timeEndRecord) - strtotime($row['date_start'] . ' ' . $row['time_start']);
			$timeEndRecord = date('H:i:s', $datatimeSTR + $duration);
			$dueDateRecord = date('Y-m-d', $datatimeSTR + $duration);
			$this->set('date_start', $dateStart);
			$this->set('time_start', $timeStart);
			$this->set('due_date', $dueDateRecord);
			$this->set('time_end', $timeEndRecord);
			$this->save();
		}
	}

	public function getActivityTypeIcon()
	{
		$icon = $this->get('activitytype');
		if ($icon == 'Task') {
			$icon = 'Tasks';
		}

		return $icon . '.png';
	}

	/**
	 * Function to get modal view url for the record.
	 *
	 * @return string - Record Detail View Url
	 */
	public function getActivityStateModalUrl()
	{
		return 'index.php?module=Calendar&view=ActivityStateModal&record=' . $this->getId();
	}

	/**
	 * {@inheritdoc}
	 */
	public function changeState($state)
	{
		parent::changeState($state);
		$stateId = 0;
		switch ($state) {
			case 'Active':
				$stateId = 0;
				break;
			case 'Trash':
				$stateId = 1;
				break;
			case 'Archived':
				$stateId = 2;
				break;
		}
		\App\Db::getInstance()->createCommand()->update('vtiger_activity', ['deleted' => $stateId], ['activityid' => $this->getId()])->execute();
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete()
	{
		parent::delete();
		\App\Db::getInstance()->createCommand()->delete('vtiger_activity_reminder', ['activity_id' => $this->getId()])->execute();
	}

	/**
	 * Function to get the list view actions for the record.
	 *
	 * @return Vtiger_Link_Model[] - Associate array of Vtiger_Link_Model instances
	 */
	public function getRecordListViewLinksLeftSide()
	{
		$links = parent::getRecordListViewLinksLeftSide();
		$recordLinks = [];
		$statuses = Calendar_Module_Model::getComponentActivityStateLabel('current');
		if ($this->isEditable() && in_array($this->getValueByField('activitystatus'), $statuses)) {
			$recordLinks[] = [
				'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
				'linklabel' => 'LBL_SET_RECORD_STATUS',
				'linkurl' => $this->getActivityStateModalUrl(),
				'linkicon' => 'fas fa-check',
				'linkclass' => 'btn-sm btn-default',
				'modalView' => true,
			];
		}
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}

		return $links;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRecordRelatedListViewLinksLeftSide(Vtiger_RelationListView_Model $viewModel)
	{
		$links = parent::getRecordRelatedListViewLinksLeftSide($viewModel);
		if ($viewModel->getRelationModel()->isEditable() && $this->isEditable()) {
			if (in_array($this->getValueByField('activitystatus'), Calendar_Module_Model::getComponentActivityStateLabel('current'))) {
				$links['LBL_SET_RECORD_STATUS'] = Vtiger_Link_Model::getInstanceFromValues([
						'linklabel' => 'LBL_SET_RECORD_STATUS',
						'linkhref' => true,
						'linkurl' => $this->getActivityStateModalUrl(),
						'linkicon' => 'fas fa-check',
						'linkclass' => 'btn-xs btn-default',
						'modalView' => true,
				]);
			}
			if ($viewModel->getRelationModel()->isEditable() && $this->isEditable()) {
				$links['LBL_EDIT'] = Vtiger_Link_Model::getInstanceFromValues([
						'linklabel' => 'LBL_EDIT',
						'linkurl' => $this->getEditViewUrl(),
						'linkhref' => true,
						'linkicon' => 'fas fa-edit',
						'linkclass' => 'btn-xs btn-default',
				]);
			}
		}

		return $links;
	}
}
