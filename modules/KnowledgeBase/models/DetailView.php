<?php
/**
 * Detail View Model for KnowledgeBase
 * @package YetiForce.Model
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
			'linklabel' => vtranslate('LBL_RECORD_PREVIEW', $moduleName),
			'linkKey' => 'LBL_RECORD_PREVIEW',
			'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showPreview',
			'linkicon' => '',
			'related' => 'Summary'
		];
		$relatedLink = Vtiger_Link_Model::getInstanceFromValues($relatedLinkEntry);
		$linkModelList = parent::getDetailViewLinks($linkParams);
		$linkModelList[$relatedLink->getType()][] = $relatedLink;
		
		return $linkModelList;
	}
}
