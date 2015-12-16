<?php

/**
 * Tree Category Class
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_TreeCategory_View extends Vtiger_BasicModal_View
{

	private $src_module;
	private $src_record;
	public $moduleName;
	private $template;
	private $fieldTemp = false;
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
			throw new NoPermittedToRecordException('LBL_PERMISSION_DENIED');
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

	/**
	 * Load tree field info
	 * @return array
	 */
	public function getTreeField()
	{
		if($this->fieldTemp){
			return $this->fieldTemp;
		}
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT tablename,columnname,fieldname,fieldlabel,fieldparams FROM vtiger_field WHERE uitype = ? AND tabid = ?', [302, Vtiger_Functions::getModuleId($this->moduleName)]);
		$this->fieldTemp = $db->getRow($result);
		return $this->fieldTemp;
	}
	
	public function getTemplate()
	{
		$field = $this->getTreeField();
		return $field['fieldparams'];
	}

	private function getCategory()
	{
		$trees = [];
		$db = PearDatabase::getInstance();
		$lastId = 0;
		$result = $db->pquery('SELECT tr.*,rel.crmid  FROM vtiger_trees_templates_data tr '
			. 'LEFT JOIN u_yf_crmentity_rel_tree rel ON rel.tree = tr.tree '
			. 'WHERE tr.templateid = ?', [$this->template]);
		while ($row = $db->getRow($result)) {
			$treeID = (int) ltrim($row['tree'], 'T');
			$pieces = explode('::', $row['parenttrre']);
			end($pieces);
			$parent = (int) ltrim(prev($pieces), 'T');
			$tree = [
				'id' => $treeID,
				'type' => 'category',
				'record_id' => $row['tree'],
				'parent' => $parent == 0 ? '#' : $parent,
				'text' => vtranslate($row['name'], $this->moduleName)
			];
			if (!empty($row['state'])) {
				$tree['state'] = $row['state'];
			}
			if (!empty($row['icon'])) {
				$tree['icon'] = $row['icon'];
			}
			if (!empty($row['crmid'])) {
				$tree['category'] = ['checked' => true];
			}
			$trees[] = $tree;
			if ($treeID > $lastId) {
				$lastId = $treeID;
			}
		}
		$this->lastIdinTree = $lastId;
		return $trees;
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

	private function getRecords()
	{
		$selectedRecords = $this->getSelectedRecords();
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('limit', 'no_limit');
		$listViewModel = Vtiger_ListView_Model::getInstanceForPopup($this->moduleName, $this->src_module);
		if (!empty($this->src_module)) {
			$listViewModel->set('src_module', $this->src_module);
			$listViewModel->set('src_record', $this->src_record);
		}
		$listEntries = $listViewModel->getListViewEntries($pagingModel, true);
		$tree = [];
		foreach ($listEntries as $item) {
			$this->lastIdinTree++;
			$parent = (int) ltrim($item->get('pscategory'), 'T');
			$tree[] = [
				'id' => $this->lastIdinTree,
				'type' => 'record',
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
			'~libraries/jquery/jstree/jstree.js',
			'~libraries/jquery/jstree/jstree.category.js',
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
