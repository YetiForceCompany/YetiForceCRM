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

class Calendar_Record_Model extends Vtiger_Record_Model
{
	/**
	 *  Show Reminder popup.
	 */
	const REMNDER_POPUP_ACTIVE = 0;
	/**
	 * Skip reminder popup.
	 */
	const REMNDER_POPUP_INACTIVE = 1;
	/**
	 * Wait to show reminder popup.
	 */
	const REMNDER_POPUP_WAIT = 2;

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
	public static function setCrmActivity($referenceIds, $refModuleName = null)
	{
		$db = \App\Db::getInstance();
		foreach ($referenceIds as $id => $fieldName) {
			if (empty($fieldName)) {
				$fieldName = self::getNameByReference($refModuleName);
			}
			if (empty($fieldName) || !\App\Record::isExists($id)) {
				continue;
			}
			$fieldModel = Vtiger_Module_Model::getInstance($refModuleName ?? \App\Record::getType($id))->getFieldByName('crmactivity');
			if (false === $fieldModel || !$fieldModel->isActiveField()) {
				continue;
			}
			$row = (new \App\Db\Query())->select(['vtiger_activity.status', 'vtiger_activity.date_start'])
				->from('vtiger_activity')
				->innerJoin('vtiger_crmentity', 'vtiger_activity.activityid=vtiger_crmentity.crmid')
				->where(['vtiger_crmentity.deleted' => 0, "vtiger_activity.$fieldName" => $id, 'vtiger_activity.status' => Calendar_Module_Model::getComponentActivityStateLabel('current')])
				->orderBy(['vtiger_activity.date_start' => SORT_ASC])->one();
			if ($row) {
				$db->createCommand()->update('vtiger_entity_stats', ['crmactivity' => (int) \App\Fields\DateTime::getDiff(date('Y-m-d'), $row['date_start'], '%r%a')], ['crmid' => $id])->execute();
			} else {
				$db->createCommand()->update(('vtiger_entity_stats'), ['crmactivity' => null], ['crmid' => $id])->execute();
			}
		}
	}

	/**
	 * Function returns the Module Name based on the activity type.
	 *
	 * @return string
	 */
	public function getType()
	{
		return 'Calendar';
	}

	/**
	 * Function to get the Detail View url for the record.
	 *
	 * @return string - Record Detail View Url
	 */
	public function getDetailViewUrl()
	{
		return 'index.php?module=Calendar&view=' . $this->getModule()->getDetailViewName() . '&record=' . $this->getId();
	}

	/** {@inheritdoc} */
	public function saveToDb()
	{
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
		if (!$this->isNew() && false === $this->getPreviousValue('reminder_time')) {
			return false;
		}
		$db = \App\Db::getInstance();
		if (!$this->isEmpty('reminder_time')) {
			$activityReminderExists = (new \App\Db\Query())->select(['activity_id'])
				->from('vtiger_activity_reminder')
				->where(['activity_id' => $this->getId()])
				->exists();
			if ($activityReminderExists) {
				$db->createCommand()->update('vtiger_activity_reminder', [
					'reminder_time' => $this->get('reminder_time'),
					'reminder_sent' => 0
				], ['activity_id' => $this->getId()])->execute();
			} else {
				$db->createCommand()->insert('vtiger_activity_reminder', [
					'reminder_time' => $this->get('reminder_time'),
					'reminder_sent' => 0,
					'activity_id' => $this->getId()
				])->execute();
			}
		} else {
			$db->createCommand()->delete('vtiger_activity_reminder', ['activity_id' => $this->getId()])->execute();
		}
	}

