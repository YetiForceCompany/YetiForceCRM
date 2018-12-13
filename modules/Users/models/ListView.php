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

class Users_ListView_Model extends Vtiger_ListView_Model
{
	/**
	 * Function to get the list of listview links for the module.
	 *
	 * @param <Array> $linkParams
	 *
	 * @return <Array> - Associate array of Link Type to List of Vtiger_Link_Model instances
	 */
	public function getListViewLinks($linkParams)
	{
		$linkTypes = ['LISTVIEWBASIC', 'LISTVIEW', 'LISTVIEWSETTING'];
		$links = Vtiger_Link_Model::getAllByType($this->getModule()->getId(), $linkTypes, $linkParams);

		$basicLinks = [
			[
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => 'LBL_ADD_RECORD',
				'linkurl' => $this->getModule()->getCreateRecordUrl(),
				'linkicon' => '',
				'linkclass' => 'btn-light'
			],
		];
		foreach ($basicLinks as $basicLink) {
			$links['LISTVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicLink);
		}

		$advancedLinks = $this->getAdvancedLinks();
		foreach ($advancedLinks as $advancedLink) {
			$links['LISTVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($advancedLink);
		}
		return $links;
	}

	/**
	 * Function to get the list of Mass actions for the module.
	 *
	 * @param <Array> $linkParams
	 *
	 * @return <Array> - Associative array of Link type to List of  Vtiger_Link_Model instances for Mass Actions
	 */
	public function getListViewMassActions($linkParams)
	{
		$links = [];
		$privilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$massActionLinks = [];
		if ($linkParams['MODULE'] === 'Users' && $linkParams['ACTION'] === 'List' && $privilegesModel->isAdminUser()) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MASS_EDIT',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerMassEdit("index.php?module=Users&view=MassActionAjax&mode=showMassEditForm");',
				'linkicon' => 'fas fa-edit'
			];
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'BTN_MASS_RESET_PASSWORD',
				'linkurl' => 'index.php?module=Users&view=PasswordModal&mode=massReset',
				'linkicon' => 'fas fa-redo-alt',
			];
			if (AppConfig::security('USER_AUTHY_MODE') !== 'TOTP_OFF') {
				$massActionLinks[] = [
					'linktype' => 'LISTVIEWMASSACTION',
					'linklabel' => 'BTN_MASS_OFF_2FA',
					'linkurl' => 'javascript:Settings_Users_List_Js.triggerMassOff2FA()',
					'linkicon' => 'fas fa-key',
				];
			}
		}
		foreach ($massActionLinks as $massActionLink) {
			$links['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}
		return $links;
	}

	/**
	 * Function to get the list view entries.
	 *
	 * @param Vtiger_Paging_Model $pagingModel , $status (Active or Inactive User). Default false
	 *
	 * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance
	 */
	public function getListViewEntries(Vtiger_Paging_Model $pagingModel)
	{
		$queryGenerator = $this->get('query_generator');
		// Added as Users module do not have custom filters and id column is added by querygenerator.
		$fields = $queryGenerator->getFields();
		$fields[] = 'id';
		$fields[] = 'imagename';
		$fields[] = 'authy_secret_totp';
		$queryGenerator->setFields($fields);
		$searchParams = $this->getArray('search_params');
		foreach ($searchParams as &$params) {
			foreach ($params as &$param) {
				if ($param['field_name'] === 'is_admin') {
					$param['value'] = $param['value'] == '0' ? 'off' : 'on';
				}
			}
		}
		$this->set('search_params', $searchParams);
		return parent::getListViewEntries($pagingModel);
	}

	/**
	 * Function to get the list view header.
	 *
	 * @return Vtiger_Field_Model[] - List of Vtiger_Field_Model instances
	 */
	public function getListViewHeaders()
	{
		$headerFieldModels = [];
		$headerFields = $this->getQueryGenerator()->getListViewFields();
		foreach ($headerFields as $fieldName => &$fieldsModel) {
			if ($fieldsModel && ((!$fieldsModel->isViewable() && $fieldsModel->getUitype() !== 106) || !$fieldsModel->getPermissions())) {
				continue;
			}
			$headerFieldModels[$fieldName] = $fieldsModel;
		}
		return $headerFieldModels;
	}

	/*
	 * Function to give advance links of Users module
	 * @return array of advanced links
	 */

	public function getAdvancedLinks()
	{
		$moduleModel = $this->getModule();
		$createPermission = \App\Privilege::isPermitted($moduleModel->getName(), 'CreateView');
		$advancedLinks = [];
		$importPermission = \App\Privilege::isPermitted($moduleModel->getName(), 'Import');
		if ($importPermission && $createPermission) {
			$advancedLinks[] = [
				'linktype' => 'LISTVIEW',
				'linklabel' => 'LBL_IMPORT',
				'linkurl' => $moduleModel->getImportUrl(),
				'linkicon' => 'fas fa-download',
			];
			$advancedLinks[] = [
				'linktype' => 'LISTVIEW',
				'linklabel' => 'LBL_EXPORT',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerExportAction("' . $moduleModel->getExportUrl() . '")',
				'linkicon' => 'fas fa-upload',
			];
		}
		return $advancedLinks;
	}
}
