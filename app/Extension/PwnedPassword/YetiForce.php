<?php

/**
 * YetiForce provider file to check the password.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Extension\PwnedPassword;

/**
 * YetiForce provider class to check the password.
 */
class YetiForce extends Base
{
	/** {@inheritdoc} */
	public $url = 'YetiForce';
	/** {@inheritdoc} */
	public $infoUrl = 'index.php?module=YetiForce&parent=Settings&view=Shop&product=YetiForcePassword&mode=showProductModal';

	/** {@inheritdoc} */
	public function check(string $password): array
	{
		$status = ['status' => true];
		$product = \App\YetiForce\Register::getProducts('YetiForcePassword');
		if (empty($password) || !\App\RequestUtil::isNetConnection() || empty($product['params']['login']) || empty($product['params']['pass'])) {
			return $status;
		}
		try {
			$url = 'https://passwords.yetiforce.eu/pwned';
			\App\Log::beginProfile("POST|YetiForce::check|{$url}", __NAMESPACE__);
			$request = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('POST', $url,
				['json' => ['sha1' => sha1($password)], 'timeout' => 2,  'http_errors' => false,  'auth' => [$product['params']['login'], $product['params']['pass']], 'headers' => ['InsKey' => \App\YetiForce\Register::getInstanceKey()]]);
			\App\Log::endProfile("POST|YetiForce::check|{$url}", __NAMESPACE__);
			if (200 === $request->getStatusCode()) {
				$response = \App\Json::decode($request->getBody());
				if (isset($response['count'])) {
					$status = [
						'message' => \App\Language::translate('LBL_ALERT_PWNED_PASSWORD', 'Settings:Password'),
						'status' => 0 === (int) $response['count'],
					];
				} elseif ($response['error']) {
					throw new \App\Exceptions\AppException('Error with response |' . $response['error']);
				}
			} else {
				throw new \App\Exceptions\AppException('Error with connection |' . $request->getReasonPhrase());
			}
		} catch (\Exception $ex) {
			\App\Log::error($ex->getMessage(), __CLASS__);
		}
		return $status;
	}

	/** {@inheritdoc} */
	public function isActive(): bool
	{
		return \App\YetiForce\Shop::check('YetiForcePassword');
	}
}
