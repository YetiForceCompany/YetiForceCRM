<?php

/**
 * Action to get data of KnowledgeBase.
 *
 * @package Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Chat_ChatAjax_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('data');
		$this->exposeMethod('getEntries');
		$this->exposeMethod('send');
	}

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('ERR_NOT_ACCESSIBLE', 406);
		}
	}

	/**
	 * Details knowledge base.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function data(App\Request $request)
	{
		$chat = \App\Chat::getInstance();
		$chatEntries = $chat->getEntries();
		$response = new Vtiger_Response();
		$response->setResult([
			'chatEntries' => $chatEntries,
			'chat' => $chat,
			'showMoreButton' => count($chatEntries) > \App\Config::module('Chat', 'CHAT_ROWS_LIMIT'),
			'currentRoom' => \App\Chat::getCurrentRoom(),
			'roomList' => \App\Chat::getRoomsByUser(),
			'isModalView' => true,
			'isSoundNotification' => $this->isSoundNotification(),
			'isDesktopNotification' => $this->isDesktopNotification(),
			'sendByEnter' => $this->sendByEnter(),
			'refreshMessageTime' => App\Config::module('Chat', 'REFRESH_MESSAGE_TIME'),
			'refreshRoomTime' => App\Config::module('Chat', 'REFRESH_ROOM_TIME'),
			'maxLengthMessage' => App\Config::module('Chat', 'MAX_LENGTH_MESSAGE'),
			'refreshTimeGlobal' => App\Config::module('Chat', 'REFRESH_TIME_GLOBAL'),
			'showNumberOfNewMessages' => App\Config::module('Chat', 'SHOW_NUMBER_OF_NEW_MESSAGES')
		]);
		$response->emit();
	}

	/**
	 * Get messages from chat.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function getEntries(\App\Request $request)
	{
		if ($request->has('roomType') && $request->has('recordId')) {
			$roomType = $request->getByType('roomType');
			$recordId = $request->getInteger('recordId');
			if (!$request->getBoolean('viewForRecord')) {
				\App\Chat::setCurrentRoom($roomType, $recordId);
			}
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
			return;
		}
		$chatEntries = $chat->getEntries($request->has('lastId') ? $request->getInteger('lastId') : null);
		$numberOfEntries = count($chatEntries);
		if ($request->has('lastId') && !$numberOfEntries) {
			return;
		}
		$result = [
			'currentRoom' => \App\Chat::getCurrentRoom(),
			'chatEntries' => $chatEntries,
			'showMoreButton' => $numberOfEntries > \App\Config::module('Chat', 'CHAT_ROWS_LIMIT'),
		];
		if (!$request->has('lastId')) {
			$result['participants'] = $chat->getParticipants();
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Send message function.
	 *
	 * @param \App\Request $request
	 */
	public function send(\App\Request $request)
	{
		$chat = \App\Chat::getInstance($request->getByType('roomType'), $request->getInteger('recordId'));
		if (!$chat->isRoomExists()) {
			throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE', 406);
		}
		$chat->addMessage(\App\Utils\Completions::encodeAll($request->getForHtml('message')));
		$chatEntries = $chat->getEntries($request->isEmpty('mid') ? null : $request->getInteger('mid'));
		$response = new Vtiger_Response();
		$response->setResult([
			'chatEntries' => $chatEntries,
			'showMoreButton' => count($chatEntries) > \App\Config::module('Chat', 'CHAT_ROWS_LIMIT')
		]);
		$response->emit();
	}
	/**
	 * Check if sound notification is enabled.
	 *
	 * @return bool
	 */
	private function isSoundNotification(): bool
	{
		return isset($_COOKIE['chat-isSoundNotification']) ?
			filter_var($_COOKIE['chat-isSoundNotification'], FILTER_VALIDATE_BOOLEAN) : \App\Config::module('Chat', 'DEFAULT_SOUND_NOTIFICATION');
	}

	/**
	 * Check if desktop notification is enabled.
	 *
	 * @return bool
	 */
	private function isDesktopNotification(): bool
	{
		return isset($_COOKIE['chat-isDesktopNotification']) ?
			filter_var($_COOKIE['chat-isDesktopNotification'], FILTER_VALIDATE_BOOLEAN) : false;
	}

	/**
	 * Check if sending on ENTER is active.
	 *
	 * @return bool
	 */
	private function sendByEnter(): bool
	{
		return isset($_COOKIE['chat-notSendByEnter']) ?
			!filter_var($_COOKIE['chat-notSendByEnter'], FILTER_VALIDATE_BOOLEAN) : true;
	}
}
