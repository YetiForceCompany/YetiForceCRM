<?php
/**
 * Basic Google OAuth provider - file.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Integrations\OAuth;

/**
 * Basic Google OAuth provider - class.
 */
class Google extends AbstractProvider
{
	/**
	 * OAuth provider label.
	 *
	 * @var string
	 */
	protected $label = 'Google';

	/** @var string Icon for authorization button */
	protected $icon = 'fab fa-google';

	/**
	 * List of scopes that will be used for authentication.
	 *
	 * @var array
	 *
	 * @see https://developers.google.com/identity/protocols/googlescopes
	 */
	protected $scopes;
	protected $scopesForAction = ['MailAccount' => ['https://mail.google.com/']];

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
	/** @var \League\OAuth2\Client\Provider\Google Google provider */
	private $client;

	public function getClient(array $options = [])
	{
		if (!$this->client) {
			$options = array_merge([
				'clientId' => $this->clientId,
				'clientSecret' => $this->clientSecret,
				'redirectUri' => $this->getRedirectUri(),
				// 	//'hostedDomain' => 'example.com', // optional; used to restrict access to users on your G Suite/Google Apps for Business accounts
				'accessType' => $this->accessType,
				'scopes' => $this->scopes,
				// 'prompt' => 'consent', // to alweys return refresh_token
			], $options);
			$this->client = new \League\OAuth2\Client\Provider\Google($options);
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
		$this->token = null;
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
