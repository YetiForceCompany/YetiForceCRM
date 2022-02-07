<?php

/**
 * Webhook cron task file.
 *
 * @package 	WorkflowTask
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Sołek <a.solek@yetiforce.com>
 */
/**
 * Webhook cron task class.
 */
class Webhook extends VTTask
{
	/** @var bool Performs the task immediately after saving. */
	public $executeImmediately = true;

	/** {@inheritdoc} */
	public function getFieldNames()
	{
		return ['url', 'login', 'password', 'fields', 'typedata', 'format'];
	}

	/** {@inheritdoc} */
	public function doTask($recordModel)
	{
		if (empty($this->url) || empty($this->format) || empty($this->typedata)) {
			return;
		}
		$data = ['date' => date('Y-m-d H:i:s (T P)')];
		$fields = $this->fields ?: array_keys($recordModel->getModule()->getFields());
		foreach ($this->typedata as $type) {
			foreach ($fields as $fieldName) {
				switch ($type) {
					case 'data':
						$data[$type][$fieldName] = $recordModel->getDisplayValue($fieldName, $recordModel->getId(), true);
						break;
					case 'changes':
						if (false !== ($previousValue = $recordModel->getPreviousValue($fieldName))) {
							$data[$type][$fieldName] = ['before' => $previousValue, 'after' => $recordModel->get($fieldName)];
						}
						break;
					case 'rawData':
						$data[$type][$fieldName] = $recordModel->get($fieldName);
						break;
					default:
						break;
				}
			}
		}
		$params = [
			'verify' => false,
			"{$this->format}" => $data
		];
		if (!empty($this->login)) {
			$params['auth'] = [$this->login, $this->password];
		}
		try {
			\App\Log::beginProfile("POST|{$this->url}", 'Workflow|Webhook');
			(new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('POST', $this->url, $params);
			\App\Log::endProfile("POST|{$this->url}", 'Workflow|Webhook');
		} catch (\Throwable $ex) {
			\App\Log::warning('Error: ' . $this->url . ' | ' . $ex->__toString(), __CLASS__);
			return false;
		}
	}
}
