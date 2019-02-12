<?php

/**
 * Detail View Model for KnowledgeBase.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Krzysztof Gastołek <krzysztof.gastolek@wars.pl>
 */
class KnowledgeBase_DetailView_Model extends Vtiger_DetailView_Model
{
	/**
	 * {@inheritdoc}
	 */
	public function getDetailViewLinks($linkParams)
	{
		$recordModel = $this->getRecord();
		$moduleName = $recordModel->getModuleName();
		$relatedLinkEntries = [
			[
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => \App\Language::translate('LBL_RECORD_PREVIEW', $moduleName),
				'linkKey' => 'LBL_RECORD_PREVIEW',
				'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showPreview',
				'linkicon' => '',
				'related' => 'Summary',
			],
			[
				'linktype' => 'DETAIL_VIEW_ADDITIONAL',
				'linkurl' => 'javascript:KnowledgeBase_Detail_Js.showPresentation();',
				'linkicon' => 'fas fa-expand',
				'title' => \App\Language::translate('LBL_FULL_SCREEN', $moduleName),
				'linkhint' => \App\Language::translate('LBL_FULL_SCREEN', $moduleName),
				'linkclass' => 'btn-outline-dark btn-sm',
			],
		];
		$relatedLinks = [];
		foreach ($relatedLinkEntries as $relatedLinkEntry) {
			$relatedLinks[] = Vtiger_Link_Model::getInstanceFromValues($relatedLinkEntry);
		}
		$linkModelList = parent::getDetailViewLinks($linkParams);
		foreach ($relatedLinks as $relatedLink) {
			$linkModelList[$relatedLink->getType()][] = $relatedLink;
		}
		return $linkModelList;
	}
}
