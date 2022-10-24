<?php
/**
 * Mail server file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Mail;

use App\Controller\Components\Action\Mail;

/**
 * Mail server class.
 */
class Server extends \App\Base
{
	/** @var string Basic table name */
	public const TABLE_NAME = 's_#__mail_servers';

	/** @var int Status inactive */
	public const STATUS_INACTIVE = 0;
	/** @var int Status active */
	public const STATUS_ACTIVE = 1;
	/** @var int Invisible to users */
	public const USER_INVISIBLE = 0;
	/** @var int Visible to users */
	public const USER_VISIBLE = 1;

	/**
	 * Get a list of all pbx servers.
	 *
	 * @param int|null $status  self::STATUS_INACTIVE, self::STATUS_ACTIVE
	 * @param int|null $visible
	 *
	 * @return array
	 */
	public static function getAll(?int $status = null, ?int $visible = null)
	{
		if (\App\Cache::has('MailServer', 'all')) {
			$servers = \App\Cache::get('MailServer', 'all');
		} else {
			$servers = (new \App\Db\Query())->from(self::TABLE_NAME)->indexBy('id')->all(\App\Db::getInstance('admin'));
			\App\Cache::save('MailServer', 'all', $servers, \App\Cache::LONG);
		}
		if (null !== $status || null !== $visible) {
			$servers = array_filter($servers, fn ($server) => (null === $status || $server['status'] === $status) && (null === $visible || $server['visible'] = $visible));
		}

		return $servers;
	}

	/**
	 * Get instance by ID.
	 *
	 * @param int $id
	 */
	public static function getInstanceById(int $id): ?self
	{
		$instance = null;
		if ($serverData = static::getAll()[$id] ?? null) {
			$instance = (new static())->setData($serverData);
		}

		return $instance;
	}

	public static function isExists(int $serverId, ?int $state = self::STATUS_ACTIVE)
	{
		$server = static::getInstanceById($serverId);
		return $server && (null === $state || $state === $server->getState());
	}

	public static function getRedirectUriByServiceId(int $serviceId): string
	{
		$service = \App\Integrations\Services::getById($serviceId);
		$uri = '';
		if ($service && $service['status'] && \App\Integrations\Services::OAUTH === $service['type']) {
			$uri = \App\Config::main('site_URL') . 'webservice/OAuth/MailAccount/' . \App\Encryption::getInstance()->decrypt($service['api_key']);
		}

		return $uri;
	}

	public function getRedirectUri(): string
	{
		return self::getRedirectUriByServiceId((int) $this->get('redirect_uri_id'));
	}

	public function isViewable(): bool
	{
		return self::STATUS_ACTIVE === $this->get('status') && self::USER_VISIBLE === $this->get('visible');
	}

	public function getClientSecret()
	{
		if ($clientSecret = $this->get('client_secret')) {
			$clientSecret = \App\Encryption::getInstance(\App\Encryption::TARGET_SETTINGS)->decrypt($clientSecret);
		}

		return $clientSecret;
	}

	public function isOAuth(): bool
	{
		return 'oauth2' === $this->get('auth_method');
	}

	public function getState(): int
	{
		return $this->get('status');
	}

	public function getImapHost()
	{
		$encrypt = $this->get('imap_encrypt');
		$host = $this->get('imap_host');
		$port = $this->get('imap_port');

		return ($encrypt ? "{$encrypt}://" : '') . $host . ($port ? ":{$port}" : '');
	}

	public function getSmptHost()
	{
		$encrypt = $this->get('smtp_encrypt');
		$host = $this->get('smtp_host');
		$port = $this->get('smtp_port');

		return ($encrypt ? "{$encrypt}://" : '') . $host . ($port ? ":{$port}" : '');
	}
}
