<?php

/**
 *
 * @package YetiForce.views
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMail_SendMailModal_View extends Vtiger_BasicModal_View
{

	protected $customViewModel = false;
	protected $query = false;
	protected $emailColumns = false;

	public function process(Vtiger_Request $request)
	{
		$this->preProcess($request);

		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$sourceRecord = $request->get('sourceRecord');
		$sourceModule = $request->get('sourceModule');
		$relatedModule = $request->get('relatedModule');

		if (empty($sourceModule)) {
			$sourceModule = $moduleName;
		}

		$records = $this->getRecordsListFromRequest($request);
		$allRecords = $this->getRecordsCount($request);
		$url = 'mailto:?bcc=' . implode(',', $records);

		if ($sourceModule == 'Campaigns' && !empty($relatedModule)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($sourceRecord, $request->get('sourceModule'));
			$url .= '&subject=' . $recordModel->get('campaign_no') . ' - ' . $recordModel->get('campaignname');
		}
		$viewer->assign('URL', $url);
		$viewer->assign('SOURCE_RECORD', $sourceRecord);
		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->assign('RECORDS', $records);
		$viewer->assign('EMAIL_RECORDS', count($records));
		$viewer->assign('ALL_RECORDS', $allRecords);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('SendMailModal.tpl', $moduleName);

		$this->postProcess($request);
	}

	public function getRecordsListFromRequest(Vtiger_Request $request)
	{
		$cvId = $request->get('cvid');
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');

		if (empty($cvId) || $cvId == 'undefined') {
			if ($request->has('relatedModule')) {
				$sourceModule = $request->get('relatedModule');
			} else {
				$sourceModule = $request->get('sourceModule');
			}
			$cvId = CustomView_Record_Model::getAllFilterByModule($sourceModule)->getId();
		}

		$customViewModel = CustomView_Record_Model::getInstanceById($cvId);
		if ($customViewModel) {
			$searchKey = $request->get('search_key');
			$searchValue = $request->get('search_value');
			$operator = $request->get('operator');
			if (!empty($operator)) {
				$customViewModel->set('operator', $operator);
				$customViewModel->set('search_key', $searchKey);
				$customViewModel->set('search_value', $searchValue);
			}
			if ($request->has('search_params')) {
				$customViewModel->set('search_params', $request->get('search_params'));
			}
			$this->customViewModel = $customViewModel;
		}
		return $this->getRecordIds($selectedIds, $excludedIds);
	}

	/**
	 * Function which provides the records for the current view
	 * @param <Boolean> $excludedIds - List of the RecordIds to be skipped
	 * @return <Array> List of RecordsIds
	 */
	public function getRecordIds($selectedIds = false, $excludedIds = false)
	{
		$db = PearDatabase::getInstance();
		$query = $this->getQuery($selectedIds, $excludedIds);
		$result = $db->query($query);

		$moduleModel = $this->customViewModel->getModule();
		$baseTableId = $moduleModel->get('basetableid');

		$records = [];
		while ($row = $db->getRow($result)) {
			foreach ($this->emailColumns as &$email) {
				if (!empty($row[$email])) {
					$records[$row[$baseTableId]] = $row[$email];
					break;
				}
			}
		}
		return $records;
	}

	public function getRecordsCount(Vtiger_Request $request)
	{
		$selectedIds = $request->get('selected_ids');
		if (!empty($selectedIds) && $selectedIds != 'all') {
			if (!empty($selectedIds)) {
				return count($selectedIds);
			}
		}

		$db = PearDatabase::getInstance();
		$query = $this->getQuery();
		$exQuery = preg_split('/ FROM /i', $query, 2);
		$query = sprintf('SELECT count(*) FROM %s', $exQuery[1]);

		$result = $db->query($query);
		return $db->getSingleValue($result);
	}

	public function getQuery($selectedIds = false, $excludedIds = false)
	{
		if ($this->query) {
			return $this->query;
		}

		$cvId = $this->customViewModel->getId();
		$moduleModel = $this->customViewModel->getModule();
		$moduleName = $moduleModel->get('name');
		$baseTableName = $moduleModel->get('basetable');
		$baseTableId = $moduleModel->get('basetableid');

		$listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $cvId);
		$queryGenerator = $listViewModel->get('query_generator');

		$searchKey = $this->customViewModel->get('search_key');
		$searchValue = $this->customViewModel->get('search_value');
		$operator = $this->customViewModel->get('operator');
		if (!empty($searchValue)) {
			$queryGenerator->addUserSearchConditions(array('search_field' => $searchKey, 'search_text' => $searchValue, 'operator' => $operator));
		}

		$searchParams = $this->customViewModel->get('search_params');
		if (empty($searchParams)) {
			$searchParams = [];
		}
		foreach ($searchParams as $key => $value) {
			if (empty($value)) {
				unset($searchParams[$key]);
			}
		}
		$glue = '';
		if (count($queryGenerator->getWhereFields()) > 0 && (count($searchParams)) > 0) {
			$glue = QueryGenerator::$AND;
		}
		$transformedSearchParams = Vtiger_Util_Helper::transferListSearchParamsToFilterCondition($searchParams, $moduleModel);
		$queryGenerator->parseAdvFilterList($transformedSearchParams, $glue);

		$emailColumns = [];
		$emailFields = ['id'];
		$emailFieldModels = $moduleModel->getFieldsByType('email');
		foreach ($emailFieldModels as $fieldName => $fieldModel) {
			if ($fieldModel->isViewable()) {
				$emailColumns[] = $fieldModel->get('column');
				$emailFields[] = $fieldModel->getName();
			}
		}
		$this->emailColumns = $emailColumns;
		$queryGenerator->setFields($emailFields);

		if ($selectedIds && !empty($selectedIds) && $selectedIds != 'all') {
			if (!empty($selectedIds) && count($selectedIds) > 0) {
				$queryGenerator->setCustomCondition([
					'tablename' => $baseTableName,
					'column' => $baseTableId,
					'operator' => 'IN',
					'value' => '(' . implode(',', $selectedIds) . ')',
					'glue' => 'AND'
				]);
			}
		}
		$listQuery = $queryGenerator->getQuery();
		if ($excludedIds && !empty($excludedIds) && is_array($excludedIds) && count($excludedIds) > 0) {
			$listQuery .= ' && ' . $baseTableName . '.' . $baseTableId . ' NOT IN (' . implode(',', $excludedIds) . ')';
		}
		$this->query = $listQuery;
		return $listQuery;
	}
}
