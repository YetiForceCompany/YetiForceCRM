<?php
/**
 * Dependencies class.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

namespace App\Utils;

/**
 * Dependencies.
 */
class Dependencies
{
	private $checkUrl = 'https://security.symfony.com/check_lock';

	/**
	 * Send lock file to verify.
	 *
	 * @return array
	 */
	public function check(): array
	{
		$result = [];
		if (\App\RequestUtil::isNetConnection() && !empty($lockFile = $this->getLockFile(ROOT_DIRECTORY))) {
			$headers = \App\RequestHttp::getOptions();
			$headers['headers']['Content-Type'] = 'application/octet-stream';
			$headers['headers']['Content-Disposition'] = 'form-data; name="lock"; filename="composer.lock"';
			$headers['headers']['Accept'] = 'application/json';
			$response = (new \GuzzleHttp\Client($headers))->post($this->checkUrl, [
				'body' => $lockFile
			]);
			$result = (array) \App\Json::decode($response->getBody());
			$result = (\is_array($result) && !empty($result)) ? $result : [];
		}
		return $result;
	}

	/**
	 * Get lock file content.
	 *
	 * @param string $lock
	 *
	 * @return string
	 */
	private function getLockFile($lock): string
	{
		if (is_dir($lock) && file_exists($lock . \DIRECTORY_SEPARATOR . 'composer.lock')) {
			$lock = $lock . \DIRECTORY_SEPARATOR . 'composer.lock';
		} elseif (preg_match('/composer\.json$/', $lock)) {
			$lock = str_replace('composer.json', 'composer.lock', $lock);
		}
		return is_file($lock) ? $this->getLockContent($lock) : '';
	}

	/**
	 * Get parsed lock file elements.
	 *
	 * @param string $lock
	 *
	 * @return string
	 */
	private function getLockContent($lock): string
	{
		$contents = json_decode(file_get_contents($lock), true);
		$hash = $contents['content-hash'] ?? ($contents['hash'] ?? '');
		$packages = ['content-hash' => $hash, 'packages' => []];
		if (\is_array($contents['packages'])) {
			foreach ($contents['packages'] as $package) {
				$data = [
					'name' => $package['name'],
					'version' => $package['version'],
				];
				if (isset($package['time']) && false !== strpos($package['version'], 'dev')) {
					$data['time'] = $package['time'];
				}
				$packages['packages'][] = $data;
			}
		}
		return json_encode($packages);
	}
}
