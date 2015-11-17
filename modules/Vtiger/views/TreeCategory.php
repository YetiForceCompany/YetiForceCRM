<?php

/**
 * Tree Category Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_TreeCategory_View extends Vtiger_BasicModal_View
{

	private $src_module;
	private $src_record;
	private $moduleName;
	private $template;
	private $lastIdinTree;

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException(vtranslate($moduleName) . ' ' . vtranslate('LBL_NOT_ACCESSIBLE'));
		}

		if (!Users_Privileges_Model::isPermitted($request->get('src_module'), 'Detail', $request->get('src_record'))) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$this->preProcess($request);
		$viewer = $this->getViewer($request);
		$moduleName = $this->moduleName = $request->getModule();
		$srcRecord = $this->src_record = $request->get('src_record');
		$srcModule = $this->src_module = $request->get('src_module');
		$template = $this->template = $this->getTemplate();

		if ($template) {
			$recordModel = Settings_TreesManager_Record_Model::getInstanceById($template);
		} else {
			Vtiger_Functions::throwNewException(vtranslate('ERR_TREE_NOT_FOUND', $moduleName));
		}
		if (!$recordModel)
			Vtiger_Functions::throwNewException(vtranslate('ERR_TREE_NOT_FOUND', $moduleName));

		$tree = $this->getCategory();
		$treeWithItems = $this->getRecords();
		$tree = array_merge($tree, $treeWithItems);

		$viewer->assign('TREE', Zend_Json::encode($tree));
		$viewer->assign('SRC_RECORD', $srcRecord);
		$viewer->assign('SRC_MODULE', $srcModule);
		$viewer->assign('TEMPLATE', $template);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('TreeCategory.tpl', $moduleName);
		$this->postProcess($request);
	}

	private function getTemplate()
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT fieldparams FROM vtiger_field WHERE uitype = ? AND tabid = ?', [302, Vtiger_Functions::getModuleId($this->moduleName)]);
		return $db->getSingleValue($result);
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

	private function getSelectedRecords()
	{
		$parentRecordModel = Vtiger_Record_Model::getInstanceById($this->src_record, $this->src_module);
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $this->moduleName);
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('noLimit', true);
		$entries = $relationListView->getEntries($pagingModel);
		return array_keys($entries);
	}

	private function getRecords()
	{
		$selectedRecords = $this->getSelectedRecords();
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('noLimit', true);
		$listViewModel = Vtiger_ListView_Model::getInstanceForPopup($this->moduleName, $this->src_module);
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

	public function getModalScripts(Vtiger_Request $request)
	{
		$parentScriptInstances = parent::getModalScripts($request);

		$scripts = [
			'~libraries/jquery/jstree/jstree.min.js',
			'modules.Vtiger.resources.TreeCategory'
		];

		$modalInstances = $this->checkAndConvertJsScripts($scripts);
		$scriptInstances = array_merge($modalInstances, $parentScriptInstances);
		return $scriptInstances;
	}

	public function getModalCss(Vtiger_Request $request)
	{
		$parentCssInstances = parent::getModalCss($request);
		$cssFileNames = [
			'~libraries/jquery/jstree/themes/proton/style.css',
		];
		$modalInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$cssInstances = array_merge($modalInstances, $parentCssInstances);
		return $cssInstances;
	}
}
