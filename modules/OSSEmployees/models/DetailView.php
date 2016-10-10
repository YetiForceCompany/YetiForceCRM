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
		$linkModelLists = parent::getDetailViewLinks($linkParams);

		$linkURL = 'index.php?module=OSSEmployees&view=EmployeeHierarchy&record=' . $recordModel->getId();
		$linkModel = [
			'linktype' => 'LISTVIEWMASSACTION',
			'linkhint' => 'LBL_SHOW_EMPLOYEES_HIERARCHY',
			'linkurl' => 'javascript:OSSEmployees_Detail_Js.triggerEmployeeHierarchy("' . $linkURL . '");',
			'linkicon' => 'glyphicon glyphicon-user'
		];
		$linkModelLists['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($linkModel);
		return $linkModelLists;
	}
}
