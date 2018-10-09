<?php

/**
 * Chat Entries View Class.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
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
		$roomId = $request->has('chat_room_id') ? $request->getInteger('chat_room_id') : \App\Chat::getCurrentRoomId();
		$viewer = Vtiger_Viewer::getInstance();
		$chatItems = \App\Chat::getInstanceById($roomId)->getEntries($request->getInteger('cid'));
		if (count($chatItems)) {
			$viewer->assign('CHAT_ENTRIES', $chatItems);
			$viewer->view('Items.tpl', 'Chat');
		} else {
			$response = new \Vtiger_Response();
			$response->setHeader('HTTP/1.1 304 Not Modified');
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function isSessionExtend()
	{
		return false;
	}
}
