<?php
/**
 * Basic abstract file for oauth provider.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\OAuth;

/**
 * Basic abstract class to oauth provider.
 */
abstract class AbstractProvider extends \App\Base
{
	protected $label;
	protected $icon;

	protected $accessToken;
	protected $refreshToken;
	protected $expireTime;
	protected $state;
	protected $scopes;
	protected $scopesForAction;

	/**
	 * Get provider name.
	 * Provider name | File name.
	 * Max lenght: 50 characters.
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return substr(strrchr(static::class, '\\'), 1);
	}

	public function getLabel(): string
	{
		return $this->label;
	}

	public function getIcon(): string
	{
		return $this->icon;
	}

	public function setData($values)
	{
		$reflect = new \ReflectionClass($this);
		foreach ($values as $name => $value) {
			if ($reflect->hasProperty($name) && !$reflect->getProperty($name)->isPrivate()) {
				$this->{$name} = $value;
			}
		}

		return $this;
	}

	public function getToken(): ?string
	{
		return $this->accessToken;
	}

	public function getRefreshToken(): ?string
	{
		return $this->refreshToken;
	}

	public function getExpires(): ?string
	{
		return $this->expireTime;
	}

	public function getState($refresh = false): string
	{
		if ($this->state && !$refresh) {
			return $this->state;
		}
		return $this->state = bin2hex(random_bytes(18));
	}

	public function getAuthorizationUrl(array $options = []): string
	{
		$options = array_merge($options, ['state' => $this->getState()/* , 'prompt' => 'consent' */]);
		return $this->getClient()->getAuthorizationUrl($options);
	}

	public function getScopesByAction(string $type)
	{
		return $this->scopesForAction[$type] ?? [];
	}

	public function getResourceOwner()
	{
		return $this->getClient()->getResourceOwner($this->token);
	}

	abstract public function getClient(array $options = []);

	abstract public function getAccessToken($grant, array $options = []);

	abstract public function refreshToken();
}
