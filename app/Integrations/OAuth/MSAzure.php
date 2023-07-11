<?php
/**
 * Basic Azure OAuth provider - file.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Integrations\OAuth;

use TheNetworg\OAuth2\Client\Provider\Azure;

/**
 * Basic Azure OAuth provider - class.
 */
class MSAzure extends AbstractProvider
{
	/**
	 * OAuth provider label.
	 *
	 * @var string
	 */
	protected $label = 'MS Azure - Outlook.com (Office 365)';

	/** @var string Icon for authorization button */
	protected $icon = 'fab fa-microsoft';

	/**
	 * List of scopes that will be used for authentication.
	 *
	 * @var array
	 *
	 * @see https://learn.microsoft.com/en-us/azure/active-directory/develop/v2-permissions-and-consent
	 */
	protected $scopes;
	protected $scopesForAction = ['MailAccount' => ['openid', 'https://outlook.office.com/IMAP.AccessAsUser.All', 'offline_access', 'email',  'https://outlook.office.com/SMTP.Send']];

	/**
	 * @var string If set, this will be sent to google as the "access_type" parameter.
	 *
	 * @see https://developers.google.com/identity/protocols/OpenIDConnect#authenticationuriparameters
	 */
	protected $accessType = 'offline';

	/**
	 * @var string The client ID string that you obtain from the API Console.
	 *
	 * @see https://developers.google.com/identity/protocols/oauth2/openid-connect#getcredentials
	 */
	protected $clientId;

	/** @var string Secret known only to the application and the authorization server */
	protected $clientSecret;

	/**
	 * Determines where the response is sent.
	 * The value of this parameter must exactly match one of the authorized redirect values that you set in the API Console.
	 *
	 * @var string
	 */
	protected $redirectUri;

	protected $refreshToken;
	protected $accessToken;
	protected $expireTime;
	/** @var \TheNetworg\OAuth2\Client\Provider\Azure Azure provider */
	private $client;

	public function getClient(array $options = [])
	{
		if (!$this->client) {
			$options = array_merge(['clientId' => $this->clientId,
				'clientSecret' => $this->clientSecret,
				'redirectUri' => $this->getRedirectUri(),
				'accessType' => $this->accessType,
				'scopes' => $this->scopes,
				'defaultEndPointVersion' => \TheNetworg\OAuth2\Client\Provider\Azure::ENDPOINT_VERSION_2_0], $options);
			$this->client = new \TheNetworg\OAuth2\Client\Provider\Azure($options);
		}

		return $this->client;
	}

	public function getRedirectUri(): string
	{
		return $this->redirectUri;
	}

	/**
	 * Requests an access token using a specified grant and option set.
	 *
	 * @param mixed $grant
	 * @param array $options
	 *
	 * @return string
	 */
	public function getAccessToken($grant, array $options = [])
	{
		try {
			$token = $this->getClient()->getAccessToken($grant, $options);
			$this->accessToken = $token->getToken();
			$this->expireTime = $token->getExpires();
			if ($token->getRefreshToken()) {
				$this->refreshToken = $token->getRefreshToken();
			}
			$this->token = $token;
		} catch (\Throwable $th) {
			\App\Log::error($th->getMessage());
			throw $th;
		}

		return $token->getToken();
	}

	public function refreshToken()
	{
		$grant = new \League\OAuth2\Client\Grant\RefreshToken();
		$this->getAccessToken($grant, ['refresh_token' => $this->getRefreshToken()]);

		return $this;
	}
}
