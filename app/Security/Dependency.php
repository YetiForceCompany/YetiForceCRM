<?php
/**
 * Security dependency check.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace App\Security;

/**
 * Class Dependency - Security dependency check.
 */
class Dependency
{
	/**
	 * Cache file name.
	 */
	const CACHE_FILE_NAME = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'security' . DIRECTORY_SEPARATOR . 'dependency.json';

	/**
	 * SensioLabs security checker.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	public function securityChecker(): array
	{
		$result = [];
		if ($this->hasCache()) {
			$result = $this->getCache();
		} elseif (\App\RequestUtil::isNetConnection()) {
			$resultObject = (new \SensioLabs\Security\SecurityChecker())->check(ROOT_DIRECTORY);
			$result = \App\Json::decode((string) $resultObject);
			$result = (\is_array($result) && !empty($result)) ? $result : [];
			$this->saveCache($result);
		}
		return $result;
	}

	/**
	 * Check if a cache is available.
	 *
	 * @return bool
	 */
	private function hasCache(): bool
	{
		return \file_exists(static::CACHE_FILE_NAME) &&
			\filesize(static::CACHE_FILE_NAME) > 0 &&
			\time() - \filemtime(static::CACHE_FILE_NAME) < (int) \AppConfig::security('CACHE_LIFETIME_SENSIOLABS_SECURITY_CHECKER');
	}

	/**
	 * Save the data to the cache.
	 *
	 * @param array $result
	 *
	 * @throws \App\Exceptions\AppException
	 */
	private function saveCache(array $result)
	{
		\file_put_contents(static::CACHE_FILE_NAME, \App\Json::encode($result));
	}

	/**
	 * Get data from the cache.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	private function getCache(): array
	{
		return \App\Json::decode(\file_get_contents(static::CACHE_FILE_NAME));
	}
}
