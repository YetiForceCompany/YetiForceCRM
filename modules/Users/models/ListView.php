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
		$links = parent::getListViewMassActions($linkParams);
		$privilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$massActionLinks = [];
		if ($linkParams['MODULE'] === 'Users' && $linkParams['ACTION'] === 'List' && $privilegesModel->isAdminUser()) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'BTN_MASS_RESET_PASSWORD',
				'linkurl' => 'index.php?module=Users&view=PasswordModal&mode=massReset',
				'linkicon' => 'fas fa-redo-alt',
			];
		}
		foreach ($massActionLinks as $massActionLink) {
			$links['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}
		$countLinks = count($links['LISTVIEWMASSACTION']);
		for ($i = 0; $i < $countLinks; ++$i) {
			if ($links['LISTVIEWMASSACTION'][$i]->linklabel === 'LBL_MASS_DELETE' || $links['LISTVIEWMASSACTION'][$i]->linklabel === 'LBL_TRANSFER_OWNERSHIP') {
				unset($links['LISTVIEWMASSACTION'][$i]);
			}
		}

		return $links;
	}

	/**
	 * Function to get the list view entries.
	 *
	 * @param Vtiger_Paging_Model $pagingModel, $status (Active or Inactive User). Default false
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
		$queryGenerator->setFields($fields);
		$searchParams = $this->get('search_params');
		if (empty($searchParams)) {
			$searchParams = [];
		} else {
			foreach ($searchParams as &$params) {
				foreach ($params as &$param) {
					if (strpos($param['columnname'], 'is_admin') !== false) {
						$param['value'] = $param['value'] == '0' ? 'off' : 'on';
					}
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

	public function getListViewCount()
	{
		$searchParams = $this->get('search_params');
		if (is_array($searchParams) && count($searchParams[0]['columns']) < 1) {
			$this->set('search_params', []);
		}

		return parent::getListViewCount();
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
