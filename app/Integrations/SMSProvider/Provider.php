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
	 * Response.
	 *
	 * @param \GuzzleHttp\Psr7\Response $request
	 */
	abstract public function getResponse($request);

	/**
	 * Fields for edit view in settings.
	 *
	 * @return array
	 */
	abstract public function getEditFields(): array;

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

	/**
	 * Send by record.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return bool
	 */
	abstract public function sendByRecord(\Vtiger_Record_Model $recordModel): bool;
}