	/** {@inheritdoc} */
	public function isMandatorySave()
	{
		return true;
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
				if (\App\TextUtils::getTextLength($invitation[0]) > 100 || !\App\Validator::email($invitation[0])) {
					throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||inviteesid||Calendar||' . $invitation[0], 406);
				}
				if (isset($invities[$invitation[2]])) {
					unset($invities[$invitation[2]]);
				} else {
					$db->createCommand()->insert('u_#__activity_invitation', [
						'email' => $invitation[0],
						'crmid' => (int) $invitation[1],
						'name' => $invitation[3],
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
			$cbdate = $this->get('date_start');
			$cbtime = $this->get('time_start');
			$reminderid = (new \App\Db\Query())->select(['reminderid'])->from('vtiger_activity_reminder_popup')
				->where(['recordid' => $cbrecord])
				->scalar();
			$currentStates = Calendar_Module_Model::getComponentActivityStateLabel('current');
			$state = Calendar_Module_Model::getCalendarState($this->getData());
			if (\in_array($state, $currentStates)) {
				$status = self::REMNDER_POPUP_ACTIVE;
			} else {
				$status = self::REMNDER_POPUP_INACTIVE;
			}
			if (!empty($reminderid)) {
				\App\Db::getInstance()->createCommand()->update('vtiger_activity_reminder_popup', [
					'datetime' => "$cbdate $cbtime",
					'status' => $status
				], ['reminderid' => $reminderid]
				)->execute();
			} else {
				\App\Db::getInstance()->createCommand()->insert('vtiger_activity_reminder_popup', [
					'recordid' => $cbrecord,
					'datetime' => "$cbdate $cbtime",
					'status' => $status
				])->execute();
			}
		}
	}

	/**
	 * Update reminder postpone.
	 *
	 * @param string $time
	 *
	 * @throws \yii\db\Exception
	 */
	public function updateReminderPostpone(string $time)
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
			default:
				break;
		}
		if ((new App\Db\Query())->select(['value'])->from('vtiger_calendar_config')
			->where(['type' => 'reminder', 'name' => 'update_event', 'value' => 1])
			->exists()) {
			$row = (new App\Db\Query())->select(['date_start', 'time_start', 'due_date', 'time_end'])
				->from('vtiger_activity')
				->where(['activityid' => $this->getId()])->one();
			$datatimeSTR = strtotime($datatime);
			$duration = strtotime($row['due_date'] . ' ' . $row['time_end']) - strtotime($row['date_start'] . ' ' . $row['time_start']);
			$this->set('date_start', date('Y-m-d', $datatimeSTR));
			$this->set('time_start', date('H:i:s', $datatimeSTR));
			$this->set('due_date', date('Y-m-d', $datatimeSTR + $duration));
			$this->set('time_end', date('H:i:s', $datatimeSTR + $duration));
			$this->save();
		} else {
			\App\Db::getInstance()->createCommand()
				->update('vtiger_activity_reminder_popup', [
					'status' => self::REMNDER_POPUP_WAIT,
					'datetime' => $datatime
				], ['recordid' => $this->getId()])
				->execute();
		}
	}

	public function getActivityTypeIcon()
	{
		$icon = $this->get('activitytype');
		if ('Task' == $icon) {
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

	/** {@inheritdoc} */
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
			default:
				break;
		}
		\App\Db::getInstance()->createCommand()->update('vtiger_activity', ['deleted' => $stateId], ['activityid' => $this->getId()])->execute();
	}

	/** {@inheritdoc} */
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
		if ($this->isEditable() && \in_array($this->getValueByField('activitystatus'), $statuses)) {
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

	/** {@inheritdoc} */
	public function getRecordRelatedListViewLinksLeftSide(Vtiger_RelationListView_Model $viewModel)
	{
		$links = parent::getRecordRelatedListViewLinksLeftSide($viewModel);
		if ($viewModel->getRelationModel()->isEditable() && $this->isEditable()) {
			if (\in_array($this->getValueByField('activitystatus'), Calendar_Module_Model::getComponentActivityStateLabel('current'))) {
				$links['LBL_SET_RECORD_STATUS'] = Vtiger_Link_Model::getInstanceFromValues([
					'linklabel' => 'LBL_SET_RECORD_STATUS',
					'linkhref' => true,
					'linkurl' => $this->getActivityStateModalUrl(),
					'linkicon' => 'fas fa-check',
					'linkclass' => 'btn-sm btn-default',
					'modalView' => true,
				]);
			}
			if ($viewModel->getRelationModel()->isEditable() && $this->isEditable()) {
				$links['LBL_EDIT'] = Vtiger_Link_Model::getInstanceFromValues([
					'linklabel' => 'LBL_EDIT',
					'linkurl' => $this->getEditViewUrl(),
					'linkhref' => true,
					'linkicon' => 'yfi yfi-full-editing-view',
					'linkclass' => 'btn-sm btn-default',
				]);
			}
		}
		return $links;
	}

	/**
	 * Get invitations with CRM metadata.
	 *
	 * @return array
	 */
	public function getInvities()
	{
		return (new \App\Db\Query())
			->select([
				'u_#__activity_invitation.*',
				'u_#__crmentity_label.label',
				'vtiger_crmentity.setype',
				'vtiger_crmentity.deleted'
			])->from('u_#__activity_invitation')
			->leftJoin('u_#__crmentity_label', 'u_#__crmentity_label.crmid = u_#__activity_invitation.crmid')
			->leftJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = u_#__activity_invitation.crmid')
			->where(['activityid' => (int) $this->getId()])
			->all();
	}

	/**
	 * Get invition status.
	 *
	 * @param false|int $status
	 *
	 * @return string
	 */
	public static function getInvitionStatus($status = false)
	{
		$statuses = [0 => 'LBL_NEEDS-ACTION', 1 => 'LBL_ACCEPTED', 2 => 'LBL_DECLINED'];
		return false !== $status ? $statuses[$status] ?? '' : $statuses;
	}

	/**
	 * Get invite user mail data.
	 *
	 * @return array
	 */
	public function getInviteUserMailData()
	{
		return []; // To do
	}

	/**
	 * Gets ICal content.
	 *
	 * @return string
	 */
	public function getICal(): string
	{
		$calendar = \App\Integrations\Dav\Calendar::createEmptyInstance();
		$calendar->loadFromArray($this->getData());
		$calendar->createComponent();
		return $calendar->getVCalendar()->serialize();
	}
}
