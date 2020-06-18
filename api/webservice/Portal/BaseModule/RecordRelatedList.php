<?php
/**
 * Get record related list file.
 *
 * @package Api
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Portal\BaseModule;

/**
 * Get record related list class.
 */
class RecordRelatedList extends \Api\Core\BaseAction
{
	/** @var string[] Allowed request methods */
	public $allowedMethod = ['GET'];
	/**
	 * {@inheritdoc}
	 */
	public $allowedHeaders = ['x-raw-data', 'x-row-offset', 'x-row-limit', 'x-fields', 'x-parent-id'];

	/**
	 * Get method.
	 *
	 * @return array
	 */
	public function get()
	{
		$recordModel = \Vtiger_Record_Model::getInstanceById($this->controller->request->getInteger('record'), $this->controller->request->getModule());
		$pagingModel = new \Vtiger_Paging_Model();
		$limit = 1000;
		if ($requestLimit = $this->controller->request->getHeader('x-row-limit')) {
			$limit = (int) $requestLimit;
		}
		$pagingModel->set('limit', $limit);
		if ($requestOffset = $this->controller->request->getHeader('x-row-offset')) {
			$pagingModel->set('page', (int) $requestOffset);
		}
		$relationListView = \Vtiger_RelationListView_Model::getInstance($recordModel, $this->controller->request->getByType('param', 'Alnum'));
		if ($requestFields = $this->controller->request->getHeader('x-fields')) {
			$relationListView->setFields(\array_merge(['id'], \App\Json::decode($requestFields)));
		}
		$response = [
			'headers' => [],
			'records' => [],
		];
		foreach ($relationListView->getHeaders() as $fieldName => $fieldModel) {
			$response['headers'][$fieldName] = \App\Language::translate($fieldModel->getFieldLabel(), $fieldModel->getModuleName());
		}
		$isRawData = $this->isRawData();
		foreach ($relationListView->getEntries($pagingModel) as $id => $relatedRecordModel) {
			foreach ($response['headers'] as $fieldName => $fieldModel) {
				$value = $relatedRecordModel->get($fieldName);
				$response['records'][$id][$fieldName] = $fieldModel->getUITypeModel()->getApiDisplayValue($value, $relatedRecordModel);
				if ($isRawData) {
					$response['rawData'][$id][$fieldName] = $value;
				}
			}
		}
		$response['count'] = \count($response['records']);
		$response['isMorePages'] = $response['count'] === $limit;
		return $response;
	}

	/**
	 * Check if you send raw data.
	 *
	 * @return bool
	 */
	protected function isRawData(): bool
	{
		return 1 === (int) $this->controller->headers['x-raw-data'];
	}
}
