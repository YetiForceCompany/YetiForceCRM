<?php
class AJAXChat_Index_View extends Vtiger_Basic_View {
	public function preProcess(Vtiger_Request $request) { parent::preProcess($request, false); }
	public function postProcess(Vtiger_Request $request) {}
	function checkPermission(Vtiger_Request $request) {
		return true;
	}
	public function process(Vtiger_Request $request) {
		global $site_URL;
		$viewer = $this->getViewer($request);
        $viewer->assign('URLCSS', $site_URL."layouts/vlayout/modules/AJAXChat/Chat.css");
        $viewer->assign('URL', $site_URL."libraries/AJAXChat/index.php");
		$viewer->view('Index.tpl', 'AJAXChat');
	}
}