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
class Chat extends \Tests\Base
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

		$recordModel = \Vtiger_Record_Model::getCleanInstance('Contacts');
		$recordModel->set('assigned_user_id', \App\User::getActiveAdminId());
		$recordModel->set('lastname', 'Test chat');
		$recordModel->save();
		static::$listId[] = $recordModel->getId();
	}

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
		$this->assertNotFalse($key, 'Problem with the method "getRoomsByUser"');
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
	 * @codeCoverageIgnore
	 * Cleaning after tests.
	 */
	public static function tearDownAfterClass()
	{
		if (!static::$chatActive) {
			(new \Settings_ModuleManager_Module_Model())->disableModule('Chat');
		}
		foreach (static::$listId as $id) {
			$recordModel = \Vtiger_Record_Model::getInstanceById($id);
			$recordModel->delete();
		}
	}
}
