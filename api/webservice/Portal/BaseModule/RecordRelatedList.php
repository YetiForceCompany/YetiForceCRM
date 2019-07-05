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
		$rawData = $records = $headers = [];
		foreach ($relationListView->getHeaders() as $fieldName => $fieldModel) {
			$headers[$fieldName] = \App\Language::translate($fieldModel->getFieldLabel(), $fieldModel->getModuleName());
		}
		foreach ($relationListView->getEntries($pagingModel) as $id => $relatedRecordModel) {
			foreach ($headers as $fieldName => $fieldModel) {
				$records[$id][$fieldName] = $relatedRecordModel->getDisplayValue($fieldName, $id, true);
				if ($this->isRawData()) {
					$rawData[$id][$fieldName] = $relatedRecordModel->get($fieldName);
				}
			}
		}
		$rowsCount = \count($records);
		return [
			'headers' => $headers,
			'records' => $records,
			'rawData' => $rawData,
			'count' => $rowsCount,
			'isMorePages' => $rowsCount === $limit,
		];
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
