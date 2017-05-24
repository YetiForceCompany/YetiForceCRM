<?php

/**
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class AJAXChat_Index_View extends Vtiger_Basic_View
{

	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request, false);
	}

	public function postProcess(\App\Request $request)
	{
		
	}

	public function checkPermission(\App\Request $request)
	{
		return true;
	}

	public function process(\App\Request $request)
	{
		$shortURL = str_replace('index.php', '', AppConfig::main('site_URL'));
		$viewer = $this->getViewer($request);
		$viewer->assign('URLCSS', $shortURL . \App\Layout::getLayoutFile('modules/AJAXChat/Chat.css'));
		$viewer->assign('URL', App\Layout::getPublicUrl('libraries/AJAXChat/index.php'));
		$viewer->view('Index.tpl', 'AJAXChat');
	}
}
