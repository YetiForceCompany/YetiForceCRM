<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class VTUpdateWorkTime extends VTTask
{

	public $executeImmediately = false;

	public function getFieldNames()
	{
		return [];
	}

	/**
	 * Execute task
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel)
	{
		if (!vglobal('workflowIdsAlreadyDone')) {
			vglobal('workflowIdsAlreadyDone', []);
		}
		$globalIds = vglobal('workflowIdsAlreadyDone');
		$db = PearDatabase::getInstance();
		$referenceIds = [];
		$referenceName = OSSTimeControl_Record_Model::$referenceFieldsToTime;

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

		$referenceIds = array_diff_key($referenceIds, array_flip($globalIds));
		$metasData = vtlib\Functions::getCRMRecordMetadata(array_keys($referenceIds));
		$modulesHierarchy = array_keys(App\ModuleHierarchy::getModulesHierarchy());
		foreach ($metasData as $referenceId => $metaData) {
			if (!$metaData['delete'] && in_array($metaData['setype'], $modulesHierarchy)) {
				OSSTimeControl_Record_Model::recalculateTimeControl($referenceId, $referenceIds[$referenceId]);
				$globalIds[] = $referenceId;
			}
		}
		vglobal('workflowIdsAlreadyDone', $globalIds);
	}

	/**
	 * Function to get contents of this task
	 * @param Vtiger_Record_Model $recordModel
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
