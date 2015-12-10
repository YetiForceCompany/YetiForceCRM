<?php

/**
 * Accounts List Tree Category Class
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Accounts_AccountsListTree_View extends Vtiger_Index_View
{

	private $modules = ['Products','Services'];
	private $moduleName = 'Products';
	private $template;
	private $lastTreeId;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showTree');
		$this->exposeMethod('showAccountsList');
	}

	public function process(Vtiger_Request $request)
	{
		$mode = $request->get('mode');
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		if (!Users_Privileges_Model::isPermitted($moduleName, $actionName)) {
			throw new NoPermittedException(vtranslate('LBL_PERMISSION_DENIED'));
		}
	}

	private function getTemplate()
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT fieldparams FROM vtiger_field WHERE uitype = ? AND tabid = ?', [302, Vtiger_Functions::getModuleId($this->moduleName)]);
		return $db->getSingleValue($result);
	}

	public function showTree(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$sourceModule = $request->get('selectedModule');
		$viewer = $this->getViewer($request);

		$this->template = $this->getTemplate();
		if ($this->template) {
			$recordModel = Settings_TreesManager_Record_Model::getInstanceById($this->template);
		} else {
			Vtiger_Functions::throwNewException(vtranslate('ERR_TREE_NOT_FOUND', $moduleName));
		}
		if (!$recordModel)
			Vtiger_Functions::throwNewException(vtranslate('ERR_TREE_NOT_FOUND', $moduleName));
		if (!in_array($sourceModule, $this->modules))
			Vtiger_Functions::throwNewException(vtranslate('ERR_MODULE_NOT_FOUND', $moduleName));

		$tree = $this->getCategory();
		$treeWithItems = $this->getRecords();
		$tree = array_merge($tree, $treeWithItems);
		$viewer->assign('TREE', Zend_Json::encode($tree));
		$viewer->assign('MODULES', $this->modules);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('SELECTED_MODULE_NAME', $sourceModule);
		$viewer->assign('CUSTOM_VIEWS', CustomView_Record_Model::getAllByGroup($moduleName));
		$viewer->view('AccountsListTree.tpl', $moduleName);
	}

	public function showAccountsList(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$selected = $request->get('selected');
		$sourceModule = $request->get('selectedModule');
		$filter = $request->get('selectedFilter');
		$records = [];
		if(empty($selected)){
			return;
		}

		$multiReferenceFirld = Vtiger_MultiReferenceValue_UIType::getFieldsByModules($moduleName, $sourceModule);
		if(count($multiReferenceFirld) === 0){
			return;
		}
		$multiReferenceFirld = reset($multiReferenceFirld);
		//var_dump($multiReferenceFirld);
		
		$searchParams = [
			['columns' => [[
				'columnname' => $multiReferenceFirld['tablename'].':'.$multiReferenceFirld['columnname'].':'.$multiReferenceFirld['fieldname'],
				'value' => implode(',', $selected),
				'column_condition' => '',
				'comparator' => 'c',
			]]],
		];
		
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('limit', 'no_limit');
		$listViewModel = Vtiger_ListView_Model::getInstance('Accounts', $filter);
		$listViewModel->set('search_key', $multiReferenceFirld['fieldname']);
		$listViewModel->set('search_params',$searchParams);

		$listEntries = $listViewModel->getListViewEntries($pagingModel, true);
		if(count($listEntries) === 0){
			return;
		}
		$listHeaders = $listViewModel->getListViewHeaders();
	
		$viewer = $this->getViewer($request);
		$viewer->assign('ENTRIES', $listEntries);
		$viewer->assign('HEADERS', $listHeaders);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('AccountsList.tpl', $moduleName);
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
				'state' => ($row['state']) ? $row['state'] : '',
				'icon' => $row['icon']
			];
			if ($treeID > $lastId) {
				$lastId = $treeID;
			}
		}
		$this->lastTreeId = $lastId;
		return $tree;
	}

	private function getRecords()
	{
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('limit', 'no_limit');
		$listViewModel = Vtiger_ListView_Model::getInstance($this->moduleName);
		$listEntries = $listViewModel->getListViewEntries($pagingModel, true);
		$tree = [];
		foreach ($listEntries as $item) {
			$this->lastTreeId++;
			$parent = $item->get('pscategory');
			$parent = (int) str_replace('T', '', $parent);
			$tree[] = [
				'id' => $this->lastTreeId,
				'record_id' => $item->getId(),
				'parent' => $parent == 0 ? '#' : $parent,
				'text' => $item->getName(),
				'state' => [],
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
		];
		$viewInstances = $this->checkAndConvertJsScripts($scripts);
		$scriptInstances = array_merge($viewInstances, $parentScriptInstances);
		return $scriptInstances;
	}

	public function getHeaderCss(Vtiger_Request $request)
	{
		$parentCssInstances = parent::getHeaderCss($request);
		$cssFileNames = [
			'~libraries/jquery/jstree/themes/proton/style.css',
		];
		$modalInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$cssInstances = array_merge($modalInstances, $parentCssInstances);
		return $cssInstances;
	}
}
