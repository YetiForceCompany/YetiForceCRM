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
			$textParser = Vtiger_TextParser_Helper::getInstanceById($relatedId, $relatedModule);
			$textParser->setContent($value);
			$value = $textParser->parse();
			return $value;
		} else {
			$textParser = Vtiger_TextParser_Helper::getCleanInstance();
			$textParser->setContent($value);
			$value = $textParser->parseTranslations();
		}
		return $value;
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
			$userModel = Users_Privileges_Model::getInstanceById($userid);
			return $userModel->getName();
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
			\App\Log::trace('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' - return true');
			return false;
		}
		if ($notificationType != 'PLL_USERS' && !Users_Privileges_Model::isPermitted($relatedModule, 'DetailView', $relatedId)) {
			\App\Log::error('User ' . vtlib\Functions::getOwnerRecordLabel($this->get('assigned_user_id')) .
				' does not have permission for this record ' . $relatedId);
			\App\Log::trace('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' - return true');
			return false;
		}
		if ($notificationType != 'PLL_USERS' && \App\Record::isExists($relatedId)) {
			$message = $this->get('description');
			$textParser = Vtiger_TextParser_Helper::getInstanceById($relatedId, $relatedModule);
			$textParser->set('withoutTranslations', true);
			$textParser->setContent($message);
			$message = $textParser->parse();
			$this->set('description', $message);
			$title = $this->get('title');
			$textParser->setContent($title);
			$title = $textParser->parse();
			$this->set('title', $title);
		}
		$users = $this->get('shownerid');
		$usersCollection = $this->isEmpty('assigned_user_id') ? [] : [$this->get('assigned_user_id')];
		if (!empty($users)) {
			foreach ($users as $userId) {
				$userType = \includes\fields\Owner::getType($userId);
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
}
