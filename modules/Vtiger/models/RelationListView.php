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

class Vtiger_RelationListView_Model extends Vtiger_Base_Model
{

	protected $relationModel = false;
	protected $parentRecordModel = false;
	protected $relatedModuleModel = false;
	protected $query = false;

	public function setRelationModel($relation)
	{
		$this->relationModel = $relation;
		return $this;
	}

	public function getRelationModel()
	{
		return $this->relationModel;
	}

	public function setParentRecordModel($parentRecord)
	{
		$this->parentRecordModel = $parentRecord;
		return $this;
	}

	public function getParentRecordModel()
	{
		return $this->parentRecordModel;
	}

	public function setRelatedModuleModel($relatedModuleModel)
	{
		$this->relatedModuleModel = $relatedModuleModel;
		return $this;
	}

	public function getRelatedModuleModel()
	{
		return $this->relatedModuleModel;
	}

	public function getCreateViewUrl()
	{
		$relationModel = $this->getRelationModel();
		$relatedModel = $relationModel->getRelationModuleModel();
		$parentRecordModule = $this->getParentRecordModel();
		$parentModule = $parentRecordModule->getModule();

		$createViewUrl = $relatedModel->getCreateRecordUrl() . '&sourceModule=' . $parentModule->get('name') .
			'&sourceRecord=' . $parentRecordModule->getId() . '&relationOperation=true';

		//To keep the reference fieldname and record value in the url if it is direct relation
		if ($relationModel->isDirectRelation()) {
			$relationField = $relationModel->getRelationField();
			$createViewUrl .='&' . $relationField->getName() . '=' . $parentRecordModule->getId();
		}
		return $createViewUrl;
	}

	public function getCreateEventRecordUrl()
	{
		$relationModel = $this->getRelationModel();
		$relatedModel = $relationModel->getRelationModuleModel();
		$parentRecordModule = $this->getParentRecordModel();
		$parentModule = $parentRecordModule->getModule();

		$createViewUrl = $relatedModel->getCreateEventRecordUrl() . '&sourceModule=' . $parentModule->get('name') .
			'&sourceRecord=' . $parentRecordModule->getId() . '&relationOperation=true';

		//To keep the reference fieldname and record value in the url if it is direct relation
		if ($relationModel->isDirectRelation()) {
			$relationField = $relationModel->getRelationField();
			$createViewUrl .='&' . $relationField->getName() . '=' . $parentRecordModule->getId();
		}
		return $createViewUrl;
	}

	public function getCreateTaskRecordUrl()
	{
		$relationModel = $this->getRelationModel();
		$relatedModel = $relationModel->getRelationModuleModel();
		$parentRecordModule = $this->getParentRecordModel();
		$parentModule = $parentRecordModule->getModule();

		$createViewUrl = $relatedModel->getCreateTaskRecordUrl() . '&sourceModule=' . $parentModule->get('name') .
			'&sourceRecord=' . $parentRecordModule->getId() . '&relationOperation=true';

		//To keep the reference fieldname and record value in the url if it is direct relation
		if ($relationModel->isDirectRelation()) {
			$relationField = $relationModel->getRelationField();
			$createViewUrl .='&' . $relationField->getName() . '=' . $parentRecordModule->getId();
		}
		return $createViewUrl;
	}

	public function getLinks()
	{
		$relationModel = $this->getRelationModel();
		$actions = $relationModel->getActions();

		$selectLinks = $this->getSelectRelationLinks();
		foreach ($selectLinks as $selectLinkModel) {
			$selectLinkModel->set('_selectRelation', true)->set('_module', $relationModel->getRelationModuleModel());
		}
		$addLinks = $this->getAddRelationLinks();

		$links = array_merge($selectLinks, $addLinks);
		$relatedLink = array();
		$relatedLink['LISTVIEWBASIC'] = $links;
		return $relatedLink;
	}

	public function getSelectRelationLinks()
	{
		$relationModel = $this->getRelationModel();
		$selectLinkModel = array();

		if (!$relationModel->isSelectActionSupported()) {
			return $selectLinkModel;
		}

		$relatedModel = $relationModel->getRelationModuleModel();

		$selectLinkList = array(
			array(
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => vtranslate('LBL_SELECT_RELATION', $relatedModel->getName()),
				'linkurl' => '',
				'linkicon' => '',
			)
		);


		foreach ($selectLinkList as $selectLink) {
			$selectLinkModel[] = Vtiger_Link_Model::getInstanceFromValues($selectLink);
		}
		return $selectLinkModel;
	}

