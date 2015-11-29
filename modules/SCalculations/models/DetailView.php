<?php

/**
 * DetailView Class for SCalculations
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class SCalculations_DetailView_Model extends Vtiger_DetailView_Model
{

	public function getDetailViewLinks($linkParams)
	{
		$linkModelList = parent::getDetailViewLinks($linkParams);
		$recordModel = $this->getRecord();
		$moduleName = $recordModel->getModuleName();
		$lockEdit = Users_Privileges_Model::checkLockEdit($moduleName, $recordId);
		$openRecord = Users_Privileges_Model::isPermitted($moduleName, 'OpenRecord', $recordId);
		if ((Users_Privileges_Model::isPermitted($moduleName, 'EditView', $recordId) && !$lockEdit) || $openRecord) {
			$basicActionLink = [
				'linktype' => 'DETAILVIEW',
				'linklabel' => 'LBL_SET_RECORD_STATUS',
				'linkurl' => '#',
				'linkdata' => ['url' => $recordModel->getModalUrl()],
				'linkicon' => 'glyphicon glyphicon-modal-window',
				'linkclass' => 'showModal'
			];
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}
		return $linkModelList;
	}
}
