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
		$this->exposeMethod('showChat');
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
		$mid = $request->getInteger('mid');
		$chat = \App\Chat::getInstance($request->getByType('roomType'), $request->getInteger('recordId'));
		if (!$chat->isRoomExists()) {
			throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE', 406);
		}
		$chat->addMessage($request->get('message'));
		$viewer = $this->getViewer($request);
		$viewer->assign('CHAT_ENTRIES', $chat->getEntries($mid));
		$viewer->assign('CURRENT_ROOM', \App\Chat::getCurrentRoom());
		echo $viewer->view('Entries.tpl', $request->getModule(), true);
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
		if ($request->has('roomType') && $request->has('recordId')) {
			$roomType = $request->getByType('roomType');
			$recordId = $request->getInteger('recordId');
			\App\Chat::setCurrentRoom($roomType, $recordId);
		} else {
			$currentRoom = \App\Chat::getCurrentRoom();
			if (!$currentRoom || !isset($currentRoom['roomType']) || !isset($currentRoom['recordId'])) {
				throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE', 406);
			}
			$roomType = $currentRoom['roomType'];
			$recordId = $currentRoom['recordId'];
		}
		$chat = \App\Chat::getInstance($roomType, $recordId);
		if (!$chat->isRoomExists()) {
			throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE', 406);
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('CHAT_ENTRIES', $chat->getEntries());
		$viewer->assign('CURRENT_ROOM', \App\Chat::getCurrentRoom());
		$viewer->view('Entries.tpl', $request->getModule());
	}

	/**
	 * Show chat for record.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \App\Exceptions\NoPermittedToRecord
	 *
	 * @return \html
	 */
	public function showChat(\App\Request $request)
	{
		$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('recordId'));
		if (!\App\Privilege::isPermitted('Chat') || !$recordModel->isViewable()) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}

		$chat = \App\Chat::getInstance('crm', $recordModel->getId());
		if (!$chat->isRoomExists()) {
			throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE', 406);
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('CHAT_ENTRIES', $chat->getEntries());
		$viewer->assign('CURRENT_ROOM', \App\Chat::getCurrentRoom());
		$viewer->view('Entries.tpl', $request->getModule());

		/*$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'));
		if (!\App\Privilege::isPermitted('Chat') || !$recordModel->isViewable()) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('CHAT', \App\Chat::getInstanceByRecordModel($recordModel));
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('RECORD_MODEL', $recordModel);
		return $viewer->view('Detail\Chat.tpl', 'Chat', true);*/
	}

	/**
	 * {@inheritdoc}
	 */
	public function isSessionExtend()
	{
		return false;
	}
}
