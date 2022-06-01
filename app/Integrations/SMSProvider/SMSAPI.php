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
	/** {@inheritdoc} */
	protected $name = 'SMSAPI';

	/** {@inheritdoc} */
	protected $url = 'https://api.smsapi.pl/sms.do';

	/** @var string Backup address URL */
	protected $urlBackup = 'https://api2.smsapi.pl/sms.do';

	/** @var string Address URL for MMS */
	protected $mmsUrl = 'https://api.smsapi.pl/mms.do';

	/** @var string Backup address URL for MMS */
	protected $mmsUrlBackup = 'https://api2.smsapi.pl/mms.do';

	/** @var string Encoding */
	public $encoding = 'utf-8';

	/** @var string Format */
	public $format = 'json';

	/** @var \GuzzleHttp\Psr7\Response Response object */
	private $response;

	/** @var array Response body */
	private $responseData;

	/** @var array Attachments */
	private $attach = [];

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

	/**
	 * Set response.
	 *
	 * @param \GuzzleHttp\Psr7\Response $response
	 *
	 * @return $this
	 */
	public function setResponse($response): self
	{
		$this->response = $response;
		$this->responseData = \App\Json::decode($this->response->getBody()) ?? [];

		return $this;
	}

	/**
	 * Check if the message was sent successfully.
	 *
	 * @return bool
	 */
	public function isSuccess(): bool
	{
		return $this->response && 200 === $this->response->getStatusCode() && empty($this->responseData['error']);
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
	 * Set message.
	 *
	 * @param string $message
	 *
	 * @return self
	 */
	public function setMessage(string $message): self
	{
		$message = \App\Utils\Completions::decodeEmoji($message);
		$this->set('message', $message);

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
			if ('' !== $this->get($key)) {
				$params[$key] = $this->get($key);
			}
		}

		return $params;
	}

	/**
	 * Get body data for MMS.
	 *
	 * @return array
	 */
	private function getMMSBody(): array
	{
		$params = [
			['name' => 'format', 'contents' => $this->format],
			['name' => 'smil', 'contents' => $this->getSmil()],
			['name' => 'subject', 'contents' => $this->getSubject()]
		];

		foreach (['to', 'idx'] as $key) {
			$params[] = ['name' => $key, 'contents' => $this->get($key) ?? ''];
		}

		return $params;
	}

	/** {@inheritdoc} */
	public function send(bool $useBackup = false): bool
	{
		if ($this->attach || mb_strlen($this->get('message')) >= 670) {
			return $this->sendMMS($useBackup);
		}
		return $this->sendSMS($useBackup);
	}

	/**
	 * Send SMS.
	 *
	 * @param bool $useBackup
	 *
	 * @return bool
	 */
	public function sendSMS(bool $useBackup = false): bool
	{
		try {
			$uri = $this->getUrl($useBackup);
			\App\Log::beginProfile('POST|' . __METHOD__ . "|{$uri}", 'SMSNotifier');
			$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('POST', $uri, [
				'headers' => $this->getHeaders(),
				'json' => $this->getBody()
			]);
			$this->setResponse($response);
			\App\Log::endProfile('POST|' . __METHOD__ . "|{$uri}", 'SMSNotifier');
		} catch (\Throwable $e) {
			\App\Log::error($e->__toString());
			return false;
		}

		return $this->isSuccess();
	}

	/**
	 * Send MMS.
	 *
	 * @param bool $useBackup
	 *
	 * @return bool
	 */
	public function sendMMS(bool $useBackup = false): bool
	{
		try {
			$uri = $useBackup ? $this->mmsUrlBackup : $this->mmsUrl;
			\App\Log::beginProfile('POST|' . __METHOD__ . "|{$uri}", 'SMSNotifier');
			$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('POST', $uri, [
				'headers' => [
					'Authorization' => 'Bearer ' . $this->getAuthorization(),
				],
				'multipart' => $this->getMMSBody()
			]);
			$this->setResponse($response);
			\App\Log::endProfile('POST|' . __METHOD__ . "|{$uri}", 'SMSNotifier');
		} catch (\Throwable $e) {
			\App\Log::error($e->__toString());
			return false;
		}

		return $this->isSuccess();
	}

	/**
	 * Add attachemnt.
	 *
	 * @param \App\Fields\File $file
	 *
	 * @return $this
	 */
	public function addAttachment(\App\Fields\File $file)
	{
		$this->attach[] = $file;

		return $this;
	}

	/**
	 * Get subject for MMS.
	 *
	 * @return string
	 */
	public function getSubject(): string
	{
		return \App\TextUtils::textTruncate(trim(strip_tags($this->get('message'))), 20) ?: $this->get('from');
	}

	/**
	 * Get smil text for MMS.
	 *
	 * @return string
	 */
	public function getSmil(): string
	{
		$image = $msg = ['region' => '', 'body' => ''];
		foreach ($this->attach as $file) {
			$image['region'] = '<region id="Image" height="100%" width="100%" fit="meet"/>';
			$image['body'] = '<img src="' . \App\Fields\File::getImageBaseData($file->getPath()) . '" region="Image"/>';
		}
		if (!$this->isEmpty('message')) {
			$msg['region'] = '<region id="Text" height="100%" width="100%" fit="scroll"/>';
			$msg['body'] = '<text src="data:text/plain;base64,' . base64_encode($this->get('message')) . '" region="Text"/>';
		}

		return "<smil>
			<head>
				<layout>
					{$image['region']}
					{$msg['region']}
				</layout>
			</head>
			<body>
				<par>
					{$image['body']}
					{$msg['body']}
				</par>
			</body>
		</smil>";
	}

	/** {@inheritdoc} */
	public function sendByRecord(\Vtiger_Record_Model $recordModel): bool
	{
		$this->setPhone($recordModel->get('phone'));
		$this->setMessage($recordModel->get('message'));
		$this->set('idx', $recordModel->getId());

		$image = $recordModel->get('image');
		if ($image && !\App\Json::isEmpty($image)) {
			foreach (\App\Json::decode($image) as $attach) {
				$file = \App\Fields\File::loadFromPath($attach['path']);
				$file->name = $attach['name'];
				$this->addAttachment($file);
			}
		}

		$result = $this->send() ?: $this->send(true);
		if ($result && !empty($this->responseData['list'][0]['id'])) {
			$recordModel->set('msgid', $this->responseData['list'][0]['id']);
		}

		return $result;
	}

	/** {@inheritdoc} */
	public function getEditFields(): array
	{
		$fields = [];
		foreach (['api_key', 'from'] as $fieldName) {
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
					$field['typeofdata'] = 'V~M';
					$field['maximumlength'] = '11';
					$field['purifyType'] = \App\Purifier::TEXT;
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
	 * @param array  $service
	 * @param string $type    Action name for werservice
	 *
	 * @return string
	 */
	public function getCallBackUrlByService(array $service, string $type): string
	{
		$callBackUrl = \App\Config::main('site_URL') . 'webservice.php?';
		$serviceId = (int) $service['server_id'];
		$server = \Settings_WebserviceApps_Record_Model::getInstanceById($serviceId);
		$apiKey = \App\Encryption::getInstance()->decrypt($server->get('api_key'));
		$params = [
			'_container' => 'SMS',
			'module' => 'SMSAPI',
			'action' => $type,
			'x-api-key' => $apiKey,
			'x-token' => $service['token'],
		];

		return $callBackUrl . http_build_query($params);
	}
}
