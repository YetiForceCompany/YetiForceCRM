<?php

/**
 * VTUpdateWorkTime Class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class VTUpdateWorkTime extends VTTask
{
	public static $workflowIdsAlreadyDone = [];
	public $executeImmediately = false;

	public function getFieldNames()
	{
		return [];
	}

	/**
	 * Execute task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel)
	{
		$referenceFields = $recordModel->getModule()->getFieldsByReference();
		$referenceIds = [];
		foreach ($referenceFields as $fieldModel) {
			if ($value = $recordModel->get($fieldModel->getName())) {
				$referenceIds[$value] = $fieldModel->getName();
			}
		}
		$delta = \App\Json::decode($this->getContents($recordModel));
		if (\is_array($delta)) {
			foreach ($delta as $fieldName => $values) {
				if (!empty($values) && !\is_array($values)) {
					$referenceIds[$values] = $fieldName;
				} elseif (\is_array($values) && $values['oldValue']) {
					$referenceIds[$values['oldValue']] = $fieldName;
				}
				if (\is_array($values) && $values['currentValue']) {
					$referenceIds[$values['currentValue']] = $fieldName;
				}
			}
		}
		$referenceIds = array_diff_key($referenceIds, array_flip(static::$workflowIdsAlreadyDone));
		$metasData = vtlib\Functions::getCRMRecordMetadata(array_keys($referenceIds));
		$modulesHierarchy = array_keys(App\ModuleHierarchy::getModulesHierarchy());
		foreach ($metasData as $referenceId => $metaData) {
			if (0 === ((int) $metaData['deleted']) && \in_array($metaData['setype'], $modulesHierarchy)) {
				(new \OSSTimeControl_TimeCounting_Model($metaData['setype'], $referenceId, $referenceIds[$referenceId]))->recalculateTimeControl();
				static::$workflowIdsAlreadyDone[] = $referenceId;
			}
		}
	}

	/**
	 * Function to get contents of this task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return string contents
	 */
	public function getContents($recordModel)
	{
		if (!$this->contents && \is_object($recordModel)) {
			$delta = array_intersect_key($recordModel->getPreviousValue(), $recordModel->getModule()->getFieldsByReference());
			$this->contents = \App\Json::encode($delta);
		}
		return $this->contents;
	}
}
