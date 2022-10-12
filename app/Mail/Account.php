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

	public function update()
	{
		foreach (['password', 'refresh_token', 'expire_time'] as $fieldName) {
			$fieldModel = $this->source->getField($fieldName);
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
					$value = $this->expireTime;
					break;
				default:
					break;
			}
			$this->source->set($fieldModel->getName(), $value)->setDataForSave([$fieldModel->getTableName() => [$fieldModel->getColumnName() => $value]]);
		}
		if ($this->source->getPreviousValue()) {
			$fieldModel = $this->source->getField('logs');
			$this->source->set($fieldModel->getName(), '')->setDataForSave([$fieldModel->getTableName() => [$fieldModel->getColumnName() => '']]);
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
	 * Open imap connection.
	 *
	 * @return Connections\Imap
	 */
	public function openImap(): Connections\Imap
	{
		$imap = new \App\Mail\Connections\Imap([
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
		} catch (\Throwable $th) {
			// // try only once if token has expired
			if (1 === $this->attempt && 'oauth2' === $this->server->get('auth_method')) {
				$this->getAccessToken();
				$this->update();
				return $this->openImap();
			}
			throw $th;
		}

		return $imap;
	}
}
