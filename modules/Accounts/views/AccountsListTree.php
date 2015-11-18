<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Accounts_AccountsListTree_View extends Vtiger_Index_View {
	private $src_module;
	private $src_record;
	private $moduleName;
	private $template;
	private $lastIdinTree;
	
	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		if(!Users_Privileges_Model::isPermitted($moduleName, $actionName)) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}
	}
	private function getTemplate()
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT fieldparams FROM vtiger_field WHERE uitype = ? AND tabid = ?', [302, Vtiger_Functions::getModuleId($this->moduleName)]);
		return $db->getSingleValue($result);
	}

	
	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		
		$this->moduleName = 'Products';
		$this->src_record = $request->get('src_record');
		$this->src_module = $request->get('src_module');
		$this->template = $this->getTemplate();
		if ($this->template) {
			$recordModel = Settings_TreesManager_Record_Model::getInstanceById($this->template);
		} else {
			Vtiger_Functions::throwNewException(vtranslate('ERR_TREE_NOT_FOUND', $this->moduleName));
		}
		if (!$recordModel)
			Vtiger_Functions::throwNewException(vtranslate('ERR_TREE_NOT_FOUND', $this->moduleName));
		$tree = $this->getCategory();
		$treeWithItems = [];
		$tree = array_merge($tree, $treeWithItems);
		$viewer->assign('TREE', Zend_Json::encode($tree));	
		$viewer->view('AccountsListTree.tpl', $moduleName);
	}
	private function getSelectedRecords()
	{
		$parentRecordModel = Vtiger_Record_Model::getInstanceById($this->src_record, $this->src_module);
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $this->moduleName);
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('limit', 'no_limit');
		$entries = $relationListView->getEntries($pagingModel);
		return array_keys($entries);
	}
	
	private function getCategory()
	{
		$tree = [];
		$adb = PearDatabase::getInstance();
		$lastId = 0;
		$result = $adb->pquery('SELECT * FROM vtiger_trees_templates_data WHERE templateid = ?', [$this->template]);
		while ($row = $adb->getRow($result)) {
			$treeID = (int) str_replace('T', '', $row['tree']);
			$cut = strlen('::' . $row['tree']);
			$parenttrre = substr($row['parenttrre'], 0, - $cut);
			$pieces = explode('::', $parenttrre);
			$parent = (int) str_replace('T', '', end($pieces));
			$tree[] = [
				'id' => $treeID,
				'record_id' => $row['tree'],
				'parent' => $parent == 0 ? '#' : $parent,
				'text' => vtranslate($row['name'], $this->moduleName),
				'state' => ($row['state']) ? $row['state'] : ''
			];
			if ($treeID > $lastId) {
				$lastId = $treeID;
			}
		}
		$this->lastIdinTree = $lastId;
		return $tree;
	}
	
	private function getRecords()
	{
		$selectedRecords = $this->getSelectedRecords();
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('limit', 'no_limit');
		$listViewModel = Vtiger_ListView_Model::getInstanceForPopup($this->moduleName);
		if (!empty($this->src_module)) {
			$listViewModel->set('src_module', $this->src_module);
			$listViewModel->set('src_record', $this->src_record);
		}
		$listEntries = $listViewModel->getListViewEntries($pagingModel, true);
		$tree = [];
		foreach ($listEntries as $item) {
			$this->lastIdinTree++;
			$parent = $item->get('pscategory');
			$parent = (int) str_replace('T', '', $parent);
			$tree[] = [
				'id' => $this->lastIdinTree,
				'record_id' => $item->getId(),
				'parent' => $parent == 0 ? '#' : $parent,
				'text' => $item->getName(),
				'state' => ['selected' => in_array($item->getId(), $selectedRecords)],
				'icon' => 'glyphicon glyphicon-file'
			];
		}
		return $tree;
	}
	public function getFooterScripts(Vtiger_Request $request) 
	{
		$parentScriptInstances = parent::getFooterScripts($request);
		$scripts = [
			'~libraries/jquery/jstree/jstree.min.js',
			'modules.Accounts.resources.AccountsListTree'
		];
		$viewInstances = $this->checkAndConvertJsScripts($scripts);
		$scriptInstances = array_merge($viewInstances, $parentScriptInstances);
		return $scriptInstances;
	}

	public function getCss(Vtiger_Request $request)
	{
		$parentCssInstances = parent::getCss($request);
		$cssFileNames = [
			'~libraries/jquery/jstree/themes/proton/style.css',
		];
		$modalInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$cssInstances = array_merge($modalInstances, $parentCssInstances);
		return $cssInstances;
	}
}
