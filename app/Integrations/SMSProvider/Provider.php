<?php
/**
 * Basic class to sms provider.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Integrations\SMSProvider;

/**
 * Basic class to sms provider.
 */
abstract class Provider extends \App\Base
{
	/**
	 * Provider name | File name.
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

	// /**
	//  * Variable name.
	//  *
	//  * @var string
	//  */
	// protected $toName = 'to';

	// /**
	//  * Variable name.
	//  *
	//  * @var string
	//  */
	// protected $messageName = 'message';

	/**
	 * Function to get provider name.
	 *
	 * @return string provider name
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Function to get service URL.
	 *
	 * @return string
	 */
	public function getUrl(): string
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
	// public function set($key, $value): self
	// {
	// 	$this->{$key} = $value;

	// 	return $this;
	// }

	/**
	 * Function to check if the key exists.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	// public function has($key): bool
	// {
	// 	return isset($this->{$key});
	// }

	/**
	 * Get.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	// public function get(string $key)
	// {
	// 	return $this->{$key};
	// }

	/**
	 * Headers.
	 *
	 * @return string[]
	 */
	public function getHeaders(): array
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
	public function getAuthorization(): string
	{
		return \App\Encryption::getInstance()->decrypt($this->get('api_key'));
	}

	/**
	 * Required fields.
	 *
	 * @return string[]
	 */
	public function getRequiredParams(): array
	{
		return [];
	}

	/**
	 * Function to get full path.
	 *
	 * @return string
	 */
	public function getPath(): string
	{
		$path = $this->getUrl();
		$keys = $this->getRequiredParams();
		// $keys[] = $this->toName;
		// $keys[] = $this->messageName;
		$params = [];
		foreach ($keys as $key) {
			$params[$key] = $this->get($key);
		}
		return $path . http_build_query($params);
	}

	/**
	 * Function to handle SMS Send operation.
	 *
	 * @return bool
	 */
	abstract public function send(): bool;

	/**
	 * Function to handle SMS Send operation.
	 *
	 * @param string          $message
	 * @param string|string[] $toNumbers
	 * @param mixed           $request
	 */
	// public function send()
	// {
	// 	try {
	// 		$url = $this->getPath();
	// 		\App\Log::beginProfile('POST|' . __METHOD__ . "|{$url}", 'SMSNotifier');
	// 		$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('POST', $url, ['headers' => $this->getHeaders()]);
	// 		echo '<pre>', print_r([
	// 			$url,
	// 			\App\Json::decode($response->getBody())
	// 		]);
	// 		echo '</pre>';
	// 		exit;
	// 		\App\Log::endProfile('POST|' . __METHOD__ . "|{$url}", 'SMSNotifier');
	// 	} catch (\Throwable $e) {
	// 		\App\Log::error($e->__toString());
	// 		return false;
	// 	}
	// 	return $this->getResponse($response);
	// }

	/**
	 * Response.
	 *
	 * @param \GuzzleHttp\Psr7\Response $request
	 */
	abstract public function getResponse($request);

	abstract public function getEditFields(): array;

	/**
	 * Fields to edit in settings.
	 *
	 * @return \Settings_Vtiger_Field_Model[]
	 */
	// public function getSettingsEditFieldsModel()
	// {
	// 	return [];
	// }

	/**
	 * Function to get Edit view url.
	 *
	 * @return string Url
	 */
	public function getEditViewUrl(): string
	{
		$model = \Settings_Vtiger_Module_Model::getInstance('Settings:SMSNotifier');
		return 'index.php?module=' . $model->getName() . '&parent=' . $model->getParentName() . "&view=Edit&provider={$this->name}";
	}

	abstract public function sendByRecord(\Vtiger_Record_Model $recordModel): bool;
}
