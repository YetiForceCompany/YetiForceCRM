<?php

/**
 * Record Class for Partners.
 *
 * @package   Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Partners_Record_Model extends Vtiger_Record_Model
{
	/**
	 * Function returns the details of hierarchy.
	 *
	 * @return array
	 */
	public function getHierarchy(): array
	{
		$focus = CRMEntity::getInstance($this->getModuleName());
		$hierarchy = $focus->getHierarchy($this->getId());
		foreach ($hierarchy['entries'] as $id => $storageInfo) {
			preg_match('/<a href="+/', $storageInfo[0], $matches);
			if (!empty($matches)) {
				preg_match('/[.\s]+/', $storageInfo[0], $dashes);
				preg_match('/<a(.*)>(.*)<\\/a>/i', $storageInfo[0], $name);

				$recordModel = Vtiger_Record_Model::getCleanInstance($this->getModuleName());
				$recordModel->setId($id);
				$hierarchy['entries'][$id][0] = $dashes[0] . '<a href=' . $recordModel->getDetailViewUrl() . '>' . $name[2] . '</a>';
			}
		}
		return $hierarchy;
	}
}
