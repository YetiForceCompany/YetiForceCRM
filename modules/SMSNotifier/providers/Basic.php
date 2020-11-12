<?php
/**
 * Basic class to sms provider.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Basic class to sms provider.
 */
abstract class SMSNotifier_Basic_Provider
{
	/**
	 * Provider name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Address URL.
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * Variable name.
	 *
	 * @var string
	 */
	public $toName = 'to';

	/**
	 * Variable name.
	 *
	 * @var string
	 */
	public $messageName = 'message';

	/**
	 * Function to get provider name.
	 *
	 * @return string provider name
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Function to get service URL.
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * Set.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return \self
	 */
	public function set($key, $value)
	{
		$this->{$key} = $value;

		return $this;
	}

	/**
	 * Function to check if the key exists.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function has($key)
	{
		return isset($this->{$key});
	}

	/**
	 * Get.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get($key)
	{
		return $this->{$key};
	}

	/**
	 * Headers.
	 *
	 * @return string[]
	 */
	public function getHeaders()
	{
		return [
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer ' . $this->getAuthorization(),
		];
	}

	/**
	 * Authorization.
	 *
	 * @return string
	 */
	public function getAuthorization()
	{
		return $this->get('api_key');
	}

	/**
	 * Required fields.
	 *
	 * @return string[]
	 */
	public function getRequiredParams()
	{
		return [];
	}

	/**
	 * Function to get full path.
	 *
	 * @return string
	 */
	public function getPath()
	{
		$path = $this->getUrl();
		$keys = $this->getRequiredParams();
		$keys[] = $this->toName;
		$keys[] = $this->messageName;
		$params = [];
		foreach ($keys as $key) {
			$params[$key] = $this->get($key);
		}
		return $path . http_build_query($params);
	}

	/**
	 * Function to handle SMS Send operation.
	 *
	 * @param string          $message
	 * @param string|string[] $toNumbers
	 */
	public function send()
	{
		try {
			$url = $this->getPath();
			\App\Log::beginProfile('POST|' . __METHOD__ . "|{$url}", 'SMSNotifier');
			$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('POST', $url, ['headers' => $this->getHeaders()]);
			\App\Log::endProfile('POST|' . __METHOD__ . "|{$url}", 'SMSNotifier');
		} catch (\Throwable $e) {
			\App\Log::error($e->__toString());
			return false;
		}
		return $this->getResponse($response);
	}

	/**
	 * Response.
	 *
	 * @param \GuzzleHttp\Psr7\Response $request
	 */
	abstract public function getResponse($request);

	/**
	 * Fields to edit in settings.
	 *
	 * @return \Settings_Vtiger_Field_Model[]
	 */
	public function getSettingsEditFieldsModel()
	{
		return [];
	}
}
