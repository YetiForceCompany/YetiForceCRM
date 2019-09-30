<?php

/**
 * Chat test class.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Tests\Base;

/**
 * Class Chat.
 */
class Z_Chat extends \Tests\Base
{
	/**
	 * ID list.
	 *
	 * @var int[]
	 */
	private static $listId;

	/**
	 * Is chat active.
	 *
	 * @var bool
	 */
	private static $chatActive = false;

	/**
	 * Global room.
	 *
	 * @var bool|array
	 */
	private static $globalRoom = false;

	/**
	 * List of user IDs.
	 *
	 * @var int[]
	 */
	private static $users = [];

	/**
	 * Get key of chat room.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param array  $userRooms
	 * @param string $roomType
	 * @param int    $recordId
	 *
	 * @return bool|int
	 */
	private static function getKeyRoom($userRooms, string $roomType, int $recordId)
	{
		if (isset($userRooms[$roomType]) && \is_array($userRooms[$roomType])) {
			foreach ($userRooms[$roomType] as $key => $val) {
				if ($val['recordid'] === $recordId) {
					return $key;
				}
			}
		}
		return false;
	}

	/**
	 * Get key of message.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param array $entries
	 * @param int   $id
	 *
	 * @return int|false
	 */
	private static function getKeyMessage($entries, int $id)
	{
		foreach ($entries as $key => $val) {
			if ($val['id'] === $id) {
				return $key;
			}
		}
		return false;
	}

	/**
	 * Get user from participants.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param array $participants
	 * @param int   $userId
	 *
	 * @return bool|int|string
	 */
	private static function getUserFromParticipants($participants, int $userId)
	{
		foreach ($participants as $key => $val) {
			if ($val['user_id'] === $userId) {
				return $key;
			}
		}
		return false;
	}

	/**
	 * Is room pinned.
	 *
	 * @param string $roomType
	 * @param int    $recordId
	 * @param int    $userId
	 *
	 * @return bool
	 */
	private static function isRoomPinned(string $roomType, int $recordId, int $userId): bool
	{
		return (new \App\Db\Query())
			->select('userid')
			->from(\App\Chat::TABLE_NAME['room'][$roomType])
			->where(['and', ['userid' => $userId], [\App\Chat::COLUMN_NAME['room'][$roomType] => $recordId]])
			->exists();
	}

	/**
	 * @codeCoverageIgnore
	 * Setting of tests.
	 */
	public static function setUpBeforeClass()
	{
		static::$chatActive = \App\Module::isModuleActive('Chat');
		if (!static::$chatActive) {
			(new \Settings_ModuleManager_Module_Model())->enableModule('Chat');
		}
		\App\User::setCurrentUserId(\App\User::getActiveAdminId());
		$recordModel = C_RecordActions::createContactRecord();
		static::$listId[] = $recordModel->getId();
		static::$users[] = A_User::createUsersRecord('test_1')->getId();
		static::$users[] = A_User::createUsersRecord('test_2')->getId();
	}

	/**
	 * Configuration testing.
	 */
	public function testConfiguration()
	{
		$this->assertTrue(\App\Module::isModuleActive('Chat'), 'The chat module is inactive');
	}

	/**
	 * Check if the general chat room exists.
	 */
	public function testGeneralRoom()
	{
		static::$globalRoom = (new \App\Db\Query())->from('u_#__chat_global')->where(['name' => 'LBL_GENERAL'])->one();
		$this->assertNotFalse(static::$globalRoom, 'The general chat room not exists.');
		$currentRoom = \App\Chat::getCurrentRoom();
		$this->assertSame($currentRoom['roomType'], 'global');
		$this->assertSame($currentRoom['recordId'], static::$globalRoom['global_room_id']);
		$chat = \App\Chat::getInstance();
		$this->assertSame($chat->getRoomType(), 'global');
		$this->assertSame($chat->getRecordId(), static::$globalRoom['global_room_id']);
	}

