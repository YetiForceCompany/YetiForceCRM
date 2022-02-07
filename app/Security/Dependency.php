<?php
/**
 * Security dependency check file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Security;

/**
 * Security dependency check class.
 */
class Dependency
{
	/**
	 * @var string Cache file name.
	 */
	const CACHE_FILE_NAME = ROOT_DIRECTORY . '/cache/security/dependency.json';
	/**
	 * @var string Symfony check url.
	 */
	private $checkUrl = 'https://security.yetiforce.com/vulnerabilities';

	/**
	 * Security checker.
	 *
	 * @param bool $cache
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	public function securityChecker(bool $cache = true): array
	{
		$result = [];
		if ($cache && $this->hasCache()) {
			$result = $this->getCache();
		} else {
			$result = $this->check();
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
		return \file_exists(static::CACHE_FILE_NAME)
			&& \filesize(static::CACHE_FILE_NAME) > 0
			&& \time() - \filemtime(static::CACHE_FILE_NAME) < (int) \App\Config::security('CACHE_LIFETIME_SENSIOLABS_SECURITY_CHECKER');
	}

	/**
	 * Save the data to the cache.
	 *
	 * @param array $result
	 */
	private function saveCache(array $result): void
	{
		\file_put_contents(static::CACHE_FILE_NAME, \App\Json::encode($result));
	}

	/**
	 * Get data from the cache.
	 *
	 * @return array
	 */
	private function getCache(): array
	{
		return \App\Json::decode(\file_get_contents(static::CACHE_FILE_NAME));
	}

	/**
	 * Send lock file to verify.
	 *
	 * @return array
	 */
	public function check(): array
	{
		$result = [];
		if (!\App\YetiForce\Shop::check('YetiForceVulnerabilities')) {
			return $result;
		}
		if (\App\RequestUtil::isNetConnection() && !empty($lockFile = $this->getLockFile(ROOT_DIRECTORY))) {
			$options = \App\RequestHttp::getOptions();
			$options['http_errors'] = false;
			$options['allow_redirects'] = false;
			$options['headers']['APP-ID'] = \App\YetiForce\Register::getInstanceKey();
			\App\Log::beginProfile("POST|Dependency::check|{$this->checkUrl}", __NAMESPACE__);
			try {
				$response = (new \GuzzleHttp\Client($options))->post($this->checkUrl, ['json' => [
					'php' => PHP_VERSION,
					'env' => \App\Utils\ConfReport::getEnv(),
					'dependencies' => $lockFile,
				]]);
				if (200 === $response->getStatusCode()) {
					$result = (array) \App\Json::decode($response->getBody()->getContents());
					$result = (\is_array($result) && !empty($result)) ? $result : [];
				}
			} catch (\Throwable $e) {
				\App\Log::warning($e->__toString(), __CLASS__);
			}
			\App\Log::endProfile("POST|Dependency::check|{$this->checkUrl}", __NAMESPACE__);
		}
		return $result;
	}

	/**
	 * Get lock file content.
	 *
	 * @param string $lock
	 *
	 * @return array
	 */
	private function getLockFile(string $lock): array
	{
		if (is_dir($lock) && file_exists($lock . \DIRECTORY_SEPARATOR . 'composer.lock')) {
			$lock = $lock . \DIRECTORY_SEPARATOR . 'composer.lock';
		} elseif (preg_match('/composer\.json$/', $lock)) {
			$lock = str_replace('composer.json', 'composer.lock', $lock);
		}
		if (is_file($lock)) {
			return $this->getLockContent($lock);
		}
		return [];
	}

	/**
	 * Get parsed lock file elements.
	 *
	 * @param string $lock
	 *
	 * @return array
	 */
	private function getLockContent(string $lock): array
	{
		$contents = json_decode(file_get_contents($lock), true);
		$packages = ['packages' => []];
		if (\is_array($contents['packages'])) {
			foreach ($contents['packages'] as $package) {
				$packages['packages'][] = [
					'name' => $package['name'],
					'version' => $package['version'],
					'time' => $package['time'],
				];
			}
		}
		return $packages;
	}
}
