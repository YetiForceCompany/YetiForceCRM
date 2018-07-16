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
		$this->$key = $value;

		return $this;
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
		return $this->$key;
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
	 * Function to get full patch.
	 *
	 * @return string
	 */
	public function getPatch()
	{
		$patch = $this->getUrl();
		$keys = $this->getRequiredParams();
		$keys[] = $this->toName;
		$keys[] = $this->messageName;
		$params = [];
		foreach ($keys as $key) {
			$params[$key] = $this->get($key);
		}
		return $patch . http_build_query($params);
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
			$request = Requests::post($this->getPatch(), $this->getHeaders());
		} catch (Exception $e) {
			\App\Log::warning($e->getMessage());
			return false;
		}
		return $this->getResponse($request);
	}

	/**
	 * Response.
	 */
	abstract public function getResponse(Requests_Response $request);

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
