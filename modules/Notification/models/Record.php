<?php

/**
 * Notification Record Model.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Notification_Record_Model extends Vtiger_Record_Model
{
	/**
	 * Function to parse content.
	 *
	 * @param string $fieldName
	 *
	 * @return string
	 */
	public function getParseField($fieldName)
	{
		$relatedRecords = $this->getRelatedRecord();
		$value = $this->get($fieldName);
		if (empty($relatedRecords['id'])) {
			return $value;
		}
		$relatedModule = $relatedRecords['module'];
		$relatedId = $relatedRecords['id'];
		if (\App\Record::isExists($relatedId)) {
			$textParser = \App\TextParser::getInstanceById($relatedId, $relatedModule);
			$textParser->setContent($value)->parse();
		} else {
			$textParser = \App\TextParser::getInstance();
			$textParser->setContent($value)->parseTranslations();
		}

		return nl2br(str_replace("<br>\n", '<br>', $textParser->getContent()));
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return $this->getDisplayValue('title', $this->getId(), $this);
	}

	/**
	 * Return name type notification.
	 *
	 * @return string
	 */
	public function getTypeName()
	{
		return $this->get('notification_type');
	}

	/**
	 * Return message of notification.
	 *
	 * @return string
	 */
	public function getMessage(): string
	{
		return \App\Utils\Completions::decode($this->getDisplayValue('description', $this->getId(), $this));
	}

	/**
	 * Function to set notification as read.
	 */
	public function setMarked()
	{
		$this->set('notification_status', 'PLL_READ');
		$this->save();
	}

	/**
	 * Function to get the most important records.
	 *
	 * @return array
	 */
	public function getRelatedRecord()
	{
		$relatedId = false;
		$subprocess = $this->get('subprocess');
		$process = $this->get('process');
		$link = $this->get('link');
		$linkextend = $this->get('linkextend');
		$sl = $this->get('subprocess_sl');
		if (!empty($sl) && \App\Record::isExists($sl)) {
			$relatedId = $sl;
		} elseif (!empty($subprocess) && \App\Record::isExists($subprocess)) {
			$relatedId = $subprocess;
		} elseif (!empty($process) && \App\Record::isExists($process)) {
			$relatedId = $process;
		} elseif (!empty($link) && \App\Record::isExists($link)) {
			$relatedId = $link;
		} elseif (!empty($linkextend) && \App\Record::isExists($linkextend)) {
			$relatedId = $linkextend;
		} else {
			return false;
		}
		return ['id' => $relatedId, 'module' => \vtlib\Functions::getCRMRecordMetadata($relatedId)['setype']];
	}

	/**
	 * Function to get name of user who create this notification.
	 */
	public function getCreatorUser()
	{
		$userid = $this->get('smcreatorid');
		if (!empty($userid)) {
			return \App\Fields\Owner::getLabel($userid);
		}
		return '';
	}

	/**
	 * Function to save record.
	 *
	 * @throws \Exception
	 *
	 * @return bool
	 */
	public function save(): bool
	{
		$relatedRecord = $this->getRelatedRecord();
		$relatedId = $relatedModule = false;
		if (false !== $relatedRecord) {
			$relatedId = $relatedRecord['id'];
			$relatedModule = $relatedRecord['module'];
		}
		$notificationType = $this->get('notification_type');
		if (!\App\Privilege::isPermitted('Notification', 'DetailView')) {
			\App\Log::warning('User ' . \App\Fields\Owner::getLabel($this->get('assigned_user_id')) . ' has no active notifications');
			\App\Log::trace('Exiting ' . __METHOD__ . ' - return true');
			return false;
		}
		if ($relatedModule && 'PLL_USERS' !== $notificationType && !\App\Privilege::isPermitted($relatedModule, 'DetailView', $relatedId)) {
			\App\Log::error('User ' . \App\Fields\Owner::getLabel($this->get('assigned_user_id')) .
				' does not have permission for this record ' . $relatedId);
			\App\Log::trace('Exiting ' . __METHOD__ . ' - return true');
			return false;
		}
		if ($relatedModule && 'PLL_USERS' !== $notificationType && \App\Record::isExists($relatedId)) {
			$textParser = \App\TextParser::getInstanceById($relatedId, $relatedModule);
			$this->setFromUserValue('description', $textParser->withoutTranslations()->setContent($this->get('description'))->parse()->getContent());
			$this->setFromUserValue('title', \App\TextUtils::textTruncate(\App\Purifier::purifyByType($textParser->setContent($this->get('title'))->parse()->getContent(), 'Text'), $this->getField('title')->getMaxValue(), false));
		}
		$users = $this->get('shownerid');
		$usersCollection = $this->isEmpty('assigned_user_id') ? [] : [$this->get('assigned_user_id')];
		if (!empty($users)) {
			$users = \is_array($users) ? $users : explode(',', $users);
			foreach ($users as $userId) {
				$userType = \App\Fields\Owner::getType($userId);
				if ('Groups' === $userType) {
					$usersCollection = array_merge($usersCollection, \App\PrivilegeUtil::getUsersByGroup($userId));
				} else {
					$usersCollection[] = $userId;
				}
			}
			$this->set('shownerid', null);
		}
		$usersCollection = array_unique($usersCollection);
		$isNew = $this->isNew;
		foreach ($usersCollection as $userId) {
			if ($isNew && $relatedId && 'PLL_SYSTEM' === $notificationType && !\App\Privilege::isPermitted($relatedModule, 'DetailView', $relatedId, $userId)) {
				continue;
			}
			$this->set('assigned_user_id', $userId);
			if ($isNew) {
				$this->isNew = true;
			}
			parent::save();
		}
		return true;
	}

	/**
	 * Function to get icon for notification.
	 *
	 * @return array params icon
	 */
	public function getIcon()
	{
		$icon = [];
		if ('PLL_USERS' === $this->get('notification_type')) {
			$icon = \App\User::getUserModel($this->get('smcreatorid'))->getImage();
		} else {
			$icon = [
				'icon' => 'fas fa-hdd',
			];
		}
		return $icon;
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
		if ($this->getModule()->isPermitted('EditView') && $this->isEditable()) {
			$recordLinks[] = [
				'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
				'linklabel' => 'LBL_MARK_AS_READ',
				'linkurl' => 'javascript:Notification_List_Js.setAsMarked(' . $this->getId() . ')',
				'linkicon' => 'fas fa-check',
				'linkclass' => 'btn-sm btn-default',
			];
		}
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
	}
}
