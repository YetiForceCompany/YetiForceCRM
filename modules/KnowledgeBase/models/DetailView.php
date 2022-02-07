<?php

/**
 * Detail View Model for KnowledgeBase.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Krzysztof Gastołek <krzysztof.gastolek@wars.pl>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
class KnowledgeBase_DetailView_Model extends Vtiger_DetailView_Model
{
	/** {@inheritdoc} */
	public function getDetailViewLinks(array $linkParams): array
	{
		if ($this->getRecord()->isReadOnly() || \App\RequestUtil::getBrowserInfo()->ie) {
			return parent::getDetailViewLinks($linkParams);
		}
		$recordModel = $this->getRecord();
		$moduleName = $recordModel->getModuleName();
		$relatedLinkEntries = [
			[
				'linktype' => 'DETAIL_VIEW_ADDITIONAL',
				'linkdata' => [
					'id' => $recordModel->getId(),
					'module-name' => $moduleName
				],
				'vueId' => 'ArticlePreview',
				'linkicon' => 'fas fa-expand',
				'title' => \App\Language::translate('LBL_GO_TO_PREVIEW', $moduleName),
				'linkhint' => \App\Language::translate('LBL_GO_TO_PREVIEW', $moduleName),
				'linkclass' => 'btn-outline-dark btn-sm js-show-article-preview',
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
