<?php

class Vendors_DetailView_Model extends Vtiger_DetailView_Model
{

	public function getDetailViewRelatedLinks()
	{
		$recordModel = $this->getRecord();
		$relatedLinks = parent::getDetailViewRelatedLinks();
		$openStreetMapModuleModel = Vtiger_Module_Model::getInstance('OpenStreetMap');
		if ($openStreetMapModuleModel->isActive()) {
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
