<?php

/**
 * OSSEmployees DetailView model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSEmployees_DetailView_Model extends Vtiger_DetailView_Model
{
	/**
	 * {@inheritdoc}
	 */
	public function getDetailViewLinks($linkParams)
	{
		$recordModel = $this->getRecord();
		$linkModelLists = parent::getDetailViewLinks($linkParams);

		$linkURL = 'index.php?module=OSSEmployees&view=EmployeeHierarchy&record=' . $recordModel->getId();
		$linkModel = [
			'linktype' => 'LISTVIEWMASSACTION',
			'linkhint' => 'LBL_SHOW_EMPLOYEES_HIERARCHY',
			'linkurl' => 'javascript:OSSEmployees_Detail_Js.triggerEmployeeHierarchy("' . $linkURL . '");',
			'linkicon' => 'fas fa-user',
			'linkclass' => 'btn-outline-dark btn-sm'
		];
		$linkModelLists['DETAIL_VIEW_BASIC'][] = Vtiger_Link_Model::getInstanceFromValues($linkModel);

		return $linkModelLists;
	}
}