	public function getAddRelationLinks()
	{
		$relationModel = $this->getRelationModel();
		$addLinkModel = array();

		if (!$relationModel->isAddActionSupported()) {
			return $addLinkModel;
		}
		$relatedModel = $relationModel->getRelationModuleModel();

		if ($relatedModel->get('label') == 'Calendar') {
			$addLinkList[] = array(
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => vtranslate('LBL_ADD_EVENT'),
				'linkurl' => $this->getCreateEventRecordUrl(),
				'linkicon' => '',
			);
			$addLinkList[] = array(
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => vtranslate('LBL_ADD_TASK'),
				'linkurl' => $this->getCreateTaskRecordUrl(),
				'linkicon' => '',
			);
		} else {
			$addLinkList = array(
				array(
					'linktype' => 'LISTVIEWBASIC',
					// NOTE: $relatedModel->get('label') assuming it to be a module name - we need singular label for Add action.
					//'linklabel' => vtranslate('LBL_ADD')." ".vtranslate('SINGLE_' . $relatedModel->getName(), $relatedModel->getName()),
					'linklabel' => vtranslate('LBL_ADD_RELATION'),
					'linkurl' => $this->getCreateViewUrl(),
					'linkqcs' => $relatedModel->isQuickCreateSupported(),
					'linkicon' => '',
				)
			);
		}

		foreach ($addLinkList as $addLink) {
			$addLinkModel[] = Vtiger_Link_Model::getInstanceFromValues($addLink);
		}
		return $addLinkModel;
	}

