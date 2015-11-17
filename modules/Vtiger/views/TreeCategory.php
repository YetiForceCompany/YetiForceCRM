<?php
/**
 * Tree Category Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

class Vtiger_TreeCategory_View extends Vtiger_Footer_View {
	private $lastIdinTree;
	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPrivilegesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException(vtranslate($moduleName).' '.vtranslate('LBL_NOT_ACCESSIBLE'));
		}	
		
		if (!Users_Privileges_Model::isPermitted($request->get('src_module'), 'Detail', $request->get('src_record'))) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}	

	function process (Vtiger_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();
		$template = $request->get('template');
		$srcField = $request->get('src_field');
		$srcRecord = $request->get('src_record');
		$srcModule = $request->get('src_module');	
		$template = 2;
		
		if(!empty($template)) {
			$recordModel = Settings_TreesManager_Record_Model::getInstanceById($template);
		} else {
			Vtiger_Functions::throwNewException(vtranslate('ERR_TREE_NOT_FOUND', $moduleName));
		}
		if(!$recordModel)
			Vtiger_Functions::throwNewException(vtranslate('ERR_TREE_NOT_FOUND', $moduleName));	
		
		$tree = $this->getCategory($template,$moduleName);				
		$treeWithItems = $this->getRecords($moduleName, $srcModule,$srcField,$srcRecord);
		$tree = array_merge($tree,$treeWithItems);		
		
		$viewer->assign('TREE', Zend_Json::encode($tree));
		$viewer->assign('SRC_RECORD', $srcRecord);
		$viewer->assign('SRC_FIELD', $srcField);
		$viewer->assign('TEMPLATE', $template);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('TRIGGER_EVENT_NAME', $request->get('triggerEventName'));
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('TreeCategory.tpl', $moduleName);
	}
	private function getCategory($templateId,$module){
		$tree = [];		
		if (empty($templateId)){
			return $tree;
		}
		$adb = PearDatabase::getInstance();
		$lastId = 0;
		$result = $adb->pquery('SELECT * FROM vtiger_trees_templates_data WHERE templateid = ?', [$templateId]);		
		if (is_numeric($module)) {
			$module = Vtiger_Functions::getModuleName($module);
		}
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
				'text' => vtranslate($row['name'], $module),
				'state' => ($row['state']) ? $row['state'] : ''
			];
			if ($treeID > $lastId){
				$lastId = $treeID;
			}
		}
		$this->lastIdinTree = $lastId;
		return $tree;
	}
	private function getRecords($moduleName,$srcModule,$srcField,$srcRecord){
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('noLimit', true);		
		$listViewModel = Vtiger_ListView_Model::getInstanceForPopup($moduleName, $srcModule);		
		if(!empty($srcModule)) {
			$listViewModel->set('src_module', $srcModule);
			$listViewModel->set('src_field', $srcField);
			$listViewModel->set('src_record', $srcRecord);
		}
		$listEntries = $listViewModel->getListViewEntries($pagingModel);		
		foreach ($listEntries as $item){
			$this->lastIdinTree++;
			$parent = $item->get('pscategory');
			$parent = (int) str_replace("T", "", $parent);
			$tree[]=['id' => $this->lastIdinTree,
				'record_id' => $item->getId(),
				'parent' => $parent == 0 ? '#' : $parent,
				'text' => $item->get('productname'),
				'state' => '',
				'icon' => 'glyphicon glyphicon-file'];
		}	
		return $tree;
	} 
	function postProcess(Vtiger_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $request->get("module");
		$viewer->assign('FOOTER_SCRIPTS',$this->getFooterScripts($request));
		$viewer->view('PopupFooter.tpl', $moduleName);
	}
	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getFooterScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(		
			'~libraries/jquery/jstree/jstree.min.js',
			'modules.Vtiger.resources.TreeCategory'
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
	
	function getHeaderCss(Vtiger_Request $request) {
		$headerCssInstances = parent::getHeaderCss($request);
		$moduleName = $request->getModule();
		$cssFileNames = array(
			'~libraries/jquery/jstree/themes/default/style.css',
		);
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($cssInstances, $headerCssInstances);
		return $headerCssInstances;
	}
	
	protected function showBodyHeader()
	{
		return false;
	}
}
