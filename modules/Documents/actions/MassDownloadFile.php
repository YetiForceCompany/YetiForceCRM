<?php
/**
 * Action to mass download files.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Lorencik <m.lorencik@yetiforce.com>
 */
class Documents_MassDownloadFile_Action extends Vtiger_RelationAjax_Action
{
	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$records = [];
		$relatedModuleName = $request->getModule();
		$sourceModule = $request->getByType('sourceModule', 2);
		$sourceRecordId = $request->getInteger('src_record');
		$pagingModel = new Vtiger_Paging_Model();
		$parentRecordModel = Vtiger_Record_Model::getInstanceById($sourceRecordId, $sourceModule);
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName);
		$excludedIds = $request->getArray('excluded_ids', 'Integer');
		if ('all' === $request->getArray('selected_ids')[0]) {
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
				$relationListView->set('search_value', App\Condition::validSearchValue($request->getByType('search_value', 'Text'), $relatedModuleName, $searchKey, $operator));
			}
			$searchParmams = App\Condition::validSearchParams($relatedModuleName, $request->getArray('search_params'));
			if (empty($searchParmams) || !is_array($searchParmams)) {
				$searchParmams = [];
			}
			$transformedSearchParams = $relationListView->get('query_generator')->parseBaseSearchParamsToCondition($searchParmams);
			$relationListView->set('search_params', $transformedSearchParams);
			$rows = array_keys($relationListView->getEntries($pagingModel));
		} else {
			$rows = '[]' === $request->getRaw('selected_ids') ? [] : $request->getArray('selected_ids', 'Integer');
		}
		foreach ($rows as $id) {
			if (!in_array($id, $excludedIds) && \App\Privilege::isPermitted($relatedModuleName, 'DetailView', $id)) {
				$records[] = $id;
			}
		}
		if (1 === count($records)) {
			$documentRecordModel = Vtiger_Record_Model::getInstanceById($records[0], $relatedModuleName);
			$documentRecordModel->downloadFile();
			$documentRecordModel->updateDownloadCount();
		} else {
			Documents_Record_Model::downloadFiles($records);
		}
	}
}
