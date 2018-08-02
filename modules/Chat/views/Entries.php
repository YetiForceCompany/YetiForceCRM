<?php

/**
 * Chat Entries View Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Chat_Entries_View extends Vtiger_IndexAjax_View
{
	/**
	 * Construct.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('get');
	}

	/**
	 * Get entries function.
	 *
	 * @param \App\Request $request
	 */
	public function get(\App\Request $request)
	{
		$viewer = Vtiger_Viewer::getInstance();
		$viewer->assign('CHAT_ENTRIES', (new Chat_Module_Model())->getEntries($request->getInteger('cid')));
		$viewer->view('Items.tpl', 'Chat');
	}

	/**
	 * {@inheritdoc}
	 */
	public function isSessionExtend()
	{
		return false;
	}
}
