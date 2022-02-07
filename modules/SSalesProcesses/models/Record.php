<?php

/**
 * Record Class for SSalesProcesses.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class SSalesProcesses_Record_Model extends Vtiger_Record_Model
{
	/**
	 * Function returns the details of IStorages Hierarchy.
	 *
	 * @return array
	 */
	public function getHierarchy()
	{
		$focus = CRMEntity::getInstance($this->getModuleName());
		$hierarchy = $focus->getHierarchy($this->getId());
		foreach ($hierarchy['entries'] as $storageId => $storageInfo) {
			preg_match('/<a href="+/', $storageInfo[0], $matches);
			if (!empty($matches)) {
				preg_match('/[.\s]+/', $storageInfo[0], $dashes);
				preg_match('/<a(.*)>(.*)<\\/a>/i', $storageInfo[0], $name);

				$recordModel = Vtiger_Record_Model::getCleanInstance('SSalesProcesses');
				$recordModel->setId($storageId);
				$hierarchy['entries'][$storageId][0] = $dashes[0] . '<a href=' . $recordModel->getDetailViewUrl() . '>' . $name[2] . '</a>';
			}
		}
		return $hierarchy;
	}
}
