<?php

class Vendors_DetailView_Model extends Vtiger_DetailView_Model
{
	/**
	 * {@inheritdoc}
	 */
	public function getDetailViewRelatedLinks()
	{
		$relatedLinks = parent::getDetailViewRelatedLinks();
		if (Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModulePermission('OpenStreetMap')) {
			$relatedLinks[] = [
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => 'LBL_MAP',
				'linkurl' => $this->getRecord()->getDetailViewUrl() . '&mode=showOpenStreetMap',
				'linkicon' => '',
			];
		}
		return $relatedLinks;
	}
}
