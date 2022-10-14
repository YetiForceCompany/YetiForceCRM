<?php
/**
 * Mail scanner file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Mail;

/**
 * Mail scanner class.
 */
class Scanner extends \App\Base
{
	/** @var string Base module name */
	public const MODULE_NAME = 'MailAccount';
	/** @var string Name of the scanned folder table */
	public const FOLDER_TABLE = 'u_#__mailscanner_folders';
	/** @var \App\Mail\Account */
	private $account;
	/** @var int Limit of scanned e-mails */
	private $limit = 0;

	public function setLimit(int $limit)
	{
		$this->limit = $limit;
		return $this;
	}

	public function setAccount(Account $account)
	{
		$this->account = $account;
		return $this;
	}

	public function run(?callable $callback = null)
	{
		$folders = $this->account->getSource()->get('folders');
		$actions = $this->account->getSource()->get('scanner_actions');
		if (!\App\Json::isEmpty($folders) && $actions) {
			$imap = $this->account->openImap();
			$folders = \App\Json::decode($folders);
			if (\count($folders) > 1) {
				$folders = array_intersect($folders, array_keys($imap->getFolders(false)));
			}
			foreach ($folders as $folderName) {
				$uid = $this->account->getLastUid($folderName);
				if (!\is_int($uid)) {
					continue;
				}
				$messageCollection = $imap->getMessagesGreaterThanUid($folderName, $uid, $this->limit);
				foreach ($messageCollection as $message) {
					if ($callback && $callback($this)) {
						return;
					}
					--$this->limit;
					$messageObject = (new Message\Imap())->setMessage($message);
					foreach (explode(',', $actions) as $action) {
						$scannerAction = $this->getAction($action);
						$scannerAction->setAccount($this->account)
							->setMessage($messageObject)
							->process();
					}
					$this->account->setLastUid($messageObject->getMsgUid(), $folderName);
				}
			}
		}
	}

	public function getAction(string $name): ScannerAction\Base
	{
		$class = "App\\Mail\\ScannerAction\\{$name}";
		return new $class();
	}
}
