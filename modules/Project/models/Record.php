<?php

/**
 * Project record model class.
 *
 * @package   Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
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
	 * Get estimated work time.
	 *
	 * @return float
	 */
	public function getEstimatedWorkTime()
	{
		return $this->getModule()->calculateEstimatedWorkTime($this->getId());
	}
}
