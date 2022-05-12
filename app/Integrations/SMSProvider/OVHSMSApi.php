<?php

namespace App\Integrations\SMSProvider;

use Ovh\Api;

class OVHSMSApi extends Provider
{
	/** {@inheritdoc} */
	protected $name = 'OVHSMSApi';

	/** @var string Encoding */
	public $encoding = 'utf-8';

	/** @var string Format */
	public $format = 'json';

	/** @var \GuzzleHttp\Psr7\Response Response object */
	private $response;

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
	 * Set phone number.
	 *
	 * @param string $phoneNumber
	 *
	 * @return $this
	 */
	public function setPhone(string $phoneNumber): self
	{
		$phoneNumber = preg_replace_callback('/[^\d]/s', fn () => '', $phoneNumber);
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
	 * Check if the message was sent successfully.
	 *
	 * @return bool
	 */
	public function isSuccess(): bool
	{
		return $this->response && 200 === $this->response->getStatusCode() && empty($this->responseData['error']);
	}

	/** {@inheritdoc} */
	public function send(): bool
	{
		return $this->sendSMS();
	}

	/**
	 * Send SMS.
	 *
	 * @return bool
	 */
	public function sendSMS(): bool
	{
		try {
			$res = $this->post();
			$this->setResponse($res);
		} catch (\Throwable $e) {
			\App\Log::error($e->__toString());
			return false;
		}
		if ($res) {
			print_r(json_decode($res, true));
		}

		return $this->isSuccess();
	}

	/** {@inheritdoc} */
	public function sendByRecord(\Vtiger_Record_Model $recordModel): bool
	{
		$this->setPhone($recordModel->get('phone'));
		$this->setMessage($recordModel->get('message'));
		$this->set('idx', $recordModel->getId());

		$result = $this->send() ?: $this->send(true);
		if ($result && !empty($this->responseData['list'][0]['id'])) {
			$recordModel->set('msgid', $this->responseData['list'][0]['id']);
		}

		return $result;
	}

	/**
	 * Send SMS by method post.
	 *
	 * @return string
	 */
	public function post(): string
	{
		$headers = [];
		$headers['Content-Type'] = 'application/json; charset=utf-8';
		$headers['X-Ovh-Application'] = $this->get('app_key');
		$headers['X-Ovh-Timestamp'] = time();
		$headers['X-Ovh-Signature'] = $this->getSignature();
		$headers['X-Ovh-Consumer'] = $this->get('consumer_key');

		$client = new \GuzzleHttp\Client(\App\RequestHttp::getOptions());
		$res = $client->request('POST', $this->getEndpointBase() . $this->getPath(), [
			'headers' => $headers,
			'verify' => false
		]);

		return json_encode($res);
	}

	/**
	 * Get signature to auth.
	 *
	 * @return string
	 */
	public function getSignature(): string
	{
		$sign = $this->get('app_secret') . '+' . $this->get('consumer_key') . '+' . 'POST' . '+' . $this->getEndpointBase() . $this->getPath() . '+' . $this->getContent() . '+' . time();
		print_r($this->getContent());
		return '$1$' . sha1($sign);
	}

	/**
	 * Get path to endpoint.
	 *
	 * @param [type] $serviceName
	 *
	 * @return void
	 */
	public function getPath($serviceName = null)
	{
		if (null === $serviceName) {
			return '/sms/*/jobs';
		}
		return "'/sms'/" . $serviceName . '/jobs';
	}

	/**
	 * Get Messages and receivers.
	 *
	 * @return void
	 */
	public function getContent()
	{
		$content = (object) [
			'message' => $this->get('message'),
			'receivers' => ['+48796984170'],
		];

		return json_encode($content, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}

	/** {@inheritdoc} */
	public function getEditFields(): array
	{
		$fields = [];
		foreach (['app_key', 'app_secret', 'consumer_key', 'from'] as $fieldName) {
			$fields[$fieldName] = $this->getFieldInstanceByName($fieldName);
		}

		return $fields;
	}

	/**
	 * Function return base of API url.
	 *
	 * @return string
	 */
	public function getEndpointBase(): string
	{
		return 'https://eu.api.ovh.com/1.0';
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
			'format' => $this->format,
		];
		foreach ($this->getRequiredParams() as $key) {
			if ('' !== $this->get($key)) {
				$params[$key] = $this->get($key);
			}
		}

		return $params;
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
				case 'app_key':
					$field['uitype'] = 99;
					$field['label'] = 'FL_APP_KEY';
					$field['purifyType'] = \App\Purifier::ALNUM;
					$field['fromOutsideList'] = true;
					$field['maximumlength'] = '100';
					$field['fieldvalue'] = $this->has($name) ? $this->get($name) : '';
					break;
				case 'app_secret':
					$field['uitype'] = 99;
					$field['label'] = 'FL_SECRET_KEY';
					$field['purifyType'] = \App\Purifier::ALNUM;
					$field['fromOutsideList'] = true;
					$field['maximumlength'] = '100';
					$field['fieldvalue'] = $this->has($name) ? $this->get($name) : '';
					break;
				case 'consumer_key':
					$field['uitype'] = 99;
					$field['label'] = 'FL_CONSUMER_KEY';
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
}
