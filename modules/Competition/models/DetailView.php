<?php

/**
 * Competition detail view model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Competition_DetailView_Model extends Vtiger_DetailView_Model
{
	/**
	 * {@inheritdoc}
	 */
	public function getDetailViewRelatedLinks()
	{
		$recordModel = $this->getRecord();
		$relatedLinks = parent::getDetailViewRelatedLinks();
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if ($userPrivilegesModel->hasModulePermission('OpenStreetMap')) {
			$relatedLinks[] = [
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => 'LBL_MAP',
				'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showOpenStreetMap',
				'linkicon' => '',
			];
		}
		return $relatedLinks;
	}
}
