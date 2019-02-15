<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Users_DetailView_Model extends Vtiger_DetailView_Model
{
	/**
	 * Function to get the detail view links (links and widgets).
	 *
	 * @param array $linkParams - parameters which will be used to calicaulate the params
	 *
	 * @return array - array of link models in the format as below
	 *               array('linktype'=>list of link models);
	 */
	public function getDetailViewLinks($linkParams)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$recordModel = $this->getRecord();
		$recordId = $recordModel->getId();
		$linkModelList['DETAIL_VIEW_BASIC'] = [];

		if (($currentUserModel->isAdminUser() === true || $currentUserModel->get('id') === $recordId) && $recordModel->get('status') === 'Active') {
			$recordModel = $this->getRecord();
			$detailViewLinks = [];
			$detailViewLinks[] = [
				'linktype' => 'DETAIL_VIEW_ADDITIONAL',
				'linklabel' => 'LBL_CHANGE_PASSWORD',
				'linkdata' => ['url' => 'index.php?module=Users&view=PasswordModal&mode=change&record=' . $recordId],
				'linkclass' => 'btn-outline-info showModal',
				'linkicon' => 'fas fa-key mr-1',
				'showLabel' => true,
			];
			if ($currentUserModel->isAdminUser() === true) {
				$detailViewLinks[] = [
					'linktype' => 'DETAIL_VIEW_ADDITIONAL',
					'linklabel' => 'BTN_RESET_PASSWORD',
					'linkdata' => ['url' => 'index.php?module=Users&view=PasswordModal&mode=reset&record=' . $recordId],
					'linkclass' => 'btn-outline-info showModal',
					'linkicon' => 'fas fa-redo-alt mr-1',
					'showLabel' => true,
				];
			}
			$detailViewLinks[] = [
				'linktype' => 'DETAIL_VIEW_ADDITIONAL',
				'linklabel' => 'LBL_EDIT',
				'linkurl' => $linkParams['VIEW'] === 'PreferenceDetail' ? $recordModel->getPreferenceEditViewUrl() : $recordModel->getEditViewUrl(),
				'linkclass' => 'btn-outline-success',
				'linkicon' => 'fas fa-edit mr-1',
				'showLabel' => true,
			];
			if ($currentUserModel->getId() !== $recordId) {
				$detailViewLinks[] = [
					'linktype' => 'DETAIL_VIEW_ADDITIONAL',
					'linklabel' => 'LBL_DELETE',
					'linkurl' => 'javascript:Users_Detail_Js.triggerDeleteUser("' . $recordModel->getDeleteUrl() . '")',
					'linkicon' => 'fas fa-trash-alt mr-1',
					'linkclass' => 'btn-outline-danger',
					'showLabel' => true,
				];
			}
			foreach ($detailViewLinks as $detailViewLink) {
				$linkModelList['DETAIL_VIEW_ADDITIONAL'][] = Vtiger_Link_Model::getInstanceFromValues($detailViewLink);
				$detailViewLink['linktype'] = 'DETAILVIEWPREFERENCE';
				$linkModelList['DETAILVIEWPREFERENCE'][] = Vtiger_Link_Model::getInstanceFromValues($detailViewLink);
			}
			$detailViewActionLinks = [];
			$detailViewActionLinks[] = [
				'linktype' => 'DETAIL_VIEW_BASIC',
				'linklabel' => 'LBL_CHANGE_ACCESS_KEY',
				'linkurl' => "javascript:Users_Detail_Js.triggerChangeAccessKey('index.php?module=Users&action=SaveAjax&mode=changeAccessKey&record={$recordId}')",
				'linkicon' => 'fas fa-edit',
				'showLabel' => true,
			];
			if (\App\User::getUserModel($recordModel->getRealId())->getDetail('login_method') === 'PLL_PASSWORD_2FA' && \App\Config::security('USER_AUTHY_MODE') !== 'TOTP_OFF') {
				$detailViewActionLinks[] = [
					'linktype' => 'DETAIL_VIEW_BASIC',
					'linklabel' => 'LBL_2FA_TOTP_QR_CODE',
					'linkdata' => ['url' => 'index.php?module=Users&view=TwoFactorAuthenticationModal&record=' . $recordId],
					'linkclass' => 'showModal',
					'linkicon' => 'fas fa-key',
					'showLabel' => true,
				];
			}
			foreach ($detailViewActionLinks as $detailViewLink) {
				$linkModelList['DETAIL_VIEW_BASIC'][] = Vtiger_Link_Model::getInstanceFromValues($detailViewLink);
			}
		}
		return $linkModelList;
	}
}
