<?php
/**
 * SMSAPI - sms provider file.
 *
 * The file is part of the paid functionality. Using the file is allowed only after purchasing a subscription.
 * File modification allowed only with the consent of the system producer.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Integrations\SMSProvider;

/**
 * SMSAPI - sms provider class.
 */
class SMSAPI extends Provider
{
	/**
	 * Provider name.
	 *
	 * @var string
	 */
	protected $name = 'SMSAPI';

	/**
	 * Address URL.
	 *
	 * @var string
	 */
	protected $url = 'https://api.smsapi.pl/sms.do';

	/**
	 * Backup address URL.
	 *
	 * @var string
	 */
	protected $urlBackup = 'https://api2.smsapi.pl/sms.do';

	/**
	 * Encoding.
	 *
	 * @var string
	 */
	public $encoding = 'utf-8';

	/**
	 * Format.
	 *
	 * @var string
	 */
	public $format = 'json';

	/**
	 * Required fields.
	 *
	 * @return string[]
	 */
	public function getRequiredParams(): array
	{
		return ['to', 'idx', 'from', 'message'];
	}

	/**
	 * Function to get service URL.
	 *
	 * @param bool $useBackup
	 *
	 * @return string
	 */
	public function getUrl(bool $useBackup = false): string
	{
		return $useBackup ? $this->urlBackup : $this->url;
	}

	/** {@inheritdoc} */
	public function getResponse($request)
	{
		$response = \App\Json::decode($request->getBody());
		return 200 === $request->getStatusCode() && empty($response['error']);
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
		$phoneNumber = preg_replace_callback('/[^\d]/s', function () {
			return '';
		}, $phoneNumber);
		$this->set('to', $phoneNumber);

		return $this;
	}

	/**
	 * Get body data.
	 *
	 * @return array
	 */
	private function getBody(): array
	{
		$params = [
			'encoding' => $this->encoding,
			'format' => $this->format
		];
		foreach ($this->getRequiredParams() as $key) {
			$params[$key] = $this->get($key) ?? '';
		}

		return $params;
	}

	/** {@inheritdoc} */
	public function send(bool $useBackup = false): bool
	{
		try {
			$uri = $this->getUrl($useBackup);
			\App\Log::beginProfile('POST|' . __METHOD__ . "|{$uri}", 'SMSNotifier');
			$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('POST', $uri, [
				'headers' => $this->getHeaders(),
				'json' => $this->getBody()
			]);
			\App\Log::endProfile('POST|' . __METHOD__ . "|{$uri}", 'SMSNotifier');
		} catch (\Throwable $e) {
			\App\Log::error($e->__toString());
			return false;
		}

		return $this->getResponse($response);
	}

	/** {@inheritdoc} */
	public function getEditFields(): array
	{
		$fields = [];
		foreach (['api_key', 'from', 'type'] as $fieldName) {
			$fields[$fieldName] = $this->getFieldInstanceByName($fieldName);
		}

		return $fields;
	}

	/**
	 * Fields to edit in settings.
	 *
	 * @param string $name
	 *
	 * @return \Vtiger_Field_Model
	 */
	public function getFieldInstanceByName(string $name)
	{
		$moduleName = 'Settings:SMSNotifier';
		$field = ['uitype' => 16, 'column' => $name, 'name' => $name, 'displaytype' => 1, 'typeofdata' => 'V~M', 'presence' => 0, 'isEditableReadOnly' => false];
		switch ($name) {
				case 'api_key':
					$field['uitype'] = 99;
					$field['label'] = 'FL_API_KEY';
					$field['purifyType'] = \App\Purifier::ALNUM;
					$field['fromOutsideList'] = true;
					$field['maximumlength'] = '100';
					$field['fieldvalue'] = $this->has($name) ? $this->get($name) : '';
					break;
				case 'from':
					$field['uitype'] = 1;
					$field['label'] = 'FL_SMSAPI_FROM';
					$field['typeofdata'] = 'V~O';
					$field['maximumlength'] = '11';
					$field['purifyType'] = \App\Purifier::TEXT;
					$field['fieldvalue'] = $this->has($name) ? $this->get($name) : '';
					break;
				case 'type':
					$field['uitype'] = 16;
					$field['label'] = 'FL_SMSAPI_TYPE';
					$field['typeofdata'] = 'V~O';
					$field['purifyType'] = \App\Purifier::STANDARD;
					$field['fieldvalue'] = $this->has($name) ? $this->get($name) : '';
					$field['picklistValues'] = ['SMS' => 'SMS', 'VMS' => 'VMS', 'MMS' => 'MMS'];
					break;
				case 'tts_lector':
					$field['uitype'] = 1;
					$field['label'] = 'FL_SMSAPI_TTS_LECTOR';
					$field['purifyType'] = \App\Purifier::STANDARD;
					$field['fieldvalue'] = $this->has($name) ? $this->get($name) : '';
					break;
				default:
					$field = [];
					break;
			}

		return $field ? \Vtiger_Field_Model::init($moduleName, $field, $name) : null;
	}

	/**
	 * Function to get Edit view url.
	 *
	 * @return string Url
	 */
	public function getEditViewUrl(): string
	{
		$model = \Settings_Vtiger_Module_Model::getInstance('Settings:SMSNotifier');
		return "index.php?module={$model->getName()}&parent={$model->getParentName()}&view={$this->name}&provider={$this->name}";
	}

	/**
	 * Callback service URL.
	 *
	 * @param array $service
	 *
	 * @return string
	 */
	public function getCallBackUrlByService(array $service): string
	{
		$callBackUrl = \App\Config::main('site_URL') . 'webservice.php?';
		$serviceId = (int) $service['server_id'];
		$server = \Settings_WebserviceApps_Record_Model::getInstanceById($serviceId);
		$apiKey = \App\Encryption::getInstance()->decrypt($server->get('api_key'));
		$params = [
			'_container' => 'SMS',
			'module' => 'SMSAPI',
			'action' => 'Report',
			'x-api-key' => $apiKey,
			'x-token' => $service['token'],
		];

		return $callBackUrl . http_build_query($params);
	}

	/** {@inheritdoc} */
	public function sendByRecord(\Vtiger_Record_Model $recordModel): bool
	{
		$this->setPhone($recordModel->get('phone'));
		$this->set('idx', $recordModel->getId());
		$this->set('message', $recordModel->get('message'));
		return $this->send() ?: $this->send(true);
	}
}