	/**
	 * Chat testing for groups.
	 */
	public function testGroup()
	{
		\App\User::setCurrentUserId(\App\User::getActiveAdminId());
		$groups = \App\User::getCurrentUserModel()->getGroupNames();
		$this->assertGreaterThanOrEqual(1, \count($groups), 'No defined groups');
		$groupId = \key($groups);
		$chat = \App\Chat::getInstance('group', $groupId);
		$this->assertTrue($chat->isRoomExists(), "The chat room does not exist '{$groups[$groupId]}'");
		$this->assertFalse($chat->isAssigned(), "The user should not be assigned '{$groups[$groupId]}'");
		$cntEntries = \count($chat->getEntries());
		$id = $chat->addMessage('Test MSG');
		$this->assertInternalType('integer', $id);
		$rowMsg = (new \App\Db\Query())
			->from(\App\Chat::TABLE_NAME['message'][$chat->getRoomType()])
			->where(['id' => $id])->one();
		$this->assertNotFalse($rowMsg, "The message {$id} does not exist");
		$this->assertSame('Test MSG', $rowMsg['messages']);
		$this->assertSame(\App\User::getCurrentUserId(), $rowMsg['userid']);
		$entries = $chat->getEntries();
		$this->assertCount($cntEntries + 1, $entries, 'Too many messages in the chat room');
		$key = static::getKeyMessage($entries, $id);
		$this->assertNotFalse($key, 'Problem with the method "getEntries"');
		$this->assertSame($rowMsg['messages'], $entries[$key]['messages']);
		$this->assertSame(\App\User::getCurrentUserModel()->getName(), $entries[$key]['user_name']);
	}

	/**
	 * Test assigning a user to a group room.
	 *
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function testAssigningToGroup()
	{
		\App\User::setCurrentUserId(\App\User::getActiveAdminId());
		$groups = \App\User::getCurrentUserModel()->getGroupNames();
		$groupId = \key($groups);
		$chat = \App\Chat::getInstance('group', $groupId);
		$this->assertTrue($chat->isRoomExists(), "The chat room does not exist '{$groups[$groupId]}'");
		$this->assertFalse($chat->isAssigned(), "The user should not be assigned '{$groups[$groupId]}'");
		$chat->addToFavorites();
		$this->assertTrue($chat->isRoomExists(), "The chat room does not exist '{$groups[$groupId]}'");
		$this->assertTrue($chat->isAssigned(), "The user should be assigned '{$groups[$groupId]}'");
		$row = (new \App\Db\Query())
			->from(\App\Chat::TABLE_NAME['room'][$chat->getRoomType()])
			->where(['userid' => \App\User::getCurrentUserId()])
			->andWhere([\App\Chat::COLUMN_NAME['room'][$chat->getRoomType()] => $groupId])
			->one();
		$this->assertNotFalse($row, 'Problem with methods "addToFavorites"');
		$this->assertIsInt($row['last_message'], 'Problem with methods "addToFavorites"');
	}

	/**
	 * Remove test from favorites.
	 *
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \yii\db\Exception
	 */
	public function testRemoveFromFavorites()
	{
		$groups = \App\User::getCurrentUserModel()->getGroupNames();
		$groupId = \key($groups);
		$chat = \App\Chat::getInstance('group', $groupId);
		$this->assertTrue($chat->isRoomExists(), "The chat room does not exist '{$groups[$groupId]}'");
		$this->assertTrue($chat->isAssigned(), "The user should be assigned '{$groups[$groupId]}'");
		$chat->removeFromFavorites();
		$this->assertTrue($chat->isRoomExists(), "The chat room does not exist '{$groups[$groupId]}'");
		$this->assertFalse($chat->isAssigned(), "The user should not be assigned '{$groups[$groupId]}'");
		$this->assertFalse((new \App\Db\Query())
			->from(\App\Chat::TABLE_NAME['room'][$chat->getRoomType()])
			->where(['userid' => \App\User::getCurrentUserId()])
			->andWhere([\App\Chat::COLUMN_NAME['room'][$chat->getRoomType()] => $groupId])
			->exists(),
			'Problem with methods "removeFromFavorites"'
		);
	}

