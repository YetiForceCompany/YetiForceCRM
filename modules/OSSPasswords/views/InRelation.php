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

class OSSPasswords_InRelation_View extends Vtiger_RelatedList_View
{

	function process(Vtiger_Request $request)
	{
		$targetModuleName = $request->getModule();
		if ($targetModuleName == 'Assets' ||
			$targetModuleName == 'Accounts' ||
			$targetModuleName == 'Contacts' ||
			$targetModuleName == 'Leads' ||
			$targetModuleName == 'Products' ||
			$targetModuleName == 'Services' ||
			$targetModuleName == 'HelpDesk' ||
			$targetModuleName == 'Vendors') {

			$moduleName = $request->getModule();
			$relatedModuleName = $request->get('relatedModule');
			$parentId = $request->get('record');
			$label = $request->get('tab_label');
			$requestedPage = $request->get('page');
			if (empty($requestedPage)) {
				$requestedPage = 1;
			}

			$pagingModel = new Vtiger_Paging_Model();
			$pagingModel->set('page', $requestedPage);

			$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
			$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, $label);
			$orderBy = $request->get('orderby');
			$sortOrder = $request->get('sortorder');
			if (empty($orderBy) && empty($sortOrder)) {
				$moduleInstance = CRMEntity::getInstance($relatedModuleName);
				$orderBy = $moduleInstance->default_order_by;
				$sortOrder = $moduleInstance->default_sort_order;
			}
			if ($sortOrder == 'ASC') {
				$nextSortOrder = 'DESC';
				$sortImage = 'glyphicon glyphicon-chevron-down';
			} else {
				$nextSortOrder = 'ASC';
				$sortImage = 'glyphicon glyphicon-chevron-up';
			}

			if (!empty($orderBy)) {
				$relationListView->set('orderby', $orderBy);
				$relationListView->set('sortorder', $sortOrder);
			}
			$models = $relationListView->getEntries($pagingModel);
			$links = $relationListView->getLinks();
			$header = $relationListView->getHeaders();
			$noOfEntries = count($models);

			$relationModel = $relationListView->getRelationModel();
			$relatedModuleModel = $relationModel->getRelationModuleModel();
			$relationField = $relationModel->getRelationField();

			$viewer = $this->getViewer($request);
			$viewer->assign('RELATED_RECORDS', $models);
			$viewer->assign('PARENT_RECORD', $parentRecordModel);
			$viewer->assign('RELATED_LIST_LINKS', $links);
			$viewer->assign('RELATED_HEADERS', $header);
			$viewer->assign('RELATED_MODULE', $relatedModuleModel);
			$viewer->assign('RELATED_ENTIRES_COUNT', $noOfEntries);
			$viewer->assign('RELATION_FIELD', $relationField);

			if (PerformancePrefs::getBoolean('LISTVIEW_COMPUTE_PAGE_COUNT', false)) {
				$totalCount = $relationListView->getRelatedEntriesCount();
				$pageLimit = $pagingModel->getPageLimit();
				$pageCount = ceil((int) $totalCount / (int) $pageLimit);

				$viewer->assign('PAGE_COUNT', $pageCount);
				$viewer->assign('TOTAL_ENTRIES', $totalCount);
				$viewer->assign('PERFORMANCE', true);
			}

			$viewer->assign('SOURCEMODULE', $relatedModuleName);
			$viewer->assign('MODULE', $moduleName);
			$viewer->assign('PAGING', $pagingModel);

			$viewer->assign('ORDER_BY', $orderBy);
			$viewer->assign('SORT_ORDER', $sortOrder);
			$viewer->assign('NEXT_SORT_ORDER', $nextSortOrder);
			$viewer->assign('SORT_IMAGE', $sortImage);
			$viewer->assign('COLUMN_NAME', $orderBy);

			$viewer->assign('IS_EDITABLE', $relationModel->isEditable());
			$viewer->assign('IS_DELETABLE', $relationModel->isDeletable());
			$viewer->assign('VIEW', $request->get('view'));

			$record = $request->get('record');
			$view = $request->get('view');

			// check if passwords are encrypted
			if (file_exists('modules/OSSPasswords/config.ini')) {   // encryption key exists so passwords are encrypted
				$config = parse_ini_file('modules/OSSPasswords/config.ini');

				// let smarty know that passwords are encrypted
				$viewer->assign('ENCRYPTED', true);
				$viewer->assign('ENC_KEY', $config['key']);
				$viewer->assign('RECORD', $record);
				$viewer->assign('VIEW', $view);
			} else {
				$viewer->assign('ENCRYPTED', false);
				$viewer->assign('ENC_KEY', '');
				$viewer->assign('RECORD', $record);
				$viewer->assign('VIEW', $view);
			}

			return $viewer->view('ViewRelatedList.tpl', 'OSSPasswords', 'true');
		} else {
			return parent::process($request);
		}
	}
}
