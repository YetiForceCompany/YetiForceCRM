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

class Vtiger_RelationAjax_Action extends \App\Controller\Action
{
	use App\Controller\ClearProcess;
	use \App\Controller\ExposeMethod;

	/**
	 * {@inheritdoc}
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('addRelation');
		$this->exposeMethod('deleteRelation');
		$this->exposeMethod('massDeleteRelation');
		$this->exposeMethod('exportToExcel');
		$this->exposeMethod('updateRelation');
		$this->exposeMethod('getRelatedListPageCount');
		$this->exposeMethod('updateFavoriteForRecord');
		$this->exposeMethod('calculate');
		$this->exposeMethod('massDownload');
		$this->exposeMethod('checkFilesIntegrity');
	}

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$request->isEmpty('record', true) && !\App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 403);
		}
		if (!$request->isEmpty('src_record', true) && !\App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->getInteger('src_record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 403);
		}
		if (!$request->isEmpty('related_module', true) && !$userPrivilegesModel->hasModulePermission($request->getByType('related_module', 2))) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 403);
		}
		if (!$request->isEmpty('relatedModule', true) && !\is_array($relatedModule = $request->getByType('relatedModule', 2)) && 'ProductsAndServices' !== $relatedModule) {
			if ('ModTracker' === $relatedModule) {
				if (!$userPrivilegesModel->hasModuleActionPermission($request->getModule(), 'ModTracker')) {
					throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 403);
				}
			} else {
				if (!$userPrivilegesModel->hasModulePermission($relatedModule)) {
					throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 403);
				}
			}
		}
	}

	/**
	 * Get query for records list from request.
	 *
	 * @param \App\Request $request
	 *
	 * @return \App\QueryGenerator|bool
	 */
	public static function getQuery(App\Request $request)
	{
		$selectedIds = $request->getArray('selected_ids', 'Alnum');
		if ($selectedIds && 'all' !== $selectedIds[0]) {
			$queryGenerator = new App\QueryGenerator($request->getByType('relatedModule', 'Alnum'));
			$queryGenerator->clearFields();
			$queryGenerator->addCondition('id', $selectedIds, 'e');
			return $queryGenerator;
		}
		$parentRecordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
		$relationId = $request->isEmpty('relationId') ? false : $request->getInteger('relationId');
		$cvId = $request->isEmpty('cvId', true) ? 0 : $request->getByType('cvId', 'Alnum');
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $request->getByType('relatedModule', 'Alnum'), $relationId, $cvId);
		if ($request->has('entityState')) {
			$relationListView->set('entityState', $request->getByType('entityState'));
		}
		$operator = 's';
		if (!$request->isEmpty('operator', true)) {
			$operator = $request->getByType('operator');
			$relationListView->set('operator', $operator);
		}
		if (!$request->isEmpty('search_key', true)) {
			$searchKey = $request->getByType('search_key', 'Alnum');
			$relationListView->set('search_key', $searchKey);
			$relationListView->set('search_value', App\Condition::validSearchValue($request->getByType('search_value', 'Text'), $relationListView->getQueryGenerator()->getModule(), $searchKey, $operator));
		}
		$searchParams = App\Condition::validSearchParams($request->getByType('relatedModule', 'Alnum'), $request->getArray('search_params'));
		if (empty($searchParams) || !\is_array($searchParams)) {
			$searchParams = [];
		}
		$relationListView->set('search_params', $relationListView->getQueryGenerator()->parseBaseSearchParamsToCondition($searchParams));
		$queryGenerator = $relationListView->getRelationQuery(true);
		$queryGenerator->clearFields();
		$excludedIds = $request->getArray('excluded_ids', 'Integer');
		if ($excludedIds && \is_array($excludedIds)) {
			$queryGenerator->addCondition('id', $excludedIds, 'n');
		}
		return $queryGenerator;
	}

	/**
	 * Get records list from request.
	 *
	 * @param \App\Request $request
	 *
	 * @return int[]
	 */
	public static function getRecordsListFromRequest(App\Request $request)
	{
		$selectedIds = $request->getArray('selected_ids', 2);
		if ($selectedIds && 'all' !== $selectedIds[0]) {
			return $selectedIds;
		}
		$queryGenerator = static::getQuery($request);
		return $queryGenerator ? $queryGenerator->createQuery()->column() : [];
	}

	/**
	 * Function to add relation for specified source record id and related record id list.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function addRelation(App\Request $request)
	{
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->getInteger('src_record');
		$relatedModule = $request->getByType('related_module', 2);
		if (is_numeric($relatedModule)) {
			$relatedModule = \App\Module::getModuleName($relatedModule);
		}
		if (!\App\Privilege::isPermitted($sourceModule, 'DetailView', $sourceRecordId)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		if ($request->isEmpty('relationId')) {
			$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, Vtiger_Module_Model::getInstance($relatedModule));
		} else {
			$relationModel = Vtiger_Relation_Model::getInstanceById($request->getInteger('relationId'));
		}
		foreach ($request->getArray('related_record_list', 'Integer') as $relatedRecordId) {
			if (\App\Privilege::isPermitted($relatedModule, 'DetailView', $relatedRecordId)) {
				$relationModel->addRelation($sourceRecordId, $relatedRecordId);
			}
		}
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	/**
	 * Function to delete the relation for specified source record id and related record id list.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function deleteRelation(App\Request $request)
	{
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->getInteger('src_record');
		$relatedModule = $request->getByType('related_module', 2);
		$relatedRecordIdList = $request->getArray('related_record_list', 'Integer');
		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		if ($request->isEmpty('relationId')) {
			$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, Vtiger_Module_Model::getInstance($relatedModule));
		} else {
			$relationModel = Vtiger_Relation_Model::getInstanceById($request->getInteger('relationId'));
		}
		$result = false;
		if ($relationModel->privilegeToDelete()) {
			foreach ($relatedRecordIdList as $relatedRecordId) {
				if (\App\Privilege::isPermitted($relatedModule, 'DetailView', $relatedRecordId)) {
					$result = $relationModel->deleteRelation($sourceRecordId, (int) $relatedRecordId);
				}
			}
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * This function removes the relationship associated with the module.
	 *
	 * @param \App\Request $request
	 */
	public function massDeleteRelation(App\Request $request)
	{
		$sourceModule = $request->getModule();
		$relatedModuleName = $request->getByType('relatedModule', 2);
		$sourceRecordId = $request->getInteger('src_record');
		$parentRecordModel = Vtiger_Record_Model::getInstanceById($sourceRecordId, $sourceModule);
		$relationId = $request->isEmpty('relationId') ? false : $request->getInteger('relationId');
		$cvId = $request->isEmpty('cvId', true) ? 0 : $request->getByType('cvId', 'Alnum');
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, $relationId, $cvId);
		$rows = $this->getRecordsListFromRequest($request);
		$relationModel = $relationListView->getRelationModel();
		foreach ($rows as $relatedRecordId) {
			if (\App\Privilege::isPermitted($relatedModuleName, 'DetailView', $relatedRecordId)) {
				$relationModel->deleteRelation((int) $sourceRecordId, (int) $relatedRecordId);
			}
		}

		$response = new Vtiger_Response();
		$response->setResult(['reloadList' => true]);
		$response->emit();
	}

	/**
	 * Export relations to excel.
	 *
	 * @param \App\Request $request
	 */
	public function exportToExcel(App\Request $request)
	{
		$sourceModule = $request->getModule();
		$relatedModuleName = $request->getByType('relatedModule', 2);
		$sourceRecordId = $request->getInteger('src_record');
		$parentRecordModel = Vtiger_Record_Model::getInstanceById($sourceRecordId, $sourceModule);
		$relationId = $request->isEmpty('relationId') ? false : $request->getInteger('relationId');
		$cvId = $request->isEmpty('cvId', true) ? 0 : $request->getByType('cvId', 'Alnum');
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, $relationId, $cvId);
		$rows = $this->getRecordsListFromRequest($request);
		$workbook = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		$worksheet = $workbook->setActiveSheetIndex(0);
		$header_styles = [
			'fill' => ['type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'E1E0F7']],
			'font' => ['bold' => true],
		];
		$row = 1;
		$col = 0;
		$headers = $relationListView->getHeaders();
		foreach ($headers as $fieldsModel) {
			$worksheet->setCellValueExplicitByColumnAndRow($col, $row, App\Purifier::decodeHtml(App\Language::translate($fieldsModel->getFieldLabel(), $relatedModuleName)), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
			++$col;
		}
		++$row;
		foreach ($rows as $id) {
			if (\App\Privilege::isPermitted($relatedModuleName, 'DetailView', $id)) {
				$col = 0;
				$record = Vtiger_Record_Model::getInstanceById($id, $relatedModuleName);
				if (!$record->isViewable()) {
					continue;
				}
				foreach ($headers as $fieldsModel) {
					//depending on the uitype we might want the raw value, the display value or something else.
					//we might also want the display value sans-links so we can use strip_tags for that
					//phone numbers need to be explicit strings
					$value = $record->getDisplayValue($fieldsModel->getFieldName(), $id, true);
					switch ($fieldsModel->getUIType()) {
						case 25:
						case 7:
							if ('sum_time' === $fieldsModel->getFieldName()) {
								$worksheet->setCellvalueExplicitByColumnAndRow($col, $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
							} else {
								$worksheet->setCellvalueExplicitByColumnAndRow($col, $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
							}
							break;
						case 71:
						case 72:
							$worksheet->setCellvalueExplicitByColumnAndRow($col, $row, $record->get($fieldsModel->getFieldName()), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
							break;
						case 6: //datetimes
						case 23:
						case 70:
							$worksheet->setCellvalueExplicitByColumnAndRow($col, $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(strtotime($record->get($fieldsModel->getFieldName()))), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
							$worksheet->getStyleByColumnAndRow($col, $row)->getNumberFormat()->setFormatCode('DD/MM/YYYY HH:MM:SS'); //format the date to the users preference
							break;
						default:
							$worksheet->setCellValueExplicitByColumnAndRow($col, $row, App\Purifier::decodeHtml($value), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
					}
					++$col;
				}
				++$row;
			}
		}
		//having written out all the data lets have a go at getting the columns to auto-size
		$col = 0;
		$row = 1;
		foreach ($headers as &$fieldsModel) {
			$cell = $worksheet->getCellByColumnAndRow($col, $row);
			$worksheet->getStyleByColumnAndRow($col, $row)->applyFromArray($header_styles);
			$worksheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
			++$col;
		}
		$tmpDir = \App\Config::main('tmp_dir');
		$tempFileName = tempnam(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $tmpDir, 'xls');
		$workbookWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($workbook, 'Xls');
		$workbookWriter->save($tempFileName);
		if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
			header('pragma: public');
			header('cache-control: must-revalidate, post-check=0, pre-check=0');
		}
		header('content-type: application/x-msexcel');
		header('content-length: ' . filesize($tempFileName));
		$filename = \App\Language::translate($relatedModuleName, $relatedModuleName) . '.xls';
		header("content-disposition: attachment; filename=\"$filename\"");
		$fp = fopen($tempFileName, 'r');
		fpassthru($fp);
		fclose($fp);
		unlink($tempFileName);
	}

	/**
	 * Function to update the relation for specified source record id and related record id list.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function updateRelation(App\Request $request)
	{
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->getInteger('src_record');
		$relatedModule = $request->getByType('related_module', 2);
		$recordsToRemove = $request->getArray('recordsToRemove', 'Integer');
		$recordsToAdd = $request->getArray('recordsToAdd', 'Integer');
		$categoryToAdd = $request->getArray('categoryToAdd', 'Alnum');
		$categoryToRemove = $request->getArray('categoryToRemove', 'Alnum');
		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		if ($request->isEmpty('relationId')) {
			$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, Vtiger_Module_Model::getInstance($relatedModule));
		} else {
			$relationModel = Vtiger_Relation_Model::getInstanceById($request->getInteger('relationId'));
		}
		if (!empty($recordsToAdd)) {
			foreach ($recordsToAdd as $relatedRecordId) {
				if (\App\Privilege::isPermitted($relatedModule, 'DetailView', $relatedRecordId)) {
					$relationModel->addRelation($sourceRecordId, $relatedRecordId);
				}
			}
		}
		if (!empty($recordsToRemove)) {
			if ($relationModel->privilegeToDelete()) {
				foreach ($recordsToRemove as $relatedRecordId) {
					$relationModel->deleteRelation((int) $sourceRecordId, (int) $relatedRecordId);
				}
			} else {
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
			}
		}
		if (!empty($categoryToAdd) && $relationModel->isTreeRelation()) {
			foreach ($categoryToAdd as $category) {
				$relationModel->addRelationTree($sourceRecordId, $category);
			}
		}
		if (!empty($categoryToRemove) && $relationModel->isTreeRelation()) {
			if ($relationModel->privilegeToTreeDelete()) {
				foreach ($categoryToRemove as $category) {
					$relationModel->deleteRelationTree($sourceRecordId, $category);
				}
			} else {
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
			}
		}
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	/**
	 * Function to get the page count for related list.
	 *
	 * @param \App\Request $request
	 */
	public function getRelatedListPageCount(App\Request $request)
	{
		$moduleName = $request->getModule();
		$relatedModuleName = $request->getArray('relatedModule', 'Alnum');
		$firstRelatedModuleName = current($relatedModuleName);
		$parentId = $request->getInteger('record');
		if (!\App\Privilege::isPermitted($moduleName, 'DetailView', $parentId)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$relationId = $request->isEmpty('relationId') ? false : $request->getInteger('relationId');
		$cvId = $request->isEmpty('cvId', true) ? 0 : $request->getByType('cvId', 'Alnum');
		$totalCount = 0;
		$pageCount = 0;
		if ('ModComments' === $firstRelatedModuleName) {
			$totalCount = ModComments_Record_Model::getCommentsCount($parentId);
		} elseif ('ModTracker' === $firstRelatedModuleName) {
			$count = (int) ($unreviewed = current(ModTracker_Record_Model::getUnreviewed($parentId, false, true))) ? array_sum($unreviewed) : '';
			$totalCount = $count ?: '';
		} else {
			$relModules = !empty($relatedModuleName) && \is_array($relatedModuleName) ? $relatedModuleName : [];
			if ('ProductsAndServices' === $firstRelatedModuleName) {
				$relModules = ['Products', 'OutsourcedProducts', 'Assets', 'Services', 'OSSOutsourcedServices', 'OSSSoldServices'];
			}
			$categoryCount = ['Products', 'OutsourcedProducts', 'Services', 'OSSOutsourcedServices'];
			$pagingModel = new Vtiger_Paging_Model();
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
			$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
			foreach ($relModules as $relModule) {
				if (!$currentUserPriviligesModel->hasModulePermission($relModule)) {
					continue;
				}
				$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relModule, $relationId, $cvId);
				if (!$relationListView) {
					continue;
				}
				if ('ProductsAndServices' === $relatedModuleName && \in_array($relModule, $categoryCount)) {
					$totalCount += (int) $relationListView->getRelatedTreeEntriesCount();
				}
				if ('Calendar' === $relatedModuleName && \App\Config::module($relatedModuleName, 'SHOW_ONLY_CURRENT_RECORDS_COUNT')) {
					$totalCount += (int) $relationListView->getRelationQuery()->andWhere(['vtiger_activity.status' => Calendar_Module_Model::getComponentActivityStateLabel('current')])->count();
				} else {
					$totalCount += (int) $relationListView->getRelatedEntriesCount();
				}
				$pageLimit = $pagingModel->getPageLimit();
				$pageCount = ceil((int) $totalCount / (int) $pageLimit);
			}
		}
		if (0 == $pageCount) {
			$pageCount = 1;
		}
		$result = [];
		$result['numberOfRecords'] = $totalCount;
		$result['page'] = $pageCount;
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	public function updateFavoriteForRecord(App\Request $request)
	{
		$sourceModuleModel = Vtiger_Module_Model::getInstance($request->getModule());
		if ($request->isEmpty('relationId')) {
			$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, Vtiger_Module_Model::getInstance($request->getByType('relatedModule', 2)));
		} else {
			$relationModel = Vtiger_Relation_Model::getInstanceById($request->getInteger('relationId'));
		}
		if (!empty($relationModel)) {
			$result = $relationModel->updateFavoriteForRecord($request->getByType('actionMode'), ['crmid' => $request->getInteger('record'), 'relcrmid' => $request->getInteger('relcrmid')]);
		}

		$response = new Vtiger_Response();
		$response->setResult((bool) $result);
		$response->emit();
	}

	/**
	 * Function for calculating values for a list of related records.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\Security
	 * @throws \App\Exceptions\NotAllowedMethod
	 */
	public function calculate(App\Request $request)
	{
		$queryGenerator = static::getQuery($request);
		$fieldQueryModel = $queryGenerator->getQueryField($request->getByType('fieldName', 2));
		$fieldModel = $fieldQueryModel->getField();
		if (!$fieldModel->isViewable()) {
			throw new \App\Exceptions\Security('ERR_NO_ACCESS_TO_THE_FIELD', 403);
		}
		if (!$fieldModel->isCalculateField()) {
			throw new \App\Exceptions\Security('ERR_NOT_SUPPORTED_FIELD', 406);
		}
		$columnName = $fieldQueryModel->getColumnName();
		if ('sum' === $request->getByType('calculateType')) {
			$fieldName = $fieldModel->getName();
			$query = $queryGenerator->setFields(['id'])->setDistinct(null)->setGroup('id')->createQuery()->select([$fieldName => $columnName]);
			$value = (new \App\Db\Query())->from(['c' => $query])->sum("c.{$fieldName}");
		} else {
			throw new \App\Exceptions\NotAllowedMethod('LBL_PERMISSION_DENIED', 406);
		}
		$response = new Vtiger_Response();
		$response->setResult($fieldModel->getDisplayValue($value));
		$response->emit();
	}

	/**
	 * Mass download.
	 *
	 * @param App\Request $request
	 */
	public function massDownload(App\Request $request)
	{
		$relatedModuleName = $request->getByType('relatedModule', 2);
		$records = $this->getRecordsListFromRequest($request);
		if (1 === \count($records)) {
			$documentRecordModel = Vtiger_Record_Model::getInstanceById($records[0], $relatedModuleName);
			$documentRecordModel->downloadFile();
			$documentRecordModel->updateDownloadCount();
		} else {
			Documents_Record_Model::downloadFiles($records);
		}
	}

	/**
	 * Check many files integrity.
	 *
	 * @param App\Request $request
	 */
	public function checkFilesIntegrity(App\Request $request)
	{
		$relatedModuleName = $request->getByType('relatedModule', 2);
		$fileNotAvailable = [];
		$result = ['success' => true];
		foreach ($this->getRecordsListFromRequest($request) as $record) {
			$documentRecordModel = Vtiger_Record_Model::getInstanceById($record, $relatedModuleName);
			$resultVal = $documentRecordModel->checkFileIntegrity();
			if (!$resultVal) {
				$fileNotAvailable[] = $documentRecordModel->get('notes_title');
			}
		}
		if (!empty($fileNotAvailable)) {
			$result = ['notify' => ['text' => \App\Language::translate('LBL_FILE_NOT_AVAILABLE', $relatedModuleName) . ': <br>- ' . implode('<br>- ', $fileNotAvailable)]];
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
