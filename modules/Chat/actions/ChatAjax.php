<?php

/**
 * Action to get Chat data.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
		$this->exposeMethod('getChatConfig');
		$this->exposeMethod('getMessages');
		$this->exposeMethod('getMoreMessages');
		$this->exposeMethod('getRoomsMessages');
		$this->exposeMethod('getUnread');
		$this->exposeMethod('getHistory');
		$this->exposeMethod('getRooms');
		$this->exposeMethod('getRoomsUnpinned');
		$this->exposeMethod('getRecordRoom');
		$this->exposeMethod('getRoomPrivateUnpinnedUsers');
		$this->exposeMethod('send');
		$this->exposeMethod('search');
		$this->exposeMethod('addPrivateRoom');
		$this->exposeMethod('archivePrivateRoom');
		$this->exposeMethod('addParticipant');
	}

	/** {@inheritdoc} */
	public function isSessionExtend(App\Request $request)
	{
		return false;
	}

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$userPrivileges = \Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPrivileges->hasModulePermission($request->getModule()) || $userPrivileges->getId() !== $userPrivileges->getRealId()) {
			throw new \App\Exceptions\NoPermitted('ERR_NOT_ACCESSIBLE', 406);
		}
	}

	/**
	 * Get chat init data.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function getChatConfig(App\Request $request)
	{
		$userRealID = \App\User::getCurrentUserRealId();
		$userRoomPin = \App\Config::module('Chat', 'userRoomPin');
		if (!$userRoomPin) {
			\App\Chat::pinAllUsers($userRealID);
		}
		$result = [
			'config' => [
				'isChatAllowed' => $userRealID === \App\User::getCurrentUserId(),
				'isAdmin' => \Users_Privileges_Model::getCurrentUserPrivilegesModel()->isAdminUser(),
				'isDefaultSoundNotification' => \App\Config::module('Chat', 'DEFAULT_SOUND_NOTIFICATION'),
				'refreshMessageTime' => \App\Config::module('Chat', 'REFRESH_MESSAGE_TIME'),
				'refreshRoomTime' => \App\Config::module('Chat', 'REFRESH_ROOM_TIME'),
				'defaultRoom' => \App\Chat::getDefaultRoom(),
				'dynamicAddingRooms' => \App\Config::module('Chat', 'dynamicAddingRooms'),
				'draggableButton' => false,
				'maxLengthMessage' => \App\Config::module('Chat', 'MAX_LENGTH_MESSAGE'),
				'refreshTimeGlobal' => \App\Config::module('Chat', 'REFRESH_TIME_GLOBAL'),
				'showNumberOfNewMessages' => \App\Config::module('Chat', 'SHOW_NUMBER_OF_NEW_MESSAGES'),
				'showRoleName' => \App\Config::module('Users', 'SHOW_ROLE_NAME'),
				'activeRoomTypes' => \App\Chat::getActiveRoomTypes(),
				'userRoomPin' => $userRoomPin
			],
			'roomList' => \App\Chat::getRoomsByUser(),
			'currentRoom' => \App\Chat::getCurrentRoom()
		];
		if ($result['config']['dynamicAddingRooms']) {
			$result['config']['chatModules'] = \App\Chat::getChatModules();
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Get messages from chat.
	 *
	 * @param \App\Request $request
	 */
	public function getMessages(App\Request $request)
	{
		$response = new Vtiger_Response();
		$response->setResult($this->setMessagesResult($request));
		$response->emit();
	}

	/**
	 *	Set messages result.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function setMessagesResult(App\Request $request)
	{
		if ($request->has('roomType') && $request->has('recordId')) {
			$roomType = $request->getByType('roomType');
			$recordId = $request->getInteger('recordId');
			if (!$request->getBoolean('recordRoom')) {
				\App\Chat::setCurrentRoom($roomType, $recordId);
			}
		} else {
			$currentRoom = \App\Chat::getCurrentRoom();
			if (!$currentRoom || !isset($currentRoom['roomType']) || !isset($currentRoom['recordId'])) {
				return false;
			}
			$roomType = $currentRoom['roomType'];
			$recordId = $currentRoom['recordId'];
		}
		$chat = \App\Chat::getInstance($roomType, $recordId);
		if (!$chat->isRoomExists()) {
			throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE', 406);
		}
		$chatEntries = $chat->getEntries($request->has('lastId') ? $request->getInteger('lastId') : 0);
		$isNextPage = $this->isNextPage(\count($chatEntries));
		if ($isNextPage) {
			array_shift($chatEntries);
		}
		$result = [];
		$roomList = \App\Chat::getRoomsByUser();
		if (!isset($roomList[$roomType][$recordId])) {
			$defaultRoom = \App\Chat::setCurrentRoomDefault();
			$roomType = $defaultRoom['roomType'];
			$recordId = $defaultRoom['recordId'];
		}
		$roomList[$roomType][$recordId]['chatEntries'] = $chatEntries;
		$roomList[$roomType][$recordId]['participants'] = $chat->getParticipants();
		if (!$request->has('lastId')) {
			$result['currentRoom'] = \App\Chat::getCurrentRoom();
			$roomList[$roomType][$recordId]['showMoreButton'] = $isNextPage;
		}
		$result['roomList'] = $roomList;

		if (App\Config::module('Chat', 'SHOW_NUMBER_OF_NEW_MESSAGES')) {
			$result['amountOfNewMessages'] = \App\Chat::getNumberOfNewMessages();
		}
		return $result;
	}

	/**
	 * Get rooms messages from chat.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function getRoomsMessages(App\Request $request)
	{
		$result = [];
		$roomList = \App\Chat::getRoomsByUser();
		$areNewEntries = false;

		foreach ($request->getArray('rooms') as $room) {
			$recordId = $room['recordid'];
			$roomType = $room['roomType'];
			$chat = \App\Chat::getInstance($roomType, $recordId);
			if (!$chat->isRoomExists()) {
				throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE', 406);
			}
			if ('private' === $roomType && !$chat->isPrivateRoomAllowed($recordId)) {
				continue;
			}
			$lastEntries = !empty($room['chatEntries']) ? array_pop($room['chatEntries']) : false;
			$chatEntries = $chat->getEntries($lastEntries ? $lastEntries['id'] : 0);
			$isNextPage = $this->isNextPage(\count($chatEntries));
			if ($isNextPage) {
				array_shift($chatEntries);
			}
			$roomList[$roomType][$recordId]['showMoreButton'] = $isNextPage;
			$roomList[$roomType][$recordId]['chatEntries'] = $chatEntries;
			$roomList[$roomType][$recordId]['participants'] = $chat->getParticipants();
			if (!$areNewEntries) {
				$areNewEntries = \count($chatEntries) > 0;
			}
		}
		$result['roomList'] = $roomList;
		$result['areNewEntries'] = $areNewEntries;
		if (App\Config::module('Chat', 'SHOW_NUMBER_OF_NEW_MESSAGES')) {
			$result['amountOfNewMessages'] = \App\Chat::getNumberOfNewMessages($roomList);
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Get more messages from chat.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \yii\db\Exception
	 */
	public function getMoreMessages(App\Request $request)
	{
		$chat = \App\Chat::getInstance($request->getByType('roomType'), $request->getInteger('recordId'));
		$chatEntries = $chat->getEntries($request->getInteger('lastId'), '<');
		$isNextPage = $this->isNextPage(\count($chatEntries));
		if ($isNextPage) {
			array_shift($chatEntries);
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'currentRoom' => \App\Chat::getCurrentRoom(),
			'chatEntries' => $chatEntries,
			'showMoreButton' => $isNextPage,
		]);
		$response->emit();
	}

	/**
	 * Send message function.
	 *
	 * @param \App\Request $request
	 */
	public function send(App\Request $request)
	{
		$roomType = $request->getByType('roomType');
		$recordId = $request->getInteger('recordId');
		$chat = \App\Chat::getInstance($roomType, $recordId);
		if (!$chat->isRoomExists()) {
			throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE', 406);
		}
		if ('private' === $roomType && !$chat->isPrivateRoomAllowed($recordId)) {
			$defaultRoom = \App\Chat::setCurrentRoomDefault();
			$result = [
				'message' => 'JS_CHAT_ROOM_NOT_ALLOWED',
				'data' => $this->setMessagesResult(new App\Request(['roomType' => $defaultRoom['roomType'], 'recordId' => $defaultRoom['recordId']]))
			];
		} else {
			$chat->addMessage(\App\Utils\Completions::encodeAll($request->getForHtml('message')));
			$chatEntries = $chat->getEntries($request->isEmpty('mid') ? null : $request->getInteger('mid'));
			$isNextPage = $this->isNextPage(\count($chatEntries));
			if ($isNextPage) {
				array_shift($chatEntries);
			}
			$result = [
				'chatEntries' => $chatEntries,
				'participants' => $chat->getParticipants(),
				'showMoreButton' => $isNextPage
			];
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Search meassages.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function search(App\Request $request)
	{
		$roomType = $request->getByType('roomType');
		$recordId = $request->getInteger('recordId');
		$chat = \App\Chat::getInstance($roomType, $recordId);
		$searchVal = $request->getByType('searchVal', 'Text');
		if (!$request->isEmpty('mid')) {
			$chatEntries = $chat->getEntries($request->getInteger('mid'), '<', $searchVal);
		} else {
			$chatEntries = $chat->getEntries(null, '>', $searchVal);
		}
		$isNextPage = $this->isNextPage(\count($chatEntries));
		if ($isNextPage) {
			array_shift($chatEntries);
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'chatEntries' => $chatEntries,
			'showMoreButton' => $isNextPage
		]);
		$response->emit();
	}

	/**
	 * Add private room.
	 *
	 * @param \App\Request $request
	 */
	public function addPrivateRoom(App\Request $request)
	{
		$chat = \App\Chat::getInstance();
		$chat->createPrivateRoom($request->getByType('name', 'Text'));
		$response = new Vtiger_Response();
		$response->setResult(\App\Chat::getRoomsByUser());
		$response->emit();
	}

	/**
	 * Archive rooms.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function archivePrivateRoom(App\Request $request)
	{
		$recordId = $request->getInteger('recordId');
		$chat = \App\Chat::getInstance('private', $recordId);
		if (!$chat->isRoomModerator($recordId)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (\App\Chat::getCurrentRoom()['recordId'] === $recordId) {
			\App\Chat::setCurrentRoomDefault();
		}
		$chat->archivePrivateRoom($recordId);
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	/**
	 * Add participant.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function addParticipant(App\Request $request)
	{
		$recordId = $request->getInteger('recordId');
		$chat = \App\Chat::getInstance('private', $recordId);
		if (!$chat->isRoomModerator($recordId)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$alreadyInvited = $chat->addParticipantToPrivate($request->getInteger('userId'));
		if ($alreadyInvited) {
			$result = ['message' => 'JS_CHAT_PARTICIPANT_INVITED'];
		} else {
			$result = $chat->getParticipants();
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Get all unread messages.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function getUnread(App\Request $request)
	{
		$unreadMessages = [];
		foreach (\App\Chat::getActiveRoomTypes() as $roomType) {
			$unreadMessages[$roomType] = \App\Chat::getUnreadByType($roomType);
		}
		$response = new Vtiger_Response();
		$response->setResult($unreadMessages);
		$response->emit();
	}

	/**
	 * Get history.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function getHistory(App\Request $request)
	{
		$chat = \App\Chat::getInstance();
		$groupHistory = $request->getByType('groupHistory', 2);
		if (!\in_array($groupHistory, \App\Chat::ALLOWED_ROOM_TYPES)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if ($request->isEmpty('mid')) {
			$chatEntries = $chat->getHistoryByType($groupHistory);
		} else {
			$chatEntries = $chat->getHistoryByType($groupHistory, $request->getInteger('mid'));
		}
		$isNextPage = $this->isNextPage(\count($chatEntries));
		if ($isNextPage) {
			array_shift($chatEntries);
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'chatEntries' => $chatEntries,
			'showMoreButton' => $isNextPage
		]);
		$response->emit();
	}

	/**
	 * Get rooms function.
	 *
	 * @param \App\Request $request
	 */
	public function getRooms(App\Request $request)
	{
		$response = new Vtiger_Response();
		$response->setResult([
			'roomList' => \App\Chat::getRoomsByUser()
		]);
		$response->emit();
	}

	/**
	 * Get rooms unpinned.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function getRoomsUnpinned(App\Request $request)
	{
		$roomType = $request->getByType('roomType');
		$methodName = 'getRooms' . ucfirst($roomType) . 'Unpinned';
		$response = new Vtiger_Response();
		$response->setResult(\App\Chat::{$methodName}());
		$response->emit();
	}

	/**
	 * Show chat for record.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function getRecordRoom(App\Request $request)
	{
		$recordId = $request->getInteger('id');
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
		if (!$recordModel->isViewable()) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$chat = \App\Chat::getInstance('crm', $recordId);
		$chatEntries = $chat->getEntries();
		$response = new Vtiger_Response();
		$response->setResult([
			'roomList' => [
				'crm' => [
					$recordId => [
						'roomType' => 'crm',
						'recordid' => $recordId,
						'active' => true,
						'recordRoom' => true,
						'chatEntries' => $chatEntries,
						'participants' => $chat->getParticipants(),
						'showMoreButton' => \count($chatEntries) > \App\Config::module('Chat', 'CHAT_ROWS_LIMIT')
					]
				]
			]
		]);
		$response->emit();
	}

	/**
	 * Get room private unpinned users.
	 *
	 * @param \App\Request $request
	 */
	public function getRoomPrivateUnpinnedUsers(App\Request $request)
	{
		$response = new Vtiger_Response();
		$response->setResult(\App\Chat::getRoomPrivateUnpinnedUsers($request->getInteger('roomId')));
		$response->emit();
	}

	/**
	 * Check if there are more messages.
	 *
	 * @param int $numberOfMessages
	 *
	 * @return bool
	 */
	private function isNextPage(int $numberOfMessages): bool
	{
		return $numberOfMessages >= \App\Config::module('Chat', 'CHAT_ROWS_LIMIT') + 1;
	}
}
