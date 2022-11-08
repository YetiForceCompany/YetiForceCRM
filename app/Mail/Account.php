<?php
/**
 * Mail account file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Mail;

/**
 * Mail account class.
 */
class Account extends \App\Base
{
	/** @var string Mailbox Status: Active */
	public const STATUS_ACTIVE = 'PLL_ACTIVE';
	/** @var string Mailbox Status: Inactive */
	public const STATUS_INACTIVE = 'PL_INACTIVE';
	/** @var string Mailbox Status: Locked */
	public const STATUS_LOCKED = 'PLL_LOCKED';

	/** @var string Base module name */
	public const MODULE_NAME = 'MailAccount';
	/** @var \App\Mail\Server */
	private $server;
	/** @var \Vtiger_Record_Model */
	private $source;
	/** @var \App\Integrations\OAuth\AbstractProvider OAuth2 provider */
	private $provider;
	/** @var string */
	private $password;
	/** @var string */
	private $userName;
	/** @var string */
	private $refreshToken;
	/** @var string Date time */
	private $expireTime;
	/** @var string */
	private $redirectUri;
	/**
	 * List of scopes that will be used for authentication.
	 *
	 * @var array
	 */
	protected $scopes;

	/** @var int */
	private $attempt = 0;

	/**
	 * Get instance by ID.
	 *
	 * @param int $id
	 */
	public static function getInstanceById(int $id): ?self
	{
		$instance = null;
		if (\App\Record::isExists($id, self::MODULE_NAME, \App\Record::STATE_ACTIVE)) {
			$instance = new static();
			$instance->source = \Vtiger_Record_Model::getInstanceById($id, self::MODULE_NAME);
			$instance->userName = $instance->source->get('login');
			$instance->password = \App\Encryption::getInstance(\App\Module::getModuleId(self::MODULE_NAME))->decrypt($instance->source->get('password'));
			$instance->refreshToken = \App\Encryption::getInstance(\App\Module::getModuleId(self::MODULE_NAME))->decrypt($instance->source->get('refresh_token'));
			$instance->server = \App\Mail\Server::getInstanceById($instance->source->get('mail_server_id'));
			$instance->redirectUri = $instance->server->getRedirectUri();
		}

		return $instance;
	}

	public function getSource()
	{
		return $this->source;
	}

	public function getPassword()
	{
		return $this->password;
	}

	public function getLogin()
	{
		return $this->userName;
	}

	/**
	 * Mail server instance.
	 *
	 * @return \App\Mail\Server
	 */
	public function getServer(): Server
	{
		return $this->server;
	}

	public function getRefreshToken()
	{
		return $this->refreshToken;
	}

	public function update(array $fields = [])
	{
		if (!$fields) {
			$fields = ['password', 'refresh_token', 'expire_time'];
		}
		foreach ($fields as $fieldName) {
			$fieldModel = $this->source->getField($fieldName);
			$value = null;
			switch ($fieldName) {
				case 'password':
					$value = $fieldModel->getDBValue($this->password);
					break;
				case 'refresh_token':
					if (!$this->refreshToken) {
						break;
					}
					$value = $fieldModel->getDBValue($this->refreshToken);
					break;
				case 'expire_time':
					if (!$this->expireTime) {
						break;
					}
					$value = $this->expireTime;
					break;
				case 'last_login':
					$value = date('Y-m-d H:i:s');
					break;
				default:
					break;
			}
			if (null !== $value) {
				$this->source->set($fieldModel->getName(), $value)->setDataForSave([$fieldModel->getTableName() => [$fieldModel->getColumnName() => $value]]);
			}
		}
		if ($this->source->getPreviousValue()) {
			$this->source->save();
		}
	}

	/**
	 * Requests an access token using a specified option set.
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function getAccessToken(array $options = [])
	{
		$provider = $this->getOAuthProvider();
		if (isset($options['code'])) {
			$provider->getAccessToken('authorization_code', $options);
		} elseif ($this->refreshToken) {
			$provider->setData(['refreshToken' => $this->refreshToken]);
			$provider->refreshToken();
		}

		if ($provider->getToken()) {
			$this->password = $provider->getToken();
			if ($provider->getRefreshToken()) {
				$this->refreshToken = $provider->getRefreshToken();
			}
			$this->expireTime = date('Y-m-d H:i:s', $provider->getExpires());
		}

		return $provider->getToken();
	}

	/**
	 * Get OAuth provider.
	 *
	 * @return \App\Integrations\OAuth\AbstractProvider
	 */
	public function getOAuthProvider(): \App\Integrations\OAuth\AbstractProvider
	{
		if (!$this->provider) {
			$this->provider = \App\Integrations\OAuth::getProviderByName($this->getServer()->get('oauth_provider'));
			$this->provider->setData([
				'clientId' => $this->getServer()->get('client_id'),
				'clientSecret' => $this->getServer()->getClientSecret(),
				'redirectUri' => $this->redirectUri,
				'scopes' => $this->scopes ?: $this->provider->getScopesByAction(self::MODULE_NAME)
			]);
		}

		return $this->provider;
	}

