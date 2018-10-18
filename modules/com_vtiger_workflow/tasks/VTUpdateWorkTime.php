<?php

/**
 * VTUpdateWorkTime Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		$referenceName = OSSTimeControl_Record_Model::$referenceFieldsToTime;
		$referenceIds = [];
		foreach ($referenceName as $name) {
			if ($recordModel->get($name)) {
				$referenceIds[$recordModel->get($name)] = $name;
			}
		}
		$delta = \App\Json::decode($this->getContents($recordModel));
		if (is_array($delta)) {
			foreach ($delta as $fieldName => $values) {
				if (!empty($values) && !is_array($values)) {
					$referenceIds[$values] = $fieldName;
				} elseif (is_array($values) && $values['oldValue']) {
					$referenceIds[$values['oldValue']] = $fieldName;
				}
				if (is_array($values) && $values['currentValue']) {
					$referenceIds[$values['currentValue']] = $fieldName;
				}
			}
		}
		$referenceIds = array_diff_key($referenceIds, array_flip(static::$workflowIdsAlreadyDone));
		$metasData = vtlib\Functions::getCRMRecordMetadata(array_keys($referenceIds));
		$modulesHierarchy = array_keys(App\ModuleHierarchy::getModulesHierarchy());
		foreach ($metasData as $referenceId => $metaData) {
			if (((int) $metaData['deleted']) === 0 && in_array($metaData['setype'], $modulesHierarchy)) {
				OSSTimeControl_Record_Model::recalculateTimeControl($referenceId, $referenceIds[$referenceId]);
				static::$workflowIdsAlreadyDone[] = $referenceId;
			}
		}
	}

	/**
	 * Function to get contents of this task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return <String> contents
	 */
	public function getContents($recordModel)
	{
		if (!$this->contents && is_object($recordModel)) {
			$delta = array_intersect_key($recordModel->getPreviousValue(), array_flip(OSSTimeControl_Record_Model::$referenceFieldsToTime));

			$this->contents = \App\Json::encode($delta);
		}
		return $this->contents;
	}
}
