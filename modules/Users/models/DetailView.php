<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Users_DetailView_Model extends Vtiger_DetailView_Model
{
	/** {@inheritdoc} */
	public function getDetailViewLinks(array $linkParams): array
	{
		$currentUserModel = \App\User::getCurrentUserModel();
		$recordModel = $this->getRecord();
		$recordId = $recordModel->getId();
		$linkModelList['DETAIL_VIEW_BASIC'] = [];
		if (($currentUserModel->isAdmin() || $currentUserModel->getId() === $recordId) && 'Active' === $recordModel->get('status')) {
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
			if ($currentUserModel->isAdmin()) {
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
				'linkurl' => 'PreferenceDetail' === $linkParams['VIEW'] ? $recordModel->getPreferenceEditViewUrl() : $recordModel->getEditViewUrl(),
				'linkclass' => 'btn-outline-success',
				'linkicon' => 'yfi yfi-full-editing-view mr-1',
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
				'linkicon' => 'yfi yfi-full-editing-view',
				'showLabel' => true,
			];
			if (
				('PLL_PASSWORD_2FA' === $recordModel->get('login_method') || 'PLL_LDAP_2FA' === $recordModel->get('login_method'))
			 && $recordModel->getId() === \App\User::getCurrentUserRealId() && 'TOTP_OFF' !== \App\Config::security('USER_AUTHY_MODE')
			) {
				$detailViewActionLinks[] = [
					'linktype' => 'DETAIL_VIEW_BASIC',
					'linklabel' => 'LBL_2FA_TOTP_QR_CODE',
					'linkdata' => ['url' => 'index.php?module=Users&view=TwoFactorAuthenticationModal&record=' . $recordId],
					'linkclass' => 'showModal',
					'linkicon' => 'fas fa-key',
					'showLabel' => true,
				];
			}
			if ($currentUserModel->getId() === $recordId && $currentUserModel->get('leader') && \App\Privilege::isPermitted('Users', 'LeaderCanManageGroupMembership')) {
				$detailViewActionLinks[] = [
					'linktype' => 'DETAIL_VIEW_BASIC',
					'linklabel' => 'LBL_GROUP_MEMBERS_CHANGE_VIEW',
					'linkdata' => ['url' => 'index.php?module=Users&view=Groups&record=' . $recordId],
					'linkclass' => 'js-show-modal',
					'linkicon' => 'yfi-groups',
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
