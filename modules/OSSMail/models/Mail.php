<?php
/**
 * Mail Scanner bind email action.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Mail Scanner bind email action.
 */
class OSSMail_Mail_Model extends \App\Base
{
	/**
	 * Mail account.
	 *
	 * @var array
	 */
	protected $mailAccount = [];

	/**
	 * Mail folder.
	 *
	 * @var string
	 */
	protected $mailFolder = '';

	/**
	 * Mail crm id.
	 *
	 * @var int|bool
	 */
	protected $mailCrmId = false;

	/**
	 * Action result.
	 *
	 * @var array
	 */
	protected $actionResult = [];

	/**
	 * Set account.
	 *
	 * @param array $account
	 */
	public function setAccount($account)
	{
		$this->mailAccount = $account;
	}

	/**
	 * Set folder.
	 *
	 * @param string $folder
	 */
	public function setFolder($folder)
	{
		$this->mailFolder = $folder;
	}

	/**
	 * Add action result.
	 *
	 * @param string $type
	 * @param string $result
	 */
	public function addActionResult($type, $result)
	{
		$this->actionResult[$type] = $result;
	}

	/**
	 * Get account.
	 *
	 * @return array
	 */
	public function getAccount()
	{
		return $this->mailAccount;
	}

	/**
	 * Get folder.
	 *
	 * @return string
	 */
	public function getFolder()
	{
		return $this->mailFolder;
	}

	/**
	 * Get action result.
	 *
	 * @param string $action
	 *
	 * @return array
	 */
	public function getActionResult($action = false)
	{
		if ($action && isset($this->actionResult[$action])) {
			return $this->actionResult[$action];
		}
		return $this->actionResult;
	}

	/**
	 * Get type email.
	 *
	 * @param bool $returnText
	 *
	 * @return string|int
	 */
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

	/**
	 * Find email user.
	 *
	 * @param string $emails
	 *
	 * @return array
	 */
	public static function findEmailUser($emails)
	{
		$return = [];
		$notFound = 0;
		if (!empty($emails)) {
			foreach (explode(',', $emails) as $email) {
				$result = (new \App\Db\Query())->select(['id'])->from('vtiger_users')->where(['email1' => $email])->scalar();
				if ($result) {
					$return[] = $result;
				} else {
					++$notFound;
				}
			}
		}
		return ['users' => $return, 'notFound' => $notFound];
	}

	/**
	 * Get account owner.
	 *
	 * @return int
	 */
	public function getAccountOwner()
	{
		$account = $this->getAccount();
		if ($account['crm_user_id']) {
			return $account['crm_user_id'];
		}
		return \App\User::getCurrentUserId();
	}

	/**
	 * Generation crm unique id.
	 *
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
	 * Get mail crm id.
	 *
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

	/**
	 * Set mail crm id.
	 *
	 * @param int $id
	 */
	public function setMailCrmId($id)
	{
		$this->mailCrmId = $id;
	}

	/**
	 * Get email.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
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

	/**
	 * Find email address.
	 *
	 * @param string $field
	 * @param string $searchModule
	 * @param bool   $returnArray
	 *
	 * @return string|array
	 */
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
			$emails = (array) $emails;
		}
		if (!empty($emailSearchList)) {
			foreach ($emailSearchList as $field) {
				$enableFind = true;
				$row = explode('=', $field);
				$moduleName = $row[1];
				if ($searchModule && $searchModule !== $moduleName) {
					$enableFind = false;
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
								$dataReader->close();
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
	 * Function to saving attachments.
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
			'modifiedtime' => $useTime,
		];
		if ($attachments = $this->get('attachments')) {
			foreach ($attachments as $attachment) {
				$fileInstance = \App\Fields\File::loadFromContent($attachment['attachment'], $attachment['filename'], ['validateAllCodeInjection' => true]);
				if ($fileInstance && $fileInstance->validate() && ($id = App\Fields\File::saveFromContent($fileInstance, $params))) {
					$files[] = $id;
				} else {
					\App\Log::error("Error downloading the file '{$attachment['filename']}' in mail: {$this->get('date')} | {$this->get('fromaddress')} | {$this->get('subject')}", __CLASS__);
				}
			}
		}
		$db = App\Db::getInstance();
		foreach ($files as $file) {
			$db->createCommand()->insert('vtiger_ossmailview_files', [
				'ossmailviewid' => $this->mailCrmId,
				'documentsid' => $file['crmid'],
				'attachmentsid' => $file['attachmentsId'],
			])->execute();
		}
		return $files;
	}

	/**
	 * Post process function.
	 */
	public function postProcess()
	{
	}
}
