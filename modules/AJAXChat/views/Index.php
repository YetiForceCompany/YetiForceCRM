<?php

class AJAXChat_Index_View extends Vtiger_Basic_View
{

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		parent::preProcess($request, false);
	}

	public function postProcess(Vtiger_Request $request)
	{
		
	}

	function checkPermission(Vtiger_Request $request)
	{
		return true;
	}

	public function process(Vtiger_Request $request)
	{
		global $site_URL;
		$shortURL = str_replace('index.php', '', $site_URL);
		$viewer = $this->getViewer($request);
		$viewer->assign('URLCSS', $shortURL . "layouts/vlayout/modules/AJAXChat/Chat.css");
		$viewer->assign('URL', $shortURL . "libraries/AJAXChat/index.php");
		$viewer->view('Index.tpl', 'AJAXChat');
	}
}
