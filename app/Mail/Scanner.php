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
	/** @var string Logs table name */
	public const LOG_TABLE = 'vtiger_ossmails_logs';

	/** @var \App\Mail\Account */
	private $account;
	/** @var int Limit of scanned e-mails */
	private $limit = 100;
	/** @var int Number of scanned e-mails */
	private $count = 0;
	/** @var ScannerLog Scanner log */
	private $log;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->log = new ScannerLog();
	}

	/**
	 * Set limit.
	 *
	 * @param int $limit
	 *
	 * @return $this
	 */
	public function setLimit(int $limit)
	{
		$this->limit = $limit;
		return $this;
	}

	/**
	 * Set Mail Account.
	 *
	 * @param Account $account
	 *
	 * @return $this
	 */
	public function setAccount(Account $account)
	{
		$this->account = $account;
		return $this;
	}

	/**
	 * Run scanner.
	 *
	 * @param callable|null $callback Conditions for scanning subsequent emails
	 *
	 * @return void
	 */
	public function run(?callable $callback = null)
	{
		$folders = $this->account->getFolders();
		$actions = $this->account->getActions();
		if ($folders && $actions && ($imap = $this->getImapConnection()) && ($folders = array_intersect($folders, array_keys($imap->getFolders(false))))) {
			$flagSeen = \App\Mail::getConfig('scanner', 'flag_seen');
			foreach ($folders as $folderName) {
				$this->log->start();
				$uid = $this->account->getLastUid($folderName);
				if (!\is_int($uid)) {
					continue;
				}

				$messageCollection = $imap->getMessagesGreaterThanUid($folderName, $uid, $this->limit);
				foreach ($messageCollection as $message) {
					if (($callback && $callback($this)) || !$this->log->isRunning()) {
						return;
					}
					--$this->limit;
					$messageObject = (new Message\Imap())->setMessage($message);
					try {
						foreach ($actions as $action) {
							$this->getAction($action)->setAccount($this->account)->setMessage($messageObject)->process();
						}
						++$this->count;
						$this->account->setLastUid($messageObject->getMsgUid(), $folderName);
						$this->log->updateCount($this->count);
						if ($flagSeen) {
							$messageObject->setFlag('Seen');
						}
					} catch (\Throwable $th) {
						$message = $th->getMessage();
						\App\Log::error("Mail Scanner - Account: {$this->account->getSource()->getId()}, folder: {$folderName}, UID: {$messageObject->getMsgUid()}, message: " . $message);
						if ($th instanceof \App\Exceptions\AppException) {
							$message = $th->getDisplayMessage();
						}
						$this->log->close(ScannerLog::STATUS_ERROR, $message, $action);
						break;
					}
				}
			}
			$this->log->close();
		}
	}

	/**
	 * Get imap connection.
	 *
	 * @return Connections\Imap|null
	 */
	public function getImapConnection(): ?Connections\Imap
	{
		try {
			$imap = $this->account->openImap();
		} catch (\Throwable $th) {
			$this->account->lock($th->getMessage());
			$imap = null;
		}

		return $imap;
	}

	/**
	 * Get action object.
	 *
	 * @param string $name
	 *
	 * @return ScannerAction\Base
	 */
	public function getAction(string $name): ScannerAction\Base
	{
		$class = "App\\Mail\\ScannerAction\\{$name}";
		return new $class();
	}

	/**
	 * Check if ready.
	 *
	 * @return bool
	 */
	public function isReady(): bool
	{
		return ScannerLog::isScannRunning();
	}
}
