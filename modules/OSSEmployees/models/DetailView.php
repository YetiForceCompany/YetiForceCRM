<?php

/**
 * OSSEmployees DetailView model class
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
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
