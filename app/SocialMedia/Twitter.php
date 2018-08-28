<?php

namespace App\SocialMedia;

/**
 * SocialMedia Helper.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Twitter implements SocialMediaInterface
{
	/**
	 * @var \Abraham\TwitterOAuth\TwitterOAuth
	 */
	private $twitterConnection;

	/**
	 * Is configured.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return bool
	 */
	public static function isConfigured()
	{
		$configTitter = \Settings_SocialMedia_Config_Model::getInstance('twitter');
		return !empty($configTitter->get('twitter_api_key')) && !empty($configTitter->get('twitter_api_secret'));
	}

	/**
	 * Vtiger_SocialMedia_Helper constructor.
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function __construct()
	{
		$configTitter = \Settings_SocialMedia_Config_Model::getInstance('twitter');
		$this->twitterConnection = new \Abraham\TwitterOAuth\TwitterOAuth(
			$configTitter->get('twitter_api_key'),
			$configTitter->get('twitter_api_secret')
		);
		$this->twitterConnection->setDecodeJsonAsArray(true);
	}

	public function getData()
	{
	}

	/**
	 * Get twitter user id by name.
	 *
	 * @param string $userName
	 *
	 * @return mixed|false
	 */
	public function getUserIdByName($userName)
	{
		$response = $this->getTwitter('users/lookup', ['screen_name' => $userName]);
		return $response[0]['id'] ?? false;
	}

	/**
	 * Get user time line.
	 *
	 * @param string $userName
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array|object|string
	 */
	public function getUserTimeline($userName)
	{
		return $this->getTwitter('statuses/user_timeline', ['screen_name' => $userName]);
	}

	/**
	 * Check if the Twitter API returned an error.
	 *
	 * @param string $response - Response from Twitter API
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return bool
	 */
	private function isError($response)
	{
		if (isset($response['errors'])) {
			throw new \App\Exceptions\AppException('Twitter API error' . $response['errors']['message'],
				(int) $response['errors']['code']);
		}
		return false;
	}

	/**
	 * Make GET requests to the API with cache.
	 *
	 * @param string $path
	 * @param array  $parameters
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array|object|string
	 */
	private function getTwitter($path, array $parameters = [])
	{
		$cacheKey = $path . md5(\App\Json::encode(($parameters)));
		if (\App\Cache::has('twitter', $cacheKey)) {
			return \App\Cache::get('twitter', $cacheKey);
		}
		$response = $this->twitterConnection->get($path, $parameters);
		$this->isError($response);
		\App\Cache::save('twitter', $cacheKey, $response, \App\Cache::MEDIUM);
		return $response;
	}
}
