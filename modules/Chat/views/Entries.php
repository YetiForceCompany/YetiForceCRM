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
class Chat_Entries_View extends \App\Controller\View
{
	use \App\Controller\ExposeMethod;

	/**
	 * Constructor with a list of allowed methods.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('send');
		$this->exposeMethod('get');
	}

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('ERR_NOT_ACCESSIBLE', 406);
		}
	}

	/**
	 * Send message function.
	 *
	 * @param \App\Request $request
	 */
	public function send(\App\Request $request)
	{
		$roomType = $request->getByType('roomType');
		$roomId = $request->getInteger('roomId');
		$chat = \App\Chat::getInstance($roomType, $roomId);
		if (!$chat->isRoomExists()) {
			throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE', 406);
		}
		echo $request->get('message');
//		$viewer = $this->getViewer($request);
//		$viewer->view('Modal.tpl', $request->getModule());
	}

	/**
	 * Get messages from chat.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function get(\App\Request $request)
	{
		if ($request->has('roomType') && $request->has('roomId')) {
			$roomType = $request->getByType('roomType');
			$roomId = $request->getInteger('roomId');
			\App\Chat::setCurrentRoom($roomType, $roomId);
		} else {
			$currentRoom = \App\Chat::getCurrentRoom();
			if (!$currentRoom || !isset($currentRoom['roomType']) || !isset($currentRoom['roomId'])) {
				throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE', 406);
			}
			$roomType = $currentRoom['roomType'];
			$roomId = $currentRoom['roomId'];
		}
		$chat = \App\Chat::getInstance($roomType, $roomId);
		if (!$chat->isRoomExists()) {
			throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE', 406);
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('CHAT_ENTRIES', $chat->getEntries());
		$viewer->assign('CURRENT_ROOM', \App\Chat::getCurrentRoom());
		$viewer->view('Entries.tpl', $request->getModule());
	}

	/**
	 * {@inheritdoc}
	 */
	public function isSessionExtend()
	{
		return false;
	}
}
