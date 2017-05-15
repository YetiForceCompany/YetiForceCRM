<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 2.0 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class OSSEmployees_Record_Model extends Vtiger_Record_Model
{

	/**
	 * Function returns the details of Employees Hierarchy
	 * @return <Array>
	 */
	public function getEmployeeHierarchy()
	{
		$focus = CRMEntity::getInstance($this->getModuleName());
		$hierarchy = $focus->getEmployeeHierarchy($this->getId());
		$i = 0;
		foreach ($hierarchy['entries'] as $employeeId => $employeeInfo) {
			preg_match('/<a href="+/', $employeeInfo[0], $matches);
			if ($matches != null) {
				preg_match('/[.\s]+/', $employeeInfo[0], $dashes);
				preg_match("/<a(.*)>(.*)<\/a>/i", $employeeInfo[0], $name);

				$recordModel = Vtiger_Record_Model::getCleanInstance('OSSEmployees');
				$recordModel->setId($employeeId);
				$hierarchy['entries'][$employeeId][0] = $dashes[0] . "<a href=" . $recordModel->getDetailViewUrl() . ">" . $name[2] . "</a>";
			}
		}
		return $hierarchy;
	}
}
