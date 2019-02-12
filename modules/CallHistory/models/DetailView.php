<?php

/**
 * CallHistory DetailView model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class CallHistory_DetailView_Model extends Vtiger_DetailView_Model
{
	public function getDetailViewLinks($linkParams)
	{
		$linkTypes = ['DETAIL_VIEW_ADDITIONAL', 'DETAIL_VIEW_BASIC'];
		$moduleModel = $this->getModule();
		$linkModelListDetails = Vtiger_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);
		//Mark all detail view basic links as detail view links.
		//Since ui will be look ugly if you need many basic links
		$detailViewBasiclinks = $linkModelListDetails['DETAIL_VIEW_ADDITIONAL'] ?? [];
		$linkModelList = [
			'DETAIL_VIEW_BASIC' => [],
			'DETAIL_VIEW_ADDITIONAL' => [],
			'DETAIL_VIEW_EXTENDED' => [],
			'DETAILVIEWTAB' => [],
			'DETAILVIEWRELATED' => []
		];
		if (!empty($detailViewBasiclinks)) {
			foreach ($detailViewBasiclinks as $linkModel) {
				// Remove view history, needed in vtiger5 to see history but not in vtiger6
				if ($linkModel->linklabel == 'View History') {
					continue;
				}
				$linkModelList['DETAIL_VIEW_BASIC'][] = $linkModel;
			}
		}
		return $linkModelList;
	}
}
