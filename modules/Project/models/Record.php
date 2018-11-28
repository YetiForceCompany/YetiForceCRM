<?php


class Project_Record_Model extends Vtiger_Record_Model
{
	/**
	 * Get children by parent ID.
	 *
	 * @return \Generator
	 */
	public function getChildren()
	{
		$instance = CRMEntity::getInstance($this->getModuleName());
		foreach ($instance->getChildren($this->getId()) as $projectInfo) {
			yield self::getInstanceById($projectInfo[$instance->table_index]);
		}
	}

	/**
	 * Check if the record model has a parent.
	 *
	 * @return bool
	 */
	public function hasParent(): bool
	{
		return !empty($this->get('parentid')) && $this->get('parentid') !== 0;
	}
}
