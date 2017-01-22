<?php

/**
 * Notification Record Model
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Notification_Record_Model extends Vtiger_Record_Model
{

	/**
	 * Function to parse content
	 * @param string $fieldName
	 * @return string
	 */
	public function getParseField($fieldName)
	{
		$relatedRecords = $this->getRelatedRecord();
		$relatedModule = $relatedRecords['module'];
		$relatedId = $relatedRecords['id'];
		$value = $this->get($fieldName);
		if (\App\Record::isExists($relatedId)) {
			$textParser = \App\TextParser::getInstanceById($relatedId, $relatedModule);
			$textParser->setContent($value)->parse();
		} else {
			$textParser = \App\TextParser::getInstance();
			$textParser->setContent($value)->parseTranslations();
		}
		return $textParser->getContent();
	}

	public function getTitle()
	{
		return $this->getParseField('title');
	}

	public function getName()
	{
		return $this->getParseField('title');
	}

	/**
	 * Function to get id
	 * @return type
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Return name type notification
	 * @return string
	 */
	public function getTypeName()
	{
		return $this->get('notification_type');
	}

	/**
	 * Return message of notification
	 * @return string
	 */
	public function getMessage()
	{
		return $this->getParseField('description');
	}

	/**
	 * Function to set notification as read
	 */
	public function setMarked()
	{
		$this->set('notification_status', 'PLL_READ');
		$this->save();
	}

	/**
	 * Function to get the most important records
	 * @return array
	 */
	public function getRelatedRecord()
	{
		$relatedId = false;
		$subprocess = $this->get('subprocess');
		$process = $this->get('process');
		$link = $this->get('link');
		if (!empty($subprocess)) {
			$relatedId = $subprocess;
		} else {
			if (!empty($process)) {
				$relatedId = $process;
			} else {
				if (empty($link)) {
					return false;
				} else {
					$relatedId = $link;
				}
			}
		}
		$relatedModule = \vtlib\Functions::getCRMRecordMetadata($relatedId);
		$relatedModule = $relatedModule['setype'];
		return ['id' => $relatedId, 'module' => $relatedModule];
	}

	/**
	 * Function to get name of user who create this notification
	 */
	public function getCreatorUser()
	{
		$userid = $this->get('smcreatorid');
		if (!empty($userid)) {
			return \App\Fields\Owner::getLabel($userid);
		}
		return '';
	}
	/*
	 * Function to save record
	 */

	public function save()
	{
		$relatedRecord = $this->getRelatedRecord();
		if ($relatedRecord !== false) {
			$relatedId = $relatedRecord['id'];
			$relatedModule = $relatedRecord['module'];
		}
		$notificationType = $this->get('notification_type');
		if (!Users_Privileges_Model::isPermitted('Notification', 'DetailView')) {
			\App\Log::warning('User ' . vtlib\Functions::getOwnerRecordLabel($this->get('assigned_user_id')) . ' has no active notifications');
			\App\Log::trace('Exiting ' . __METHOD__ . ' - return true');
			return false;
		}
		if ($notificationType !== 'PLL_USERS' && !Users_Privileges_Model::isPermitted($relatedModule, 'DetailView', $relatedId)) {
			\App\Log::error('User ' . vtlib\Functions::getOwnerRecordLabel($this->get('assigned_user_id')) .
				' does not have permission for this record ' . $relatedId);
			\App\Log::trace('Exiting ' . __METHOD__ . ' - return true');
			return false;
		}
		if ($notificationType !== 'PLL_USERS' && \App\Record::isExists($relatedId)) {
			$textParser = \App\TextParser::getInstanceById($relatedId, $relatedModule);
			$this->set('description', $textParser->withoutTranslations()->setContent($this->get('description'))->parse()->getContent());
			$this->set('title', $textParser->setContent($this->get('title'))->parse()->getContent());
		}
		$users = $this->get('shownerid');
		$usersCollection = $this->isEmpty('assigned_user_id') ? [] : [$this->get('assigned_user_id')];
		if (!empty($users)) {
			foreach (explode(',', $users) as $userId) {
				$userType = \App\Fields\Owner::getType($userId);
				if ($userType === 'Groups') {
					$usersCollection = array_merge($usersCollection, \App\PrivilegeUtil::getUsersByGroup($userId));
				} else {
					$usersCollection [] = $userId;
				}
			}
			$this->set('shownerid', null);
		}
		$usersCollection = array_unique($usersCollection);
		foreach ($usersCollection as $userId) {
			if ($relatedId && $notificationType === 'PLL_SYSTEM' && !\App\Privilege::isPermitted($relatedModule, 'DetailView', $relatedId, $userId)) {
				continue;
			}
			$this->set('assigned_user_id', $userId);
			parent::save();
		}
	}

	/**
	 * Function to get icon for notification
	 * @return array params icon
	 */
	public function getIcon()
	{
		$icon = false;
		switch ($this->get('notification_type')) {
			case 'PLL_USERS':
				$userModel = Users_Privileges_Model::getInstanceById($this->get('smcreatorid'));
				$icon = [
					'type' => 'image',
					'title' => $userModel->getName(),
					'src' => $userModel->getImagePath(),
					'class' => 'userImage',
				];
				break;
			default:
				$relatedRecord = $this->getRelatedRecord();
				$icon = [
					'type' => 'icon',
					'title' => vtranslate($relatedRecord['module'], $relatedRecord['module']),
					'class' => 'userIcon-' . $relatedRecord['module'],
				];
				break;
		}
		return $icon;
	}

	/**
	 * Function to get the list view actions for the record
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
				'linkicon' => 'glyphicon glyphicon-ok',
				'linkclass' => 'btn-sm btn-default'
			];
		}
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
	}
}