	/**
	 * Testing the method of the current chat room.
	 */
	public function testCurrentRoom()
	{
		unset($_SESSION);
		\App\Chat::setCurrentRoom('global', static::$globalRoom['global_room_id']);
		$this->assertSame($_SESSION['chat']['roomType'], 'global');
		$this->assertSame($_SESSION['chat']['recordId'], static::$globalRoom['global_room_id']);
	}

	/**
	 * Testing adding messages.
	 */
	public function testAddNewMessage()
	{
		$chat = \App\Chat::getInstance();
		$id = $chat->addMessage('test');
		$this->assertInternalType('integer', $id);
		$rowMsg = (new \App\Db\Query())
			->from(\App\Chat::TABLE_NAME['message'][$chat->getRoomType()])
			->where(['id' => $id])->one();
		$this->assertNotFalse($rowMsg, "The message {$id} does not exist");
		$this->assertSame('test', $rowMsg['messages']);
		$this->assertSame(\App\User::getCurrentUserId(), $rowMsg['userid']);
		$entries = $chat->getEntries();
		$key = static::getKeyMessage($entries, $id);
		$this->assertNotFalse($key, 'Problem with the method "getEntries"');
		$this->assertSame($rowMsg['messages'], $entries[$key]['messages']);
		$this->assertSame(\App\User::getCurrentUserModel()->getName(), $entries[$key]['user_name']);
	}

	/**
	 * Testing the method for checking new messages.
	 */
	public function testNewMessage()
	{
		$chat = \App\Chat::getInstance();
		$this->assertFalse(\App\Chat::isNewMessages(), 'Problem with the method "isNewMessages"');
		$chat->addMessage('test 2');
		$this->assertTrue(\App\Chat::isNewMessages(), 'Problem with the method "isNewMessages"');
		//Switch user
		\App\User::setCurrentUserId(static::$users[0]);
		$chat = \App\Chat::getInstance();
		$this->assertTrue(\App\Chat::isNewMessages(), 'Problem with the method "isNewMessages"');
		$chat->getEntries();
		$this->assertFalse(\App\Chat::isNewMessages(), 'Problem with the method "isNewMessages"');
		//Switch user
		\App\User::setCurrentUserId(static::$users[1]);
		$chat = \App\Chat::getInstance();
		$this->assertTrue(\App\Chat::isNewMessages(), 'Problem with the method "isNewMessages"');
		$chat->getEntries();
		$this->assertFalse(\App\Chat::isNewMessages(), 'Problem with the method "isNewMessages"');
	}

	/**
	 * Testing creating a chat room.
	 */
	public function testCreatingChatRoomCrm()
	{
		$recordModel = \Vtiger_Record_Model::getInstanceById(static::$listId[0]);
		$chat = \App\Chat::createRoom('crm', $recordModel->getId());
		$rowRoom = (new \App\Db\Query())
			->from(\App\Chat::TABLE_NAME['room']['crm'])
			->where([\App\Chat::COLUMN_NAME['room']['crm'] => $recordModel->getId()])->one();
		$this->assertNotFalse($rowRoom, "The chat room {$recordModel->getId()} does not exist");
		$this->assertSame($recordModel->getId(), $rowRoom[\App\Chat::COLUMN_NAME['room']['crm']]);
		$this->assertSame($recordModel->getId(), $chat->getRecordId());
		$rooms = \App\Chat::getRoomsByUser();
		$key = static::getKeyRoom($rooms, 'crm', (int) $recordModel->getId());
		$this->assertNotFalse($key, 'Problem with the method "getRoomsByUser". Crm id=' . $recordModel->getId());
		$this->assertSame($recordModel->getDisplayName(), $rooms['crm'][$key]['name']);
	}

