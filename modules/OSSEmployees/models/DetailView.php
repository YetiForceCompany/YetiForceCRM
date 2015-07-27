<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class OSSEmployees_DetailView_Model extends Vtiger_DetailView_Model
{

	public function getDetailViewLinks($linkParams)
	{

		$recordModel = $this->getRecord();
		$linkModelList = parent::getDetailViewLinks($linkParams);

		//TODO: update the database so that these separate handlings are not required
		$index = 0;
		foreach ($linkModelList['DETAILVIEW'] as $link) {
			if ($link->linklabel == 'View History' || $link->linklabel == 'Send SMS') {
				unset($linkModelList['DETAILVIEW'][$index]);
			} else if ($link->linklabel == 'LBL_SHOW_EMPLOYEES_HIERARCHY') {
				$linkURL = 'index.php?module=OSSEmployees&view=EmployeeHierarchy&record=' . $recordModel->getId();
				$link->linkurl = 'javascript:OSSEmployees_Detail_Js.triggerEmployeeHierarchy("' . $linkURL . '");';
				unset($linkModelList['DETAILVIEW'][$index]);
				$linkModelList['DETAILVIEW'][$index] = $link;
			}
			$index++;
		}
		return $linkModelList;
	}
}
