<?php
/**
 * Get record history file.
 *
 * @package Api
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Portal\BaseModule;

/**
 * Get record history class.
 */
class RecordHistory extends \Api\Core\BaseAction
{
	/** @var string[] Allowed request methods */
	public $allowedMethod = ['GET'];
	/**
	 * {@inheritdoc}
	 */
	public $allowedHeaders = ['x-raw-data', 'x-row-offset', 'x-row-limit'];
	/**
	 * Record model.
	 *
	 * @var \Vtiger_Record_Model
	 */
	protected $recordModel;

	/**
	 * {@inheritdoc}
	 */
	public function checkAction()
	{
		parent::checkAction();
		$moduleName = $this->controller->request->getModule();
		if ($this->controller->request->isEmpty('record', true) || !\App\Record::isExists($this->controller->request->getInteger('record'), $moduleName)) {
			throw new \Api\Core\Exception('Record doesn\'t exist', 404);
		}
		$this->recordModel = \Vtiger_Record_Model::getInstanceById($this->controller->request->getInteger('record'), $moduleName);
		if (!$this->recordModel->isViewable()) {
			throw new \Api\Core\Exception('No permissions to view record', 403);
		}
		return true;
	}

	/**
	 * Get related record list method.
	 *
	 * @return array
	 */
	public function get()
	{
		$pagingModel = new \Vtiger_Paging_Model();
		$limit = 100;
		if ($requestLimit = $this->controller->request->getHeader('x-row-limit')) {
			$limit = (int) $requestLimit;
		}
		$pagingModel->set('limit', $limit);
		if ($requestOffset = $this->controller->request->getHeader('x-row-offset')) {
			$pagingModel->set('page', (int) $requestOffset);
		}
		$recentActivities = \ModTracker_Record_Model::getUpdates($this->controller->request->getInteger('record'), $pagingModel, 'changes');
		$response = [];
		$isRawData = $this->isRawData();
		foreach ($recentActivities as $recordModel) {
			$row = [
				'time' => $recordModel->get('changedon'),
				'owner' => $recordModel->getModifiedBy()->getDisplayName(),
				'status' => \App\Language::translate($recordModel->getStatusLabel(), 'ModTracker'),
			];
			if ($isRawData) {
				$row['rawOwner'] = $recordModel->getModifiedBy()->getId();
				$row['rawStatus'] = $recordModel->getStatusLabel();
			}
			if ($recordModel->isCreate() || $recordModel->isUpdate() || $recordModel->isTransferEdit()) {
				$data = [];
				foreach ($recordModel->getFieldInstances() as $fieldModel) {
					if ($fieldModel && ($fieldInstance = $fieldModel->getFieldInstance()) && $fieldInstance->isViewable() && 5 !== $fieldModel->getFieldInstance()->getDisplayType()) {
						$fieldName = $fieldInstance->getName();
						$data[$fieldName]['from'] = $fieldInstance->getUITypeModel()->getHistoryDisplayValue($fieldModel->get('prevalue'), $recordModel, true);
						$data[$fieldName]['to'] = $fieldInstance->getUITypeModel()->getHistoryDisplayValue($fieldModel->get('postvalue'), $recordModel, true);
						if ($isRawData) {
							$data[$fieldName]['rawFrom'] = $fieldModel->get('prevalue');
							$data[$fieldName]['rawTo'] = $fieldModel->get('postvalue');
						}
					}
				}
				$row['data'] = $data;
			} elseif ($recordModel->isRelationLink() || $recordModel->isRelationUnLink()) {
				$relationInstance = $recordModel->getRelationInstance();
				$row['data'] = [
					'targetModule' => $relationInstance->get('targetmodule'),
					'targetLabel' => $relationInstance->getValue(),
				];
				if ($isRawData) {
					$row['data']['targetId'] = $relationInstance->get('targetid');
				}
			}
			if ($row) {
				$response[] = $row;
			}
		}
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
