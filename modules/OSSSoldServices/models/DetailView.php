<?php

/**
 * DetailView Class for OSSSoldServices
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class OSSSoldServices_DetailView_Model extends Vtiger_DetailView_Model
{

	public function getDetailViewLinks($linkParams)
	{
		$linkModelList = parent::getDetailViewLinks($linkParams);
		$recordModel = $this->getRecord();
		$moduleName = $recordModel->getModuleName();

		$openRecord = Users_Privileges_Model::isPermitted($moduleName, 'OpenRecord', $recordId);
		if (($recordModel->isEditable() || $openRecord) && $recordModel->get('ssservicesstatus') == 'PLL_ACCEPTED') {
			$basicActionLink = [
				'linktype' => 'DETAILVIEW',
				'linklabel' => 'LBL_SET_RENEWAL',
				'linkurl' => '#',
				'linkdata' => ['url' => $recordModel->getEditFieldByModalUrl() . '&changeEditFieldByModal=osssoldservices_renew'],
				'linkicon' => 'glyphicon glyphicon-repeat',
				'linkclass' => 'showModal'
			];
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}
		return $linkModelList;
	}
}
