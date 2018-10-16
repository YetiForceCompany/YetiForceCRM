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
	 * Is general room exists.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param array $userRooms
	 *
	 * @return bool
	 */
	private static function isGeneralRoomExists($userRooms)
	{
		foreach ($userRooms as $val) {
			if ($val['room_id'] == 0) {
				return true;
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
		$this->assertTrue(\App\Chat::getInstanceById(0)->isRoomExists(), 'The general chat room not exists.');
		$this->assertTrue(static::isGeneralRoomExists(\App\Chat::getRoomsByUser()), 'A general room is not related to the user');
	}

	/**
	 * Testing adding messages.
	 */
	public function testAddNewMessage()
	{
		$room = \App\Chat::getInstanceById(0);
		$id = $room->addMessage('test');
		$this->assertInternalType('integer', $id);
		$rowMsg = (new \App\Db\Query())->from('u_#__chat_messages')->where(['id' => $id])->one();
		$this->assertNotFalse($rowMsg, "The message {$id} does not exist");
		$this->assertSame('test', $rowMsg['messages']);
		$this->assertSame(\App\User::getCurrentUserId(), $rowMsg['userid']);
		$this->assertSame(\App\User::getCurrentUserModel()->getName(), $rowMsg['user_name']);
		$rowUser = (new \App\Db\Query())->from('u_#__chat_users')
			->where(['room_id' => 0])
			->andWhere(['userid' => \App\User::getCurrentUserId()])->one();
		$this->assertNotFalse($rowUser, 'The "chat_users" does not exist');
		$this->assertSame($room->getLastMessageId(), $rowUser['last_message']);
	}

	/**
	 * Testing the general room.
	 */
	public function testGeneralRoomAfterAddingMessage()
	{
		$this->assertTrue(static::isGeneralRoomExists(\App\Chat::getRoomsByUser()), 'A general room is not related to the user');
	}

	/**
	 * Testing creating a chat room.
	 */
	public function testCreatingChatRoom()
	{
		$recordModel = \Vtiger_Record_Model::getInstanceById(static::$listId[0]);
		$room = \App\Chat::createRoom($recordModel->getId());
		$rowRoom = (new \App\Db\Query())->from('u_#__chat_rooms')->where(['room_id' => $recordModel->getId()])->one();
		$this->assertNotFalse($rowRoom, "The chat room {$recordModel->getId()} does not exist");
		$this->assertSame($recordModel->getDisplayName(), $rowRoom['name']);
		$this->assertSame($recordModel->getId(), $rowRoom['room_id']);
		$this->assertSame($recordModel->getId(), $room->getRoomId());
		$this->assertSame(0, (new \App\Db\Query())->from('u_#__chat_messages')->where(['room_id' => $recordModel->getId()])->count());
		$this->assertTrue($room->isRoomExists(), 'Problem with the method "isRoomExists"');
		$this->assertTrue($room->isAssigned(), 'Problem with the method "isAssigned"');
		$this->assertFalse($room->isFavorite(), 'Problem with the method "isFavorite"');
		$this->assertTrue(static::isGeneralRoomExists(\App\Chat::getRoomsByUser()), 'A general room is not related to the user');
	}

	/**
	 * Testing the record model removal.
	 */
	public function testDeleteRecordModel()
	{
		$recordModel = \Vtiger_Record_Model::getInstanceById(static::$listId[0]);
		$id = $recordModel->getId();
		$recordModel->delete();
		$this->assertFalse(
			(new \App\Db\Query())->from('u_#__chat_rooms')->where(['room_id' => $id])->one(),
			"Chat room {$id} exists"
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