	public function getEntries($pagingModel)
	{
		$db = PearDatabase::getInstance();
		$parentModule = $this->getParentRecordModel()->getModule();
		$relationModel = $this->getRelationModel();
		$relationModule = $relationModel->getRelationModuleModel();
		$relatedColumnFields = $relationModel->getRelationFields(true, true);
		$relationModuleName = $relationModule->get('name');
		if (count($relatedColumnFields) <= 0) {
			$relatedColumnFields = $relationModule->getConfigureRelatedListFields();
		}
		if (count($relatedColumnFields) <= 0) {
			$relatedColumnFields = $relationModule->getRelatedListFields();
		}

		if ($relationModuleName == 'Calendar') {
			//Adding visibility in the related list, showing records based on the visibility
			$relatedColumnFields['visibility'] = 'visibility';
		}
		if ($relationModuleName == 'PriceBooks') {
			//Adding fields in the related list
			$relatedColumnFields['unit_price'] = 'unit_price';
			$relatedColumnFields['listprice'] = 'listprice';
			$relatedColumnFields['currency_id'] = 'currency_id';
		}
		if ($relationModuleName == 'Documents') {
			$relatedColumnFields['filelocationtype'] = 'filelocationtype';
			$relatedColumnFields['filestatus'] = 'filestatus';
		}

		$query = $this->getRelationQuery();

		if ($this->get('whereCondition')) {
			$query = $this->updateQueryWithWhereCondition($query);
		}

		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();

		$orderBy = $this->getForSql('orderby');
		$sortOrder = $this->getForSql('sortorder');
		if ($orderBy) {
			$orderByFieldModuleModel = $relationModule->getFieldByColumn($orderBy);
			if ($orderByFieldModuleModel && $orderByFieldModuleModel->isReferenceField()) {
				//If reference field then we need to perform a join with crmentity with the related to field
				$queryComponents = $split = explode(' where ', $query);
				$selectAndFromClause = $queryComponents[0];
				$whereCondition = $queryComponents[1];
				$qualifiedOrderBy = 'vtiger_crmentity' . $orderByFieldModuleModel->get('column');
				$selectAndFromClause .= ' LEFT JOIN vtiger_crmentity AS ' . $qualifiedOrderBy . ' ON ' .
					$orderByFieldModuleModel->get('table') . '.' . $orderByFieldModuleModel->get('column') . ' = ' .
					$qualifiedOrderBy . '.crmid ';
				$query = $selectAndFromClause . ' WHERE ' . $whereCondition;
				$query .= ' ORDER BY ' . $qualifiedOrderBy . '.label ' . $sortOrder;
			} elseif ($orderByFieldModuleModel && $orderByFieldModuleModel->isOwnerField()) {
				$query .= ' ORDER BY COALESCE(' . getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users') . ',vtiger_groups.groupname) ' . $sortOrder;
			} else {
				// Qualify the the column name with table to remove ambugity
				$qualifiedOrderBy = $orderBy;
				$orderByField = $relationModule->getFieldByColumn($orderBy);
				if ($orderByField) {
					$qualifiedOrderBy = $relationModule->getOrderBySql($qualifiedOrderBy);
				}
				$query = "$query ORDER BY $qualifiedOrderBy $sortOrder";
			}
		}
		$limitQuery = $query . ' LIMIT ' . $startIndex . ',' . $pageLimit;
		$result = $db->pquery($limitQuery, array());
		$relatedRecordList = array();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$groupsIds = Vtiger_Util_Helper::getGroupsIdsForUsers($currentUser->getId());
		while ($row = $db->fetchByAssoc($result)) {
			$recordId = $row['crmid'];
			$newRow = array();
			foreach ($row as $col => $val) {
				if (array_key_exists($col, $relatedColumnFields)) {
					if ($relationModuleName == 'Documents' && $col == 'filename') {
						$fileName = $row['filename'];
						$downloadType = $row['filelocationtype'];
						$status = $row['filestatus'];
						$fileIdQuery = "select attachmentsid from vtiger_seattachmentsrel where crmid=?";

						$fileIdRes = $db->pquery($fileIdQuery, array($recordId));
						$fileId = $db->query_result($fileIdRes, 0, 'attachmentsid');

						if ($fileName != '' && $status == 1) {
							if ($downloadType == 'I') {
								$val = '<a onclick="Javascript:Documents_Index_Js.updateDownloadCount(\'index.php?module=Documents&action=UpdateDownloadCount&record=' . $recordId . '\');"' .
									' href="index.php?module=Documents&action=DownloadFile&record=' . $recordId . '&fileid=' . $fileId . '"' .
									' title="' . getTranslatedString('LBL_DOWNLOAD_FILE', $relationModuleName) .
									'" >' . textlength_check($val) .
									'</a>';
							} elseif ($downloadType == 'E') {
								$val = '<a onclick="Javascript:Documents_Index_Js.updateDownloadCount(\'index.php?module=Documents&action=UpdateDownloadCount&record=' . $recordId . '\');"' .
									' href="' . $fileName . '" target="_blank"' .
									' title="' . getTranslatedString('LBL_DOWNLOAD_FILE', $relationModuleName) .
									'" >' . textlength_check($val) .
									'</a>';
							} else {
								$val = ' --';
							}
						}
					}
					$newRow[$relatedColumnFields[$col]] = $val;
				}
			}
			//To show the value of "Assigned to"
			$ownerId = $row['smownerid'];
			$newRow['assigned_user_id'] = $row['smownerid'];
			if ($relationModuleName == 'Calendar') {
				$visibleFields = array('activitytype', 'date_start', 'time_start', 'due_date', 'time_end', 'assigned_user_id', 'visibility', 'smownerid', 'parent_id');
				$visibility = true;
				if (in_array($ownerId, $groupsIds)) {
					$visibility = false;
				} else if ($ownerId == $currentUser->getId()) {
					$visibility = false;
				}
				if (!$currentUser->isAdminUser() && $newRow['activitytype'] != 'Task' && $newRow['visibility'] == 'Private' && $ownerId && $visibility) {
					foreach ($newRow as $data => $value) {
						if (in_array($data, $visibleFields) != -1) {
							unset($newRow[$data]);
						}
					}
					$newRow['subject'] = vtranslate('Busy', 'Events') . '*';
				}
				if ($newRow['activitytype'] == 'Task') {
					unset($newRow['visibility']);
				}
			}

			$record = Vtiger_Record_Model::getCleanInstance($relationModule->get('name'));
			$record->setData($newRow)->setModuleFromInstance($relationModule);
			$record->setId($row['crmid']);
			$relatedRecordList[$row['crmid']] = $record;
		}
		$pagingModel->calculatePageRange($relatedRecordList);

