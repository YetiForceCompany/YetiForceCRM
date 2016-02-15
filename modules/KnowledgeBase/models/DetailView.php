<?php
/**
 * Detail View
 * @package YetiForce.Models
 * @license licenses/License.html
 * @author Krzysztof Gastołek <krzysztof.gastolek@wars.pl>
 */
class KnowledgeBase_DetailView_Model extends Vtiger_DetailView_Model
{
	public function getDetailViewLinks($linkParams)
	{
		$recordModel = $this->getRecord();
		$moduleName = $recordModel->getModuleName();
		$relatedLinkEntry = [
			'linktype' => 'DETAILVIEWTAB',
			'linklabel' => vtranslate('LBL_RECORD_SUMMARY', $moduleName),
			'linkKey' => 'LBL_RECORD_SUMMARY',
			'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showModuleSummaryView&requestMode=summary',
			'linkicon' => '',
			'related' => 'Summary'
		];
		$relatedLink = Vtiger_Link_Model::getInstanceFromValues($relatedLinkEntry);
		$linkModelList = parent::getDetailViewLinks($linkParams);
		$linkModelList[$relatedLink->getType()][] = $relatedLink;
		
		return $linkModelList;
	}
}
