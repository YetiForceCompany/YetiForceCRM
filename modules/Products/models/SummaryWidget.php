<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class Products_SummaryWidget_Model
{

	const MODULES = ['Products', 'OutsourcedProducts', 'Assets', 'Services', 'OSSOutsourcedServices', 'OSSSoldServices'];
	const CATEGORY_MODULES = ['Products', 'OutsourcedProducts', 'Services', 'OSSOutsourcedServices'];

	public static function getCleanInstance()
	{
		$instance = new self();
		return $instance;
	}

	public function getProductsServices(Vtiger_Request $request, Vtiger_Viewer $viewer)
	{
		$fromModule = $request->get('fromModule');
		$record = $request->get('record');
		$mod = $request->get('mod');
		if (!in_array($mod, self::MODULES)) {
			throw new \Exception\AppException('Not supported Module');
		}
		$limit = 10;
		if (!empty($request->get('limit'))) {
			$limit = $request->get('limit');
		}
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', 0);
		$pagingModel->set('limit', $limit);

		$parentRecordModel = Vtiger_Record_Model::getInstanceById($record, $fromModule);
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $mod);
		$recordsModels = $relationListView->getEntries($pagingModel);
		$recordsHeader = $relationListView->getHeaders();
		array_splice($recordsHeader, 3);
		$viewer->assign('RELATED_RECORDS', $recordsModels);
		$viewer->assign('RELATED_HEADERS', $recordsHeader);
		if (in_array($mod, self::CATEGORY_MODULES)) {
			$viewer->assign('RELATED_HEADERS_TREE', $relationListView->getTreeHeaders());
			$viewer->assign('RELATED_RECORDS_TREE', $relationListView->getTreeEntries());
		}
		$viewer->assign('RECORD_PAGING_MODEL', $pagingModel);
	}

	/**
	 * Get releted modules record counts
	 * @param Vtiger_Record_Model $parentRecordModel
	 * @return type
	 */
	public static function getModulesAndCount(Vtiger_Record_Model $parentRecordModel)
	{
		$modules = [];
		foreach (self::MODULES as &$moduleName) {
			$count = 0;
			if (!\App\Privilege::isPermitted($moduleName)) {
				continue;
			}
			$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $moduleName);
			if (!$relationListView->getRelationModel()) {
				continue;
			}
			if (in_array($moduleName, self::CATEGORY_MODULES)) {
				$count += (int) $relationListView->getRelatedTreeEntriesCount();
			}
			$count += (int) $relationListView->getRelatedEntriesCount();
			$modules[$moduleName] = $count;
		}
		return $modules;
	}
}
