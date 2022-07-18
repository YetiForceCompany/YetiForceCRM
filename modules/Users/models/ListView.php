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
		if (App\User::getCurrentUserModel()->isAdmin()) {
			$links['LISTVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => 'LBL_ADD_RECORD',
				'linkurl' => $this->getModule()->getCreateRecordUrl(),
				'linkicon' => '',
				'linkclass' => 'btn-light',
			]);
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
		$links['LISTVIEWMASSACTION'] = [];
		$massActionLinks = [];
		if ('Users' === $linkParams['MODULE'] && 'List' === $linkParams['ACTION'] && App\User::getCurrentUserModel()->isAdmin()) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MASS_EDIT',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerMassEdit("index.php?module=Users&view=MassActionAjax&mode=showMassEditForm");',
				'linkicon' => 'yfi yfi-full-editing-view',
			];
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'BTN_MASS_RESET_PASSWORD',
				'linkurl' => 'index.php?module=Users&view=PasswordModal&mode=massReset',
				'linkicon' => 'fas fa-redo-alt',
			];
			if ('TOTP_OFF' !== App\Config::security('USER_AUTHY_MODE')) {
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

	/** {@inheritdoc} */
	public function getListViewEntries(Vtiger_Paging_Model $pagingModel)
	{
		$queryGenerator = $this->getQueryGenerator();
		// Added as Users module do not have custom filters and id column is added by querygenerator.
		$fields = $queryGenerator->getFields();
		$fields[] = 'id';
		$fields[] = 'imagename';
		$fields[] = 'authy_secret_totp';
		$queryGenerator->setFields($fields);

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
			if ($fieldsModel && ((!$fieldsModel->isViewable() && 106 !== $fieldsModel->getUitype()) || !$fieldsModel->getPermissions())) {
				continue;
			}
			$headerFieldModels[$fieldName] = $fieldsModel;
		}
		return $headerFieldModels;
	}

	/**
	 * Function to give advance links of Users module.
	 *
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

	/** {@inheritdoc} */
	public function loadListViewOrderBy()
	{
		$orderBy = $this->getForSql('orderby');
		if (!empty($orderBy)) {
			[$fieldName, $moduleName, $sourceFieldName] = array_pad(explode(':', $orderBy), 3, false);
			if ($sourceFieldName) {
				return $this->getQueryGenerator()->setRelatedOrder([
					'sourceField' => $sourceFieldName,
					'relatedModule' => $moduleName,
					'relatedField' => $fieldName,
					'relatedSortOrder' => $this->getForSql('sortorder'),
				]);
			}
			return $this->getQueryGenerator()->setOrder($orderBy, $this->getForSql('sortorder'));
		}
	}
}
