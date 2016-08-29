<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class VTUpdateWorkTime extends VTTask
{

	public $executeImmediately = false;

	public function getFieldNames()
	{
		return [];
	}

	public function doTask($entity)
	{
		if (!vglobal('workflowIdsAlreadyDone')) {
			vglobal('workflowIdsAlreadyDone', []);
		}
		$globalIds = vglobal('workflowIdsAlreadyDone');
		$db = PearDatabase::getInstance();
		$referenceIds = [];
		$referenceName = OSSTimeControl_Record_Model::$referenceFieldsToTime;

		foreach ($referenceName as $name) {
			if ($entity->get($name)) {
				$parts = explode('x', $entity->get($name));
				$referenceIds[$parts[1]] = $name;
			}
		}
		$delta = \includes\utils\Json::decode($this->getContents($entity));
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
		include('user_privileges/moduleHierarchy.php');
		$modulesHierarchy = array_keys($modulesHierarchy);
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
	 * @param <Object> $entity
	 * @return <String> contents
	 */
	public function getContents($entity, $entityCache = false)
	{
		if (!$this->contents && is_object($entity)) {
			$moduleName = $entity->getModuleName();
			if ($entity->mode != 'delete') {
				$parts = explode('x', $entity->getId());
				$vtEntityDelta = new VTEntityDelta();
				$delta = $vtEntityDelta->getEntityDelta($moduleName, $parts[1], true);
			} else {
				$delta = $entity->getData();
			}
			$delta = array_intersect_key($delta, array_flip(OSSTimeControl_Record_Model::$referenceFieldsToTime));
			$this->contents = \includes\utils\Json::encode($delta);
		}
		return $this->contents;
	}
}
