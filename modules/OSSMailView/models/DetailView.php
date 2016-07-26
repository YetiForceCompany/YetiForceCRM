<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class OSSMailView_DetailView_Model extends Vtiger_DetailView_Model
{

	public function getDetailViewLinks($linkParams)
	{
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$recordModel = $this->getRecord();
		$linkModelList = parent::getDetailViewLinks($linkParams);
		unset($linkModelList['DETAILVIEWBASIC']);

		$moduleModel = Vtiger_Module_Model::getInstance('OSSMail');
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
		if ($permission && AppConfig::main('isActiveSendingMails') && Users_Privileges_Model::isPermitted('OSSMail')) {
			$recordId = $recordModel->getId();
			if ($currentUserModel->get('internal_mailer') == 1) {
				$config = OSSMail_Module_Model::getComposeParameters();
				$url = OSSMail_Module_Model::getComposeUrl();

				$detailViewLinks[] = [
					'linktype' => 'DETAILVIEWBASIC',
					'linklabel' => '',
					'linkhint' => 'LBL_REPLY',
					'linkdata' => ['url' => $url . '&mid=' . $recordId . '&type=reply', 'popup' => $config['popup']],
					'linkimg' => Yeti_Layout::getLayoutFile('modules/OSSMailView/previewReply.png'),
					'linkclass' => 'sendMailBtn'
				];
				$detailViewLinks[] = [
					'linktype' => 'DETAILVIEWBASIC',
					'linklabel' => '',
					'linkhint' => 'LBL_REPLYALLL',
					'linkdata' => ['url' => $url . '&mid=' . $recordId . '&type=replyAll', 'popup' => $config['popup']],
					'linkimg' => Yeti_Layout::getLayoutFile('modules/OSSMailView/previewReplyAll.png'),
					'linkclass' => 'sendMailBtn'
				];
				$detailViewLinks[] = [
					'linktype' => 'DETAILVIEWBASIC',
					'linklabel' => '',
					'linkhint' => 'LBL_FORWARD',
					'linkdata' => ['url' => $url . '&mid=' . $recordId . '&type=forward', 'popup' => $config['popup']],
					'linkicon' => 'glyphicon glyphicon-share-alt',
					'linkclass' => 'sendMailBtn'
				];
			} else {
				$detailViewLinks[] = [
					'linktype' => 'DETAILVIEWBASIC',
					'linkhref' => true,
					'linklabel' => '',
					'linkhint' => 'LBL_REPLY',
					'linkurl' => OSSMail_Module_Model::getExternalUrlForWidget($recordModel, 'reply'),
					'linkimg' => Yeti_Layout::getLayoutFile('modules/OSSMailView/previewReply.png'),
					'linkclass' => 'sendMailBtn'
				];
				$detailViewLinks[] = [
					'linktype' => 'DETAILVIEWBASIC',
					'linkhref' => true,
					'linklabel' => '',
					'linkhint' => 'LBL_REPLYALLL',
					'linkurl' => OSSMail_Module_Model::getExternalUrlForWidget($recordModel, 'replyAll'),
					'linkimg' => Yeti_Layout::getLayoutFile('modules/OSSMailView/previewReplyAll.png'),
					'linkclass' => 'sendMailBtn'
				];
				$detailViewLinks[] = [
					'linktype' => 'DETAILVIEWBASIC',
					'linkhref' => true,
					'linklabel' => '',
					'linkhint' => 'LBL_FORWARD',
					'linkurl' => OSSMail_Module_Model::getExternalUrlForWidget($recordModel, 'forward'),
					'linkicon' => 'glyphicon glyphicon-share-alt',
					'linkclass' => 'sendMailBtn'
				];
			}

			if (Users_Privileges_Model::isPermitted('OSSMailView', 'PrintMail')) {
				$detailViewLinks[] = [
					'linktype' => 'DETAILVIEWBASIC',
					'linklabel' => '',
					'linkhint' => 'LBL_PRINT',
					'linkurl' => 'javascript:OSSMailView_Detail_Js.printMail();',
					'linkicon' => 'glyphicon glyphicon-print'
				];
			}
			foreach ($detailViewLinks as $detailViewLink) {
				$linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($detailViewLink);
			}
		}
		$linkModelDetailViewList = $linkModelList['DETAILVIEW'];
		$countOfList = count($linkModelDetailViewList);
		for ($i = 0; $i < $countOfList; $i++) {
			$linkModel = $linkModelDetailViewList[$i];
			if ($linkModel->get('linklabel') == 'LBL_DUPLICATE') {
				unset($linkModelList['DETAILVIEW'][$i]);
				break;
			}
		}
		return $linkModelList;
	}
}
