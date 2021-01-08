<?php

/**
 * Communication between servers file.
 *
 * @package 	tasks
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Sołek <a.solek@yetiforce.com>
 */
/**
 * Communication between servers class.
 */
class VTWebhook extends VTTask
{
	/** @var bool Performs the task immediately after saving. */
	public $executeImmediately = true;

	/**
	 * {@inheritdoc}
	 */
	public function getFieldNames()
	{
		return ['url', 'login', 'password', 'fields', 'typedata', 'format'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function doTask($recordModel)
	{
		if (empty($this->url) || empty($this->format) || empty($this->typedata)) {
			return;
		}
		$params = $recordModelData = [];
		$fields = $types = false;
		if (!empty($this->fields)) {
			if (!\is_array($this->fields)) {
				$fields = [$this->fields];
			} else {
				$fields = $this->fields;
			}
		}
		if (!\is_array($this->typedata)) {
			$types = [$this->typedata];
		} else {
			$types = $this->typedata;
		}
		$fieldParams = $fields ?: array_keys($recordModel->getModule()->getFields());
		foreach ($types as $type) {
			foreach ($fieldParams as $fieldName) {
				if ('data' === $type) {
					$recordModelData[$type][] = $recordModel->getDisplayValue($fieldName, $recordModel->getId(), true);
				} elseif ('changes' === $type) {
					$recordModelData[$type][] = $recordModel->getPreviousValue($fieldName) ?: '';
				} elseif ('rawData' === $type) {
					$recordModelData[$type][] = $recordModel->get($fieldName);
				}
			}
		}
		if (!empty($this->format)) {
			$params[$this->format] = $recordModelData;
		}
		$params['auth'] = [$this->login, $this->password];
		$params['date'] = date('Y-m-d H:i:s');
		\App\Log::beginProfile("POST|VTWebhook::doTask|{$this->url}", 'com_vtiger_workflow\tasks\VTWebhook');
		(new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('POST', $this->url, $params);
		\App\Log::endProfile("POST|VTWebhook::doTask|{$this->url}", 'com_vtiger_workflow\tasks\VTWebhook');
	}
}
