<?php

/**
 * CallHistory DetailView model class 
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class CallHistory_DetailView_Model extends Vtiger_DetailView_Model
{

	public function getDetailViewLinks($linkParams)
	{
		$linkTypes = array('DETAILVIEWBASIC', 'DETAILVIEW');
		$moduleModel = $this->getModule();
		$linkModelListDetails = Vtiger_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);
		//Mark all detail view basic links as detail view links.
		//Since ui will be look ugly if you need many basic links
		$detailViewBasiclinks = $linkModelListDetails['DETAILVIEWBASIC'];
		unset($linkModelListDetails['DETAILVIEWBASIC']);

		/*
		  if(Users_Privileges_Model::isPermitted($moduleName, 'Delete', $recordId) && $recordPermissionToEditView) {
		  $deletelinkModel = array(
		  'linktype' => 'DETAILVIEW',
		  'linklabel' => sprintf("%s %s", getTranslatedString('LBL_DELETE', $moduleName), \App\Language::translate('SINGLE_'. $moduleName, $moduleName)),
		  'linkurl' => 'javascript:Vtiger_Detail_Js.deleteRecord("'.$recordModel->getDeleteUrl().'")',
		  'linkicon' => ''
		  );
		  $linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($deletelinkModel);
		  }
		 */
		if (!empty($detailViewBasiclinks)) {
			foreach ($detailViewBasiclinks as $linkModel) {
				// Remove view history, needed in vtiger5 to see history but not in vtiger6
				if ($linkModel->linklabel == 'View History') {
					continue;
				}
				$linkModelList['DETAILVIEW'][] = $linkModel;
			}
		}

		return $linkModelList;
	}
}
