<?php

/**
 * OSSEmployees record model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSEmployees_Record_Model extends Vtiger_Record_Model
{
	/**
	 * Function returns the details of Employees Hierarchy.
	 *
	 * @return <Array>
	 */
	public function getEmployeeHierarchy()
	{
		$focus = CRMEntity::getInstance($this->getModuleName());
		$hierarchy = $focus->getEmployeeHierarchy($this->getId());
		foreach ($hierarchy['entries'] as $employeeId => $employeeInfo) {
			preg_match('/<a href="+/', $employeeInfo[0], $matches);
			if (null !== $matches) {
				preg_match('/[.\s]+/', $employeeInfo[0], $dashes);
				preg_match('/<a(.*)>(.*)<\\/a>/i', $employeeInfo[0], $name);
				if (empty($name[2])) {
					$label = $employeeInfo[0];
				} else {
					$label = $name[2];
				}
				$recordModel = Vtiger_Record_Model::getCleanInstance('OSSEmployees');
				$recordModel->setId($employeeId);
				$hierarchy['entries'][$employeeId][0] = $dashes[0] . '<a href=' . $recordModel->getDetailViewUrl() . '>' . $label . '</a>';
			}
		}
		return $hierarchy;
	}
}
