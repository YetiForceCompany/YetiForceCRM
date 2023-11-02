<?php
/**
 * YetiForce register file.
 * Modifying this file or functions that affect the footer appearance will violate the license terms!!!
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\YetiForce;

/**
 * YetiForce register class.
 */
final class ApiClient
{
	/** @var string URL */
	public const URL = 'https://api.yetiforce.eu/registrations';

	/** @var string Last error. */
	public ?string $error = null;
	/** @var bool Response result */
	private bool $success;

	/** @var int|null Resopnse code */
	private ?int $responseCode = 0;
	/** @var string Resopnse body */
	private $responseBody;

	/** @var int Total timeout of the request in seconds. */
	private int $timeout = 20;
	/** @var int The number of seconds to wait while trying to connect to a server. */
	private int $connectTimeout = 10;

	/**
	 * Send registration data.
	 *
	 * @param string $url
	 * @param string $method
	 * @param array  $option
	 *
	 * @return bool
	 */
	public function send(string $url, string $method, array $option = []): bool
	{
		$this->error = null;
		$this->success = false;
		$this->basicValidations($url);
		if ($this->error) {
			return $this->success;
		}

		try {
			\App\Log::beginProfile($method . '|' . __METHOD__ . "|{$url}", __NAMESPACE__);
			$response = (new \GuzzleHttp\Client($this->getRequestOptions()))->request($method, $url, $option);
			\App\Log::endProfile($method . '|' . __METHOD__ . "|{$url}", __NAMESPACE__);

			$this->responseCode = $response->getStatusCode();
			$this->responseBody = $response->getBody()->getContents();
			$this->success = true;
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			$this->responseCode = $e->getResponse()->getStatusCode();
			$this->error = $e->getResponse()->getBody()->getContents();
			if (\App\Json::isJson($this->error) && ($error = \App\Json::decode($this->error)['errors'] ?? null)) {
				$this->error = \is_array($error) ? implode(' | ', $error) : $error;
			}
			\App\Log::error($e->getMessage(), __METHOD__);
		} catch (\GuzzleHttp\Exception\ServerException $e) {
			$this->responseCode = $e->getResponse()->getStatusCode();
			$this->error = $this->responseCode . ' Internal Server Error';
			\App\Log::error($e->getMessage(), __METHOD__);
		} catch (\Throwable $e) {
			$this->error = \App\Language::translate("LBL_ERROR");
			\App\Log::error($e->getMessage(), __METHOD__);
		}

		return $this->success;
	}

	/**
	 * Get response status code.
	 *
	 * @return int
	 */
	public function getStatusCode()
	{
		return $this->responseCode;
	}

	/**
	 * Get response content.
	 *
	 * @return mixed
	 */
	public function getResponseBody()
	{
		return $this->responseBody;
	}

	/**
	 * Get last error.
	 *
	 * @return string
	 */
	public function getError(): string
	{
		return $this->error ?? '';
	}

	/**
	 * Get request options.
	 *
	 * @return array
	 */
	public function getRequestOptions(): array
	{
		$headers = [
			'x-crm-id' => \App\Config::main('application_unique_key'),
			'x-app-id' => Register::getInstanceKey(),
			'accept-language' => \App\Language::getLanguage() ?: 'en'
		];
		if ($key = (new Config())->getToken()) {
			$headers['x-api-key'] = $key;
		}

		$options = \App\RequestHttp::getOptions();

		return array_merge($options, [
			'headers' => array_merge($options['headers'] ?? [], $headers),
			'timeout' => $this->timeout,
			'connect_timeout' => $this->connectTimeout
		]);
	}

	/**
	 * Basic validations.
	 *
	 * @param string $url
	 *
	 * @return void
	 */
	private function basicValidations($url)
	{
		$hostName = parse_url($url, PHP_URL_HOST);
		if (!\App\RequestUtil::isNetConnection() || $hostName === gethostbyname($hostName)) {
			\App\Log::warning('ERR_NO_INTERNET_CONNECTION', __METHOD__);
			$this->error = 'ERR_NO_INTERNET_CONNECTION';
		} elseif (!$this->isWritable()) {
			\App\Log::warning('ERR_REGISTER_FILES_PERMISSIONS||app_data', __METHOD__);
			$this->error = 'ERR_REGISTER_FILES_PERMISSIONS||app_data';
		}
	}

	/**
	 * Check write permissions for the registry file.
	 *
	 * @return bool
	 */
	private function isWritable(): bool
	{
		$path = Register::REGISTRATION_FILE;
		return (file_exists($path) && is_writable($path)) || (!file_exists($path) && is_writable(\dirname($path)));
	}
}
