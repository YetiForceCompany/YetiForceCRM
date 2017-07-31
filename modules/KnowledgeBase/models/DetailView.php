<?php

/**
 * Detail View Model for KnowledgeBase
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Krzysztof Gastołek <krzysztof.gastolek@wars.pl>
 */
class KnowledgeBase_DetailView_Model extends Vtiger_DetailView_Model
{

	public function getDetailViewLinks($linkParams)
	{
		$recordModel = $this->getRecord();
		$recordId = $recordModel->get('id');
		$moduleName = $recordModel->getModuleName();
		$relatedLinkEntries = [
			[
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => \App\Language::translate('LBL_RECORD_PREVIEW', $moduleName),
				'linkKey' => 'LBL_RECORD_PREVIEW',
				'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showPreview',
				'linkicon' => '',
				'related' => 'Summary'
			],
			[
				'linktype' => 'DETAILVIEWBASIC',
				'linkurl' => 'javascript:KnowledgeBase_Popup_Js.getInstance().showPresentationContent(' . $recordId . ');',
				'linkicon' => 'glyphicon glyphicon-resize-full',
				'title' => \App\Language::translate('LBL_FULL_SCREEN', $moduleName),
				'linkhint' => \App\Language::translate('LBL_FULL_SCREEN', $moduleName)
			]
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
