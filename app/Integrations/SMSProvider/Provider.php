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
	 * Function to handle SMS Send operation.
	 *
	 * @return bool
	 */
	abstract public function send(): bool;

	/**
	 * Fields for edit view in settings.
	 *
	 * @return \Vtiger_Field_Model[]
	 */
	abstract public function getEditFields(): array;

	/**
	 * Send by record.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return bool
	 */
	abstract public function sendByRecord(\Vtiger_Record_Model $recordModel): bool;

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
	 * Set phone number.
	 *
	 * @param string $phoneNumber
	 *
	 * @return $this
	 */
	public function setPhone(string $phoneNumber): self
	{
		return $this;
	}
}