	/**
	 * Check if mail account is active.
	 *
	 * @return bool
	 */
	public function isActive(): bool
	{
		return 'PLL_ACTIVE' === $this->getSource()->get('mailaccount_status');
	}

	/**
	 * Open imap connection.
	 *
	 * @return Connections\Imap
	 */
	public function openImap(): Connections\Imap
	{
		$imap = new Connections\Imap([
			'host' => $this->getServer()->get('imap_host'),
			'port' => $this->getServer()->get('imap_port'),
			'encryption' => $this->getServer()->get('imap_encrypt'), //'ssl',
			'validate_cert' => (bool) $this->getServer()->get('validate_cert'),
			'authentication' => 'oauth2' === $this->getServer()->get('auth_method') ? 'oauth' : null,
			'username' => $this->userName,
			'password' => $this->password
		]);
		try {
			++$this->attempt;
			$imap->connect();
			$this->update(['last_login']);
		} catch (\Throwable $th) {
			// try only once if token has expired
			if (1 === $this->attempt && 'oauth2' === $this->server->get('auth_method')) {
				$this->getAccessToken();
				$this->update();
				return $this->openImap();
			}
			\App\Log::error("IMAP connect - Account: {$this->getSource()->getId()}, message: " . $th->getMessage());
			throw $th;
		}

		return $imap;
	}

	/**
	 * Lock mail account.
	 *
	 * @param string $messages
	 *
	 * @return $this
	 */
	public function lock(string $messages)
	{
		$fieldModel = $this->source->getField('logs');

		$this->source->set('mailaccount_status', self::STATUS_LOCKED);
		$messages = \App\Purifier::decodeHtml(\App\Purifier::encodeHtml($messages));
		$this->source->set('logs', \App\TextUtils::textTruncate($messages, $fieldModel->getMaxValue(), true, true))->save();

		return $this;
	}

	/**
	 * Unlock mail account.
	 *
	 * @return $this
	 */
	public function unlock()
	{
		$this->source->set('mailaccount_status', self::STATUS_ACTIVE);
		$this->source->set('logs', '')->save();

		return $this;
	}

	/**
	 * Deactiveate mail account.
	 *
	 * @param string|null $messages
	 *
	 * @return $this
	 */
	public function deactivate(?string $messages = null)
	{
		if (null !== $messages) {
			$fieldModel = $this->source->getField('logs');
			$messages = \App\Purifier::decodeHtml(\App\Purifier::encodeHtml($messages));
			$this->source->set('logs', \App\TextUtils::textTruncate($messages, $fieldModel->getMaxValue(), true, true));
		}
		$this->source->set('mailaccount_status', self::STATUS_INACTIVE)->save();

		return $this;
	}

	/**
	 * Get last UID.
	 *
	 * @param string $folderName
	 *
	 * @return int|null
	 */
	public function getLastUid(string $folderName)
	{
		return (new \App\Db\Query())->select(['uid'])->from(Scanner::FOLDER_TABLE)
			->where(['user_id' => $this->source->getId(), 'name' => $folderName])->scalar();
	}

	/**
	 * Set UID to folder.
	 *
	 * @param int    $uid
	 * @param string $folderName
	 *
	 * @return bool
	 */
	public function setLastUid(int $uid, string $folderName): bool
	{
		return (bool) \App\Db::getInstance()->createCommand()
			->update(Scanner::FOLDER_TABLE, ['uid' => $uid], ['user_id' => $this->source->getId(), 'name' => $folderName])->execute();
	}

	/**
	 * Get actions.
	 *
	 * @return array
	 */
	public function getActions(): array
	{
		$actions = $this->getSource()->get('scanner_actions');
		return $actions ? explode(',', $actions) : [];
	}

	/**
	 * Get folders.
	 *
	 * @return array
	 */
	public function getFolders(): array
	{
		$folders = $this->getSource()->get('folders');
		return \App\Json::isEmpty($folders) ? [] : \App\Json::decode($folders);
	}
}
