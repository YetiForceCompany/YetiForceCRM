<?php
/**
 * Security.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace App;

/**
 * Class Security.
 */
class Security
{
	/**
	 * SensioLabs security checker.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return \SensioLabs\Security\Result
	 */
	public function securityChecker(): \SensioLabs\Security\Result
	{
		$resultObject = null;
		if ($this->hasCache()) {
			$resultObject = $this->getCache();
		} elseif (!\App\RequestUtil::isNetConnection()) {
			throw new \App\Exceptions\AppException('ERR_NO_INTERNET_CONNECTION');
		} else {
			$resultObject = (new \SensioLabs\Security\SecurityChecker())->check(ROOT_DIRECTORY);
			$this->saveCache($resultObject);
		}
		return $resultObject;
	}

	/**
	 * Check if a cache is available.
	 *
	 * @return bool
	 */
	private function hasCache(): bool
	{
		return \file_exists($this->getCacheFileName()) &&
			\filesize($this->getCacheFileName()) > 0 &&
			\time() - \filemtime($this->getCacheFileName()) < (int) \AppConfig::security('CACHE_LIFETIME_SENSIOLABS_SECURITY_CHECKER');
	}

	/**
	 * Save the data to the cache.
	 *
	 * @param \SensioLabs\Security\Result $results
	 *
	 * @throws \App\Exceptions\AppException
	 */
	private function saveCache(\SensioLabs\Security\Result $results)
	{
		\file_put_contents($this->getCacheFileName(), Json::encode([
			'count' => $results->count(),
			'vulnerabilities' => (string) $results,
			'format' => $results->getFormat(),
		]));
	}

	/**
	 * Get data from the cache.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return \SensioLabs\Security\Result
	 */
	private function getCache(): \SensioLabs\Security\Result
	{
		$resultArray = Json::decode(\file_get_contents($this->getCacheFileName()));
		return new \SensioLabs\Security\Result(
			$resultArray['count'], $resultArray['vulnerabilities'], $resultArray['format']
		);
	}

	/**
	 * Get the name of the cache file.
	 *
	 * @return string
	 */
	private function getCacheFileName(): string
	{
		return ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'cache/security/dependency.json';
	}
}