	/**
	 * Testing adding messages to Crm chat room.
	 */
	public function testAddMessageCrm()
	{
		$recordModel = \Vtiger_Record_Model::getInstanceById(static::$listId[0]);
		$chat = \App\Chat::getInstance('crm', $recordModel->getId());
		$id = $chat->addMessage('test2');
		$this->assertInternalType('integer', $id);
		$rowMsg = (new \App\Db\Query())
			->from(\App\Chat::TABLE_NAME['message'][$chat->getRoomType()])
			->where(['id' => $id])->one();
		$this->assertNotFalse($rowMsg, "The message {$id} does not exist");
		$this->assertSame('test2', $rowMsg['messages']);
		$this->assertSame(\App\User::getCurrentUserId(), $rowMsg['userid']);
		$entries = $chat->getEntries();
		$key = static::getKeyMessage($entries, $id);
		$this->assertNotFalse($key, 'Problem with the method "getEntries"');
		$this->assertSame($rowMsg['messages'], $entries[$key]['messages']);
		$this->assertSame(\App\User::getCurrentUserModel()->getName(), $entries[$key]['user_name']);
	}

	/**
	 * Testing a CRM chat room.
	 */
	public function testRoomCrm()
	{
		$userId = \App\User::getActiveAdminId();
		\App\User::setCurrentUserId($userId);
		$this->assertFalse(\App\Chat::isNewMessagesForCrm($userId), 'Problem with the method "isNewMessagesForCrm"');
		$chat = \App\Chat::getInstance('crm', static::$listId[0]);
		$chat->addToFavorites();
		$this->assertFalse(\App\Chat::isNewMessagesForCrm($userId), 'Problem with the method "isNewMessagesForCrm"');
		$id = $chat->addMessage('testRoomCrm');
		$this->assertInternalType('integer', $id);
		$rowMsg = (new \App\Db\Query())
			->from(\App\Chat::TABLE_NAME['message'][$chat->getRoomType()])
			->where(['id' => $id])->one();
		$this->assertNotFalse($rowMsg, "The message {$id} does not exist");
		$this->assertSame('testRoomCrm', $rowMsg['messages']);
		$this->assertSame(\App\User::getCurrentUserId(), $rowMsg['userid']);
	}

	/**
	 * Chat room switching test.
	 *
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function testSwitchRoom()
	{
		\App\Chat::setCurrentRoom('crm', static::$listId[0]);
		$this->assertSame($_SESSION['chat']['roomType'], 'crm');
		$this->assertSame($_SESSION['chat']['recordId'], static::$listId[0]);
		$currentRoom = \App\Chat::getCurrentRoom();
		$this->assertSame($currentRoom['roomType'], 'crm');
		$this->assertSame($currentRoom['recordId'], static::$listId[0]);
	}

	/**
	 * Message history test.
	 *
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function testHistory()
	{
		$userId = \App\User::getCurrentUserId();
		$userName = \App\User::getCurrentUserModel()->getName();
		$chat = \App\Chat::getInstance('global', static::$globalRoom['global_room_id']);
		$globalHistory = $chat->getHistoryByType('global');
		$this->assertInternalType('array', $globalHistory);
		foreach ($globalHistory as $message) {
			$this->assertSame($userId, $message['userid']);
			$this->assertSame($userName, $message['user_name']);
			$this->assertNotNull($message['messages']);
		}
		$globalCrm = $chat->getHistoryByType('crm');
		$this->assertInternalType('array', $globalCrm);
		foreach ($globalCrm as $message) {
			$this->assertSame($userId, $message['userid']);
			$this->assertSame($userName, $message['user_name']);
			$this->assertNotNull($message['messages']);
		}
		$globalGroup = $chat->getHistoryByType('group');
		$this->assertInternalType('array', $globalGroup);
		foreach ($globalGroup as $message) {
			$this->assertSame($userId, $message['userid']);
			$this->assertSame($userName, $message['user_name']);
			$this->assertNotNull($message['messages']);
		}
	}

	/**
	 * Testing the removal of Crm chat room.
	 *
	 * @throws \Exception
	 */
	public function testRemoveRecordCrm()
	{
		$recordModel = \Vtiger_Record_Model::getInstanceById(static::$listId[0]);
		$recordId = $recordModel->getId();
		$recordModel->delete();
		$this->assertFalse(
			(new \App\Db\Query())
				->from(\App\Chat::TABLE_NAME['room']['crm'])
				->where([\App\Chat::COLUMN_NAME['room']['crm'] => $recordId])->exists(),
			"The chat room {$recordId} does exist"
		);
		$this->assertFalse(
			(new \App\Db\Query())
				->from(\App\Chat::TABLE_NAME['message']['crm'])
				->where([\App\Chat::COLUMN_NAME['message']['crm'] => $recordId])->exists(),
			"Messages {$recordId} exist"
		);
	}

