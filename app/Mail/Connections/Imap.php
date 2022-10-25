<?php
/**
 * Mail imap file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Mail\Connections;

use App\Mail\Messages;

/**
 * Mail imap class.
 */
class Imap
{
	/** @var string Server hostname. */
	protected $host;
	/** @var int Server port. */
	protected $port;
	/** @var string Server encryption. Supported: none, ssl, tls, starttls or notls. */
	protected $encryption;
	/** @var string Server protocol. */
	protected $protocol = 'imap';
	/** @var bool Validate cert. */
	protected $validateCert = true;
	/** @var int Connection timeout. */
	protected $timeout = 15;
	/** @var string Account username. */
	protected $username;
	/** @var string Account password. */
	protected $password;
	/**
	 * Account authentication method.
	 *
	 * @var string|null
	 *
	 * @example oauth, null
	 */
	protected $authentication;

	/** Client */
	private $client;

	/** @var int */
	private $attempt = 0;
	/** @var bool Debug */
	protected $debug = false;

	/**
	 * Constructor.
	 *
	 * @param array $options
	 */
	public function __construct(array $options)
	{
		$reflect = new \ReflectionClass($this);
		foreach ($options as $name => $value) {
			if ($reflect->hasProperty($name) && !$reflect->getProperty($name)->isPrivate()) {
				$this->{$name} = $value;
			}
		}
	}

	/**
	 * Connect to server.
	 *
	 * @return $this
	 */
	public function connect()
	{
		if (!$this->client || !$this->client->isConnected()) {
			$this->client = (new \Webklex\PHPIMAP\ClientManager(['options' => ['debug' => $this->debug]]))->make([
				'host' => $this->host,
				'port' => $this->port,
				'encryption' => $this->encryption, //'ssl',
				'validate_cert' => $this->validateCert,
				'protocol' => $this->protocol,
				'authentication' => $this->authentication,
				'username' => $this->username,
				'password' => $this->password,
				'timeout' => $this->timeout
			]);
			++$this->attempt;
			$this->client->connect();
		}

		return $this;
	}

	public function isConnected()
	{
		return $this->client && $this->client->isConnected();
	}

	/**
	 * Disconnect from server.
	 *
	 * @return $this
	 */
	public function disconnect()
	{
		if ($this->client && $this->client->isConnected()) {
			$this->client->disconnect();
		}

		return $this;
	}

	/**
	 * Get folders list.
	 * If hierarchical order is set to true, it will make a tree of folders, otherwise it will return flat array.
	 *
	 * @param bool        $hierarchical
	 * @param string|null $parentFolder
	 */
	public function getFolders(bool $hierarchical = true, ?string $parentFolder = null)
	{
		$this->connect();
		$folders = [];
		foreach ($this->client->getFolders($hierarchical, $parentFolder) as $folder) {
			$folders = $this->getChildrenFolders($folder, $folders);
		}

		return $folders;
	}

	public function getFolderByName(string $name)
	{
		$this->connect();
		$imapFolderName = \App\Utils::convertCharacterEncoding($name, 'UTF-8', 'UTF7-IMAP');

		return $this->client->getFolderByPath($imapFolderName);
	}

	/**
	 * Get all messages with an uid greater than a given UID.
	 *
	 * @param int    $uid
	 * @param string $folderName
	 * @param int    $limit
	 *
	 * @return MessageCollection
	 */
	public function getMessagesGreaterThanUid(string $folderName, int $uid, int $limit)
	{
		return $this->getFolderByName($folderName)->query()->limit($limit)->getByUidGreater($uid);
	}

	public function getLastMessages(int $limit = 5, string $folderName = 'INBOX')
	{
		$folder = $this->getFolderByName($folderName);
		$messages = [];
		if ($folder) {
			foreach ($folder->query()->setFetchOrder('desc')->limit($limit)->all()->get()->reverse() as $message) {
				$messages[$message->getUid()] = (new \App\Mail\Message\Imap())->setMessage($message);
			}
		}

		return $messages;
	}

	/**
	 * Get children folders.
	 *
	 * @param object $folder
	 * @param array  $folders
	 *
	 * @return array
	 */
	private function getChildrenFolders($folder, array &$folders): array
	{
		$folders[$folder->full_name]['name'] = $folder->name;
		$folders[$folder->full_name]['fullName'] = $folder->full_name;
		if ($folder->hasChildren()) {
			foreach ($folder->children as $subFolder) {
				$children = [];
				$folders[$folder->full_name]['children'] = $this->getChildrenFolders($subFolder, $children);
			}
		}

		return $folders;
	}

	public function appendMessage(string $folderName, $message): bool
	{
		$this->connect();
		$folder = $this->client->getFolder($folderName);
		if (!$folder) {
			throw new \App\Exceptions\AppException('ERR_IMAP_FOLDER_NOT_EXISTS||' . $folderName);
		}

		return $folder->appendMessage($message);
	}
}
