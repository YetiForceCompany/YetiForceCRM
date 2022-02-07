<?php

/**
 * Record Class for Project.
 *
 * @package   Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class Project_Record_Model extends Vtiger_Record_Model
{
	/**
	 * Function returns the details of Hierarchy.
	 *
	 * @return array
	 */
	public function getHierarchy(): array
	{
		$focus = CRMEntity::getInstance($this->getModuleName());
		$hierarchy = $focus->getHierarchy($this->getId());
		foreach ($hierarchy['entries'] as $id => $info) {
			preg_match('/<a href="+/', $info[0], $matches);
			if (!empty($matches)) {
				preg_match('/[.\s]+/', $info[0], $dashes);
				preg_match('/<a(.*)>(.*)<\\/a>/i', $info[0], $name);
				$recordModel = Vtiger_Record_Model::getCleanInstance('Project');
				$recordModel->setId($id);
				$hierarchy['entries'][$id][0] = $dashes[0] . '<a href=' . $recordModel->getDetailViewUrl() . '>' . $name[2] . '</a>';
			}
		}
		return $hierarchy;
	}
}