	/**
	 * Test the current chat room after deleting the record.
	 */
	public function testCurrentRoomAfterDeletingRecord()
	{
		$this->assertSame($_SESSION['chat']['roomType'], 'crm');
		$this->assertSame($_SESSION['chat']['recordId'], static::$listId[0]);
		\vtlib\Functions::clearCacheMetaDataRecord(static::$listId[0]);
		$this->assertFalse(\App\Record::isExists(static::$listId[0]), 'The record should not exist');
		$currentRoom = \App\Chat::getCurrentRoom();
		$this->assertSame($currentRoom['roomType'], 'global');
		$this->assertSame($currentRoom['recordId'], static::$globalRoom['global_room_id']);
	}

	/**
	 * The test checks what happens when the user is removed.
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \yii\db\Exception
	 */
	public function testRemoveUser()
	{
		\App\User::setCurrentUserId(static::$users[0]);
		$chat = \App\Chat::getInstance('global', static::$globalRoom['global_room_id']);
		$id = $chat->addMessage('testRemoveUser');
		$entries = $chat->getEntries();
		\Users_Record_Model::deleteUserPermanently(static::$users[0], \App\User::getActiveAdminId());
		\App\Cache::clear(static::$users[0]);
		\App\User::clearCache();
		$this->assertFalse(\App\User::isExists(static::$users[0]), 'The user should not exist');
		//Switch user
		\App\User::setCurrentUserId(\App\User::getActiveAdminId());
		$chat = \App\Chat::getInstance('global', static::$globalRoom['global_room_id']);
		$rowMsg = (new \App\Db\Query())
			->from(\App\Chat::TABLE_NAME['message'][$chat->getRoomType()])
			->where(['id' => $id])->one();
		$this->assertNotFalse($rowMsg, "The message {$id} does not exist");
		$this->assertSame('testRemoveUser', $rowMsg['messages']);
		$this->assertSame(static::$users[0], $rowMsg['userid']);
		$entriesAfter = $chat->getEntries();
		$this->assertCount(\count($entries), $entriesAfter, 'The number of messages should be the same');
		$key = static::getKeyMessage($entriesAfter, $id);
		$this->assertNotFalse($key, 'Problem with the method "getEntries"');
		$this->assertSame($rowMsg['messages'], $entriesAfter[$key]['messages']);
		$this->assertNull($entriesAfter[$key]['user_name'], 'User name should be null');
		$this->assertNull($entriesAfter[$key]['role_name'], 'User role should be null');
		$this->assertNull($entriesAfter[$key]['image'], 'User image should be null');
		$participants = $chat->getParticipants();
		$keyUser = static::getUserFromParticipants($participants, static::$users[0]);
		if ($keyUser && static::isRoomPinned($chat->getRoomType(), $chat->getRecordId(), \App\User::getActiveAdminId())) {
			$this->assertNotFalse($keyUser, 'Problem with the method "getParticipants"');
			$this->assertSame($participants[$keyUser]['message'], $entriesAfter[$key]['messages']);
		} else {
			$this->assertFalse($keyUser, 'Problem with the method "getParticipants"');
		}
	}

	/**
	 * @codeCoverageIgnore
	 * Cleaning after tests.
	 */
	public static function tearDownAfterClass()
	{
		unset(static::$listId[0]);
		\App\User::setCurrentUserId(\App\User::getActiveAdminId());
		if (!static::$chatActive) {
			(new \Settings_ModuleManager_Module_Model())->disableModule('Chat');
		}
		foreach (static::$listId as $id) {
			$recordModel = \Vtiger_Record_Model::getInstanceById($id);
			$recordModel->delete();
		}
	}
}
