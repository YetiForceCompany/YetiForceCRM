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
	 * @var bool|int
	 */
	protected $mailCrmId = false;

	/**
	 * Action result.
	 *
	 * @var array
	 */
	protected $actionResult = [];
	/**
	 * Mail type.
	 *
	 * @var int
	 */
	protected $mailType;

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
	 * @return int|string
	 */
	public function getTypeEmail($returnText = false)
	{
		if (isset($this->mailType)) {
			if ($returnText) {
				$cacheKey = 'Received';
				switch ($this->mailType) {
					case 0:
						$cacheKey = 'Sent';
						break;
					case 2:
						$cacheKey = 'Internal';
						break;
				}
				return $cacheKey;
			}
			return $this->mailType;
		}
		$account = $this->getAccount();
		$fromEmailUser = $this->findEmailUser($this->get('from_email'));
		$toEmailUser = $this->findEmailUser($this->get('to_email'));
		$ccEmailUser = $this->findEmailUser($this->get('cc_email'));
		$bccEmailUser = $this->findEmailUser($this->get('bcc_email'));
		$existIdentitie = false;
		foreach (OSSMailScanner_Record_Model::getIdentities($account['user_id']) as $identitie) {
			if ($identitie['email'] == $this->get('from_email')) {
				$existIdentitie = true;
			}
		}
		if ($fromEmailUser && ($toEmailUser || $ccEmailUser || $bccEmailUser)) {
			$key = 2;
			$cacheKey = 'Internal';
		} elseif ($existIdentitie || $fromEmailUser) {
			$key = 0;
			$cacheKey = 'Sent';
		} else {
			$key = 1;
			$cacheKey = 'Received';
		}
		$this->mailType = $key;
		if ($returnText) {
			return $cacheKey;
		}
		return $key;
	}

	/**
	 * Find email user.
	 *
	 * @param string $emails
	 *
	 * @return bool
	 */
	public static function findEmailUser($emails)
	{
		$notFound = 0;
		if (!empty($emails)) {
			foreach (explode(',', $emails) as $email) {
				if (!\Users_Module_Model::checkMailExist($email)) {
					++$notFound;
				}
			}
		}
		return 0 === $notFound;
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
		$uid = hash('sha256', $this->get('from_email') . '|' . $this->get('date') . '|' . $this->get('subject') . '|' . $this->get('message_id'));
		$this->set('cid', $uid);
		return $uid;
	}

	/**
	 * Get mail crm id.
	 *
	 * @return bool|int
	 */
	public function getMailCrmId()
	{
		if ($this->mailCrmId) {
			return $this->mailCrmId;
		}
		if (empty($this->get('message_id')) || \Config\Modules\OSSMailScanner::$ONE_MAIL_FOR_MULTIPLE_RECIPIENTS) {
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
	 * @param string $cacheKey
	 *
	 * @return string
	 */
	public function getEmail($cacheKey)
	{
		$header = $this->get('header');
		$text = '';
		if (property_exists($header, $cacheKey)) {
			$text = $header->{$cacheKey};
		}
		$return = '';
		if (\is_array($text)) {
			foreach ($text as $row) {
				if ('' != $return) {
					$return .= ',';
				}
				$return .= $row->mailbox . '@' . $row->host;
			}
		}
		return $return;
	}

	/**
	 * Search crmids by emails.
	 *
	 * @param string   $moduleName
	 * @param string   $fieldName
	 * @param string[] $emails
	 *
	 * @return array crmids
	 */
	public function searchByEmails(string $moduleName, string $fieldName, array $emails)
	{
		$return = [];
		$cacheKey = 'MailSearchByEmails' . $moduleName . '_' . $fieldName;
		foreach ($emails as $email) {
			if (empty($email) || \in_array($email, ['@', 'undisclosed-recipients@', 'undisclosed-recipients'])) {
				continue;
			}
			if (App\Cache::staticHas($cacheKey, $email)) {
				$cache = App\Cache::staticGet($cacheKey, $email);
				if (0 != $cache) {
					$return = array_merge($return, $cache);
				}
			} else {
				$ids = [];
				$queryGenerator = new \App\QueryGenerator($moduleName);
				if ($queryGenerator->getModuleField($fieldName)) {
					$queryGenerator->setFields(['id']);
					$queryGenerator->addCondition($fieldName, $email, 'e');
					$ids = $queryGenerator->createQuery()->column();
					$return = array_merge($return, $ids);
				}
				if (empty($ids)) {
					$ids = 0;
				}
				App\Cache::staticSave($cacheKey, $email, $ids);
			}
		}
		return $return;
	}

	/**
	 * Search crmids from domains.
	 *
	 * @param string   $moduleName
	 * @param string   $fieldName
	 * @param string[] $emails
	 *
	 * @return int[] crmids
	 */
	public function searchByDomains(string $moduleName, string $fieldName, array $emails)
	{
		$cacheKey = 'MailSearchByDomains' . $moduleName . '_' . $fieldName;
		$crmids = [];
		foreach ($emails as $email) {
			if (empty($email) || \in_array($email, ['@', 'undisclosed-recipients@', 'undisclosed-recipients'])) {
				continue;
			}
			$domain = mb_strtolower(explode('@', $email)[1]);
			if (App\Cache::staticHas($cacheKey, $domain)) {
				$cache = App\Cache::staticGet($cacheKey, $domain);
				if (0 != $cache) {
					$crmids = array_merge($crmids, $cache);
				}
			} else {
				$crmids = App\Fields\MultiDomain::findIdByDomain($moduleName, $fieldName, $domain);
				App\Cache::staticSave($cacheKey, $domain, $crmids);
			}
		}
		return $crmids;
	}

	/**
	 * Find email address.
	 *
	 * @param string $field
	 * @param string $searchModule
	 * @param bool   $returnArray
	 *
	 * @return array|string
	 */
	public function findEmailAdress($field, $searchModule = false, $returnArray = true)
	{
		$return = [];
		$emails = $this->get($field);
		if (empty($emails)) {
			return [];
		}
		if (strpos($emails, ',')) {
			$emails = explode(',', $emails);
		} else {
			$emails = (array) $emails;
		}
		$emailSearchList = OSSMailScanner_Record_Model::getEmailSearchList();
		if (!empty($emailSearchList)) {
			foreach ($emailSearchList as $field) {
				$enableFind = true;
				$row = explode('=', $field);
				$moduleName = $row[1];
				$fieldName = $row[0];
				$fieldModel = Vtiger_Field_Model::getInstance($row[0], Vtiger_Module_Model::getInstance($moduleName));
				if ($searchModule && $searchModule !== $moduleName) {
					$enableFind = false;
				}
				if ($enableFind) {
					if (319 === $fieldModel->getUIType()) {
						$return = array_merge($return, $this->searchByDomains($moduleName, $fieldName, $emails));
					} else {
						$return = array_merge($return, $this->searchByEmails($moduleName, $fieldName, $emails));
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
		$useTime = $this->get('date');
		$files = $this->get('files');
		$params = [
			'created_user_id' => $userId,
			'assigned_user_id' => $userId,
			'modifiedby' => $userId,
			'createdtime' => $useTime,
			'modifiedtime' => $useTime,
			'folderid' => 'T2'
		];
		if ($attachments = $this->get('attachments')) {
			$maxSize = \App\Config::main('upload_maxsize');
			foreach ($attachments as $attachment) {
				if ($maxSize < ($size = \strlen($attachment['attachment']))) {
					\App\Log::error("Error - downloaded the file is too big '{$attachment['filename']}', size: {$size}, in mail: {$this->get('date')} | {$this->get('from_email')} | {$this->get('subject')} | folder: {$this->getFolder()} | message_id:{$this->get('message_id')}", __CLASS__);
					continue;
				}
				$fileInstance = \App\Fields\File::loadFromContent($attachment['attachment'], $attachment['filename'], ['validateAllCodeInjection' => true]);
				if ($fileInstance && $fileInstance->validateAndSecure() && ($id = App\Fields\File::saveFromContent($fileInstance, $params))) {
					$files[] = $id;
				} else {
					\App\Log::error("Error downloading the file '{$attachment['filename']}' in mail: {$this->get('date')} | {$this->get('from_email')} | {$this->get('subject')}", __CLASS__);
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
