<?php

/**
 * Mail Scanner bind email action 
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMail_Mail_Model extends \App\Base
{

	protected $mailAccount = [];
	protected $mailFolder = '';
	protected $mailCrmId = false;
	protected $actionResult = [];

	public function setAccount($account)
	{
		$this->mailAccount = $account;
	}

	public function setFolder($folder)
	{
		$this->mailFolder = $folder;
	}

	public function addActionResult($type, $result)
	{
		$this->actionResult[$type] = $result;
	}

	public function getAccount()
	{
		return $this->mailAccount;
	}

	public function getFolder()
	{
		return $this->mailFolder;
	}

	public function getActionResult($action = false)
	{
		if ($action && isset($this->actionResult[$action])) {
			return $this->actionResult[$action];
		}
		return $this->actionResult;
	}

	public function getTypeEmail($returnText = false)
	{
		$account = $this->getAccount();
		$fromEmailUser = $this->findEmailUser($this->get('fromaddress'));
		$toEmailUser = $this->findEmailUser($this->get('toaddress'));
		$ccEmailUser = $this->findEmailUser($this->get('ccaddress'));
		$bccEmailUser = $this->findEmailUser($this->get('bccaddress'));
		$notFound = $toEmailUser['notFound'] + $ccEmailUser['notFound'] + $bccEmailUser['notFound'];
		$identities = OSSMailScanner_Record_Model::getIdentities($account['user_id']);
		$type = false;
		foreach ($identities as $identitie) {
			if ($identitie['email'] == $this->get('fromaddress')) {
				$type = true;
			}
		}
		if ($fromEmailUser['notFound'] == 0 && $notFound == 0) {
			$key = 2;
			$name = 'Internal';
		} elseif ($type) {
			$key = 0;
			$name = 'Sent';
		} else {
			$key = 1;
			$name = 'Received';
		}
		if ($returnText) {
			return $name;
		} else {
			return $key;
		}
	}

	public static function findEmailUser($emails)
	{
		$db = PearDatabase::getInstance();
		$return = [];
		$notFound = 0;
		if (!empty($emails)) {
			foreach (explode(',', $emails) as $email) {
				$result = $db->pquery('SELECT id FROM vtiger_users WHERE email1 = ?', [$email]);
				if ($db->getRowCount($result) > 0) {
					$return[] = $db->getSingleValue($result);
				} else {
					$notFound++;
				}
			}
		}
		return ['users' => $return, 'notFound' => $notFound];
	}

	public function getAccountOwner()
	{
		$account = $this->getAccount();
		if ($account['crm_user_id']) {
			return $account['crm_user_id'];
		}
		return \App\User::getCurrentUserId();
	}

	/**
	 * Generation crm unique id
	 * @return string
	 */
	public function getUniqueId()
	{
		if ($this->has('cid')) {
			return $this->get('cid');
		}
		$uid = sha1($this->get('fromaddress') . '|' . $this->get('date') . '|' . $this->get('subject') . '|' . $this->get('body'));
		$this->set('cid', $uid);
		return $uid;
	}

	/**
	 * Get mail crm id 
	 * @return int|bool
	 */
	public function getMailCrmId()
	{
		if ($this->mailCrmId) {
			return $this->mailCrmId;
		}
		if (empty($this->get('message_id')) || AppConfig::module('OSSMailScanner', 'ONE_MAIL_FOR_MULTIPLE_RECIPIENTS')) {
			$query = (new \App\Db\Query())->select(['ossmailviewid'])->from('vtiger_ossmailview')->where(['cid' => $this->getUniqueId()])->limit(1);
		} else {
			$query = (new \App\Db\Query())->select(['ossmailviewid'])->from('vtiger_ossmailview')->where(['uid' => $this->get('message_id'), 'rc_user' => $this->getAccountOwner()])->limit(1);
		}
		return $this->mailCrmId = $query->scalar();
	}

	public function setMailCrmId($id)
	{
		$this->mailCrmId = $id;
	}

	public function getEmail($name)
	{
		$header = $this->get('header');
		$text = '';
		if (property_exists($header, $name)) {
			$text = $header->$name;
		}
		$return = '';
		if (is_array($text)) {
			foreach ($text as $row) {
				if ($return != '') {
					$return .= ',';
				}
				$return .= $row->mailbox . '@' . $row->host;
			}
		}
		return $return;
	}

	public function findEmailAdress($field, $searchModule = false, $returnArray = true)
	{
		$return = [];
		$emails = $this->get($field);
		$emailSearchList = OSSMailScanner_Record_Model::getEmailSearchList();

		if (empty($emails)) {
			return [];
		} elseif (strpos($emails, ',')) {
			$emails = explode(',', $emails);
		} else {
			settype($emails, 'array');
		}
		if (!empty($emailSearchList)) {
			foreach ($emailSearchList as $field) {
				$enableFind = true;
				$row = explode('=', $field);
				$moduleName = $row[1];
				if ($searchModule) {
					if ($searchModule !== $moduleName) {
						$enableFind = false;
					}
				}

				if ($enableFind) {
					foreach ($emails as $email) {
						if (empty($email)) {
							continue;
						}
						$name = 'MSFindEmail_' . $moduleName . '_' . $row[0];
						$cache = Vtiger_Cache::get($name, $email);
						if ($cache !== false) {
							if ($cache != 0) {
								$return = array_merge($return, $cache);
							}
						} else {
							$ids = [];
							$queryGenerator = new \App\QueryGenerator($moduleName);
							if ($queryGenerator->getModuleField($row[0])) {
								$queryGenerator->setFields(['id']);
								$queryGenerator->addCondition($row[0], $email, 'e');
								$dataReader = $queryGenerator->createQuery()->createCommand()->query();
								while (($crmid = $dataReader->readColumn(0)) !== false) {
									$ids[] = $crmid;
								}
								$return = array_merge($return, $ids);
							}
							if (empty($ids)) {
								$ids = 0;
							}
							Vtiger_Cache::set($name, $email, $ids);
						}
					}
				}
			}
		}
		if (!$returnArray) {
			return implode(',', $return);
		}
		return $return;
	}

	/**
	 * Function to saving attachments
	 */
	public function saveAttachments()
	{
		$userId = $this->getAccountOwner();
		$useTime = $this->get('udate_formated');
		$files = $this->get('files');
		$params = [
			'created_user_id' => $userId,
			'assigned_user_id' => $userId,
			'modifiedby' => $userId,
			'createdtime' => $useTime,
			'modifiedtime' => $useTime
		];
		if ($attachments = $this->get('attachments')) {
			foreach ($attachments as $attachment) {
				if ($id = App\Fields\File::saveFromContent($attachment['attachment'], $attachment['filename'], false, $params)) {
					$files[] = $id;
				}
			}
		}
		$db = App\Db::getInstance();
		foreach ($files as $file) {
			$db->createCommand()->insert('vtiger_ossmailview_files', [
				'ossmailviewid' => $this->mailCrmId,
				'documentsid' => $file['crmid'],
				'attachmentsid' => $file['attachmentsId']
			])->execute();
		}
	}
}
