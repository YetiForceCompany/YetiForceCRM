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

/**
 * Vtiger ListView Model Class
 */
class Calendar_ListView_Model extends Vtiger_ListView_Model
{

	public function getBasicLinks()
	{
		$basicLinks = [];
		$moduleModel = $this->getModule();
		$createPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'CreateView');
		if ($createPermission) {
			$basicLinks[] = [
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => 'LBL_ADD_EVENT',
				'linkurl' => $this->getModule()->getCreateEventRecordUrl(),
				'linkclass' => 'moduleColor_' . $moduleModel->getName(),
				'linkicon' => '',
				'showLabel' => 1,
			];
			$basicLinks[] = [
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => 'LBL_ADD_TASK',
				'linkurl' => $this->getModule()->getCreateTaskRecordUrl(),
				'linkclass' => 'moduleColor_' . $moduleModel->getName(),
				'linkicon' => '',
				'showLabel' => 1,
			];
		}
		return $basicLinks;
	}
	/*
	 * Function to give advance links of a module
	 * 	@RETURN array of advanced links
	 */

	public function getAdvancedLinks()
	{
		$moduleModel = $this->getModule();
		$createPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'CreateView') && Users_Privileges_Model::isPermitted($moduleModel->getName(), 'EditView');
		$advancedLinks = [];
		$importPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'Import');
		if ($importPermission && $createPermission) {
			$advancedLinks[] = array(
				'linktype' => 'LISTVIEW',
				'linklabel' => 'LBL_IMPORT',
				'linkurl' => 'javascript:Calendar_List_Js.triggerImportAction("' . $moduleModel->getImportUrl() . '")',
				'linkicon' => ''
			);
		}

		$exportPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'Export');
		if ($exportPermission) {
			$advancedLinks[] = array(
				'linktype' => 'LISTVIEW',
				'linklabel' => 'LBL_EXPORT',
				'linkurl' => 'javascript:Calendar_List_Js.triggerExportAction("' . $this->getModule()->getExportUrl() . '")',
				'linkicon' => ''
			);
		}
		return $advancedLinks;
	}

	/**
	 * Function to get the list of Mass actions for the module
	 * @param array $linkParams
	 * @return array - Associative array of Link type to List of  Vtiger_Link_Model instances for Mass Actions
	 */
	public function getListViewMassActions($linkParams)
	{
		$moduleModel = $this->getModule();
		$links = Vtiger_Link_Model::getAllByType($moduleModel->getId(), ['LISTVIEWMASSACTION'], $linkParams);

		$massActionLinks = [];
		if ($moduleModel->isPermitted('MassTransferOwnership')) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_TRANSFER_OWNERSHIP',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerTransferOwnership("index.php?module=' . $moduleModel->getName() . '&view=MassActionAjax&mode=transferOwnership")',
				'linkicon' => ''
			];
		}
		if ($moduleModel->isPermitted('MassDelete')) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_DELETE',
				'linkurl' => 'javascript:Vtiger_List_Js.massDeleteRecords("index.php?module=' . $moduleModel->get('name') . '&action=MassDelete");',
				'linkicon' => ''
			];
		}

		foreach ($massActionLinks as $massActionLink) {
			$links['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}

		return $links;
	}

	/**
	 * Function to get the list view entries
	 * @param Vtiger_Paging_Model $pagingModel
	 * @return array - Associative array of record id mapped to Vtiger_Record_Model instance.
	 */
	public function getListViewEntries(Vtiger_Paging_Model $pagingModel)
	{
		$queryGenerator = $this->get('query_generator');
		$queryGenerator->setField(['visibility', 'assigned_user_id', 'activitystatus']);
		return parent::getListViewEntries($pagingModel);
	}

	public function getListViewOrderBy()
	{
		$moduleModel = $this->getModule();

		$orderBy = $this->getForSql('orderby');
		$sortOrder = $this->getForSql('sortorder');
		if (!empty($orderBy)) {
			$columnFieldMapping = $moduleModel->getColumnFieldMapping();
			$orderByFieldName = $columnFieldMapping[$orderBy];
			$orderByFieldModel = $moduleModel->getField($orderByFieldName);

			if ($orderBy == 'date_start') {
				$orderBy = "str_to_date(concat(date_start,time_start),'%Y-%m-%d %H:%i:%s')";
			} else if ($orderBy == 'due_date') {
				$orderBy = "str_to_date(concat(due_date,time_end),'%Y-%m-%d %H:%i:%s')";
			}

			if ($orderByFieldModel && $orderByFieldModel->isReferenceField()) {
				//IF it is reference add it in the where fields so that from clause will be having join of the table
				$this->get('query_generator')->setConditionField($orderByFieldName);

				$referenceModules = $orderByFieldModel->getReferenceList();
				$referenceNameFieldOrderBy = [];
				foreach ($referenceModules as $referenceModuleName) {
					$referenceModuleModel = Vtiger_Module_Model::getInstance($referenceModuleName);
					$referenceNameFields = $referenceModuleModel->getNameFields();

					$columnList = [];
					foreach ($referenceNameFields as $nameField) {
						$fieldModel = $referenceModuleModel->getField($nameField);
						$columnList[] = $fieldModel->get('table') . $orderByFieldModel->getName() . '.' . $fieldModel->get('column');
					}
					if (count($columnList) > 1) {
						$referenceNameFieldOrderBy[] = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' => $columnList[0], 'last_name' => $columnList[1]), 'Users', '') . ' ' . $sortOrder;
					} else {
						$referenceNameFieldOrderBy[] = implode('', $columnList) . ' ' . $sortOrder;
					}
				}
				$query = ' ORDER BY %s';
				$query = sprintf($query, implode(',', $referenceNameFieldOrderBy));
			} else if ($orderBy === 'smownerid') {
				$this->get('query_generator')->setConditionField($orderByFieldName);
				$this->get('query_generator')->addUserTable = true;
				$fieldModel = Vtiger_Field_Model::getInstance('assigned_user_id', $moduleModel);
				if ($fieldModel->getFieldDataType() == 'owner') {
					$orderBy = 'COALESCE(' . \vtlib\Deprecated::getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users') . ',vtiger_groups.groupname)';
				}
				$query = ' ORDER BY %s %s';
				$query = sprintf($query, $orderBy, $sortOrder);
			} else {
				$query = ' ORDER BY %s %s';
				$query = sprintf($query, $orderBy, $sortOrder);
			}
		}
		return $query;
	}
}