		$nextLimitQuery = $query . ' LIMIT ' . ($startIndex + $pageLimit) . ' , 1';
		$nextPageLimitResult = $db->pquery($nextLimitQuery, array());
		if ($db->num_rows($nextPageLimitResult) > 0) {
			$pagingModel->set('nextPageExists', true);
		} else {
			$pagingModel->set('nextPageExists', false);
		}
		return $relatedRecordList;
	}

	public function getHeaders()
	{
		$relationModel = $this->getRelationModel();
		$relatedModuleModel = $relationModel->getRelationModuleModel();
		$relationFields = $relationModel->getRelationFields(true);

		$headerFields = array();
		if (count($relationFields) > 0) {
			foreach ($relationFields as $fieldName) {
				$headerFields[$fieldName] = $relatedModuleModel->getField($fieldName);
			}
			return $headerFields;
		}
		$summaryFieldsList = $relatedModuleModel->getSummaryViewFieldsList();
		if (count($summaryFieldsList) > 0) {
			foreach ($summaryFieldsList as $fieldName => $fieldModel) {
				$headerFields[$fieldName] = $fieldModel;
			}
		} else {
			$headerFieldNames = $relatedModuleModel->getRelatedListFields();
			foreach ($headerFieldNames as $fieldName) {
				$headerFields[$fieldName] = $relatedModuleModel->getField($fieldName);
			}
		}
		return $headerFields;
	}

	/**
	 * Function to get Relation query
	 * @return <String>
	 */
	public function getRelationQuery()
	{
		if (!empty($this->query)) {
			return $this->query;
		}
		$relationModel = $this->getRelationModel();
		if (!empty($relationModel) && $relationModel->get('name') != NULL) {
			$recordModel = $this->getParentRecordModel();
			$this->query = $relationModel->getQuery($recordModel, false, $this);
			return $this->query;
		}
		$searchParams = $this->get('search_params');
		if (empty($searchParams)) {
			$searchParams = array();
		}

		$relatedModuleModel = $this->getRelatedModuleModel();
		$relatedModuleName = $relatedModuleModel->getName();

		$relatedModuleBaseTable = $relatedModuleModel->basetable;
		$relatedModuleEntityIdField = $relatedModuleModel->basetableid;

		$parentModuleModel = $relationModel->getParentModuleModel();
		$parentModuleBaseTable = $parentModuleModel->basetable;
		$parentModuleEntityIdField = $parentModuleModel->basetableid;
		$parentRecordId = $this->getParentRecordModel()->getId();
		$parentModuleDirectRelatedField = $parentModuleModel->get('directRelatedFieldName');

		$relatedModuleFields = array_keys($this->getHeaders());
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$queryGenerator = new QueryGenerator($relatedModuleName, $currentUserModel);
		$queryGenerator->setFields($relatedModuleFields);

		if (count($searchParams) > 0) {
			$queryGenerator->parseAdvFilterList($searchParams);
		}
		$joinQuery = ' INNER JOIN ' . $parentModuleBaseTable . ' ON ' . $parentModuleBaseTable . '.' . $parentModuleDirectRelatedField . " = " . $relatedModuleBaseTable . '.' . $relatedModuleEntityIdField;

		$query = $queryGenerator->getQuery();
		$queryComponents = explode(' FROM ', $query);
		foreach ($queryComponents as $key => $val) {
			if ($key == 0) {
				$query = $queryComponents[0] . ' ,vtiger_crmentity.crmid';
			} else {
				$query .= ' FROM ' . $val;
			}
		}
		$whereSplitQueryComponents = explode(' WHERE ', $query);
		$query = $whereSplitQueryComponents[0] . $joinQuery;
		foreach ($whereSplitQueryComponents as $key => $val) {
			if ($key == 0) {
				$query .= " WHERE $parentModuleBaseTable.$parentModuleEntityIdField = $parentRecordId AND ";
			} else {
				$query .= $val . ' WHERE ';
			}
		}
		$this->query = trim($query, "WHERE ");
		return $this->query;
	}

	public static function getInstance($parentRecordModel, $relationModuleName, $label = false)
	{
		$parentModuleName = $parentRecordModel->getModule()->get('name');
		$className = Vtiger_Loader::getComponentClassName('Model', 'RelationListView', $parentModuleName);
		$instance = new $className();

		$parentModuleModel = $parentRecordModel->getModule();
		$relatedModuleModel = Vtiger_Module_Model::getInstance($relationModuleName);
		$instance->setRelatedModuleModel($relatedModuleModel);

		$relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModuleModel, $label);
		$instance->setParentRecordModel($parentRecordModel);

		if (!$relationModel) {
			$relatedModuleName = $relatedModuleModel->getName();
			$parentModuleModel = $instance->getParentRecordModel()->getModule();
			$referenceFieldOfParentModule = $parentModuleModel->getFieldsByType('reference');
			foreach ($referenceFieldOfParentModule as $fieldName => $fieldModel) {
				$refredModulesOfReferenceField = $fieldModel->getReferenceList();
				if (in_array($relatedModuleName, $refredModulesOfReferenceField)) {
					$relationModelClassName = Vtiger_Loader::getComponentClassName('Model', 'Relation', $parentModuleModel->getName());
					$relationModel = new $relationModelClassName();
					$relationModel->setParentModuleModel($parentModuleModel)->setRelationModuleModel($relatedModuleModel);
					$parentModuleModel->set('directRelatedFieldName', $fieldModel->get('column'));
				}
			}
		}
		if (!$relationModel) {
			$relationModel = false;
		}
		$instance->setRelationModel($relationModel);
		return $instance;
	}

	/**
	 * Function to get Total number of record in this relation
	 * @return <Integer>
	 */
	public function getRelatedEntriesCount()
	{
		$db = PearDatabase::getInstance();
		$relationQuery = $this->getRelationQuery();
		$relationQuery = preg_replace("/[ \t\n\r]+/", " ", $relationQuery);
		$position = stripos($relationQuery, ' from ');
		if ($position) {
			$split = explode(' FROM ', $relationQuery);
			$splitCount = count($split);
			$relationQuery = 'SELECT COUNT(DISTINCT vtiger_crmentity.crmid) AS count';
			for ($i = 1; $i < $splitCount; $i++) {
				$relationQuery = $relationQuery . ' FROM ' . $split[$i];
			}
		}
		if (strpos($relationQuery, ' GROUP BY ') !== false) {
			$parts = explode(' GROUP BY ', $relationQuery);
			$relationQuery = $parts[0];
		}
		$result = $db->pquery($relationQuery, array());
		return $db->query_result($result, 0, 'count');
	}

	/**
	 * Function to update relation query
	 * @param <String> $relationQuery
	 * @return <String> $updatedQuery
	 */
	public function updateQueryWithWhereCondition($relationQuery)
	{
		$condition = '';

		$whereCondition = $this->get("whereCondition");
		$count = count($whereCondition);
		if ($count > 1) {
			$appendAndCondition = true;
		}

		$i = 1;
		foreach ($whereCondition as $fieldName => $fieldValue) {
			$condition .= " $fieldName = '$fieldValue' ";
			if ($appendAndCondition && ($i++ != $count)) {
				$condition .= " AND ";
			}
		}

		$pos = stripos($relationQuery, 'where');
		if ($pos) {
			$split = explode('where', $relationQuery);
			$updatedQuery = $split[0] . ' WHERE ' . $split[1] . ' AND ' . $condition;
		} else {
			$updatedQuery = $relationQuery . ' WHERE ' . $condition;
		}
		return $updatedQuery;
	}

	public function getCurrencySymbol($recordId, $fieldModel)
	{
		$db = PearDatabase::getInstance();
		$moduleName = $fieldModel->getModuleName();
		$fieldName = $fieldModel->get('name');
		$tableName = $fieldModel->get('table');
		$columnName = $fieldModel->get('column');

		if (($fieldName == 'currency_id') && ($moduleName == 'Products' || $moduleName == 'Services')) {
			$query = "SELECT currency_symbol FROM vtiger_currency_info WHERE id = (";
			if ($moduleName == 'Products')
				$query .= "SELECT currency_id FROM vtiger_products WHERE productid = ?)";
			else if ($moduleName == 'Services')
				$query .= "SELECT currency_id FROM vtiger_service WHERE serviceid = ?)";

			$result = $db->pquery($query, array($recordId));
			return $db->query_result($result, 0, 'currency_symbol');
		} else if (($tableName == 'vtiger_invoice' || $tableName == 'vtiger_quotes' || $tableName == 'vtiger_purchaseorder' || $tableName == 'vtiger_salesorder') &&
			($columnName == 'total' || $columnName == 'subtotal' || $columnName == 'discount_amount' || $columnName == 'paid' ||
			$columnName == 'balance' || $columnName == 'received' || $columnName == 'listprice' || $columnName == 'pre_tax_total')) {
			$focus = CRMEntity::getInstance($moduleName);
			$query = "SELECT currency_symbol FROM vtiger_currency_info WHERE id = ( SELECT currency_id FROM " . $tableName . " WHERE " . $focus->table_index . " = ? )";
			$result = $db->pquery($query, array($recordId));
			return $db->query_result($result, 0, 'currency_symbol');
		} else {
			$fieldInfo = $fieldModel->getFieldInfo();
			return $fieldInfo['currency_symbol'];
		}
	}
}
