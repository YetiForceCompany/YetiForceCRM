<?php
/**
 * Chat.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace App;

/**
 * Class Chat.
 */
class Chat
{
	/**
	 * Record ID.
	 *
	 * @var int
	 */
	protected $recordId;

	/**
	 * Name of room.
	 *
	 * @var string
	 */
	protected $nameOfRoom;

	/**
	 * Room ID.
	 *
	 * @var int
	 */
	protected $roomId = false;

	/**
	 * Last message ID.
	 *
	 * @var int|null
	 */
	protected $lastMessage;

	/**
	 * Determines if the room is a favorite.
	 *
	 * @var bool
	 */
	protected $favorite = false;

	/**
	 * Specifies whether the user is assigned to a room.
	 *
	 * @var bool
	 */
	protected $isAssigned = false;

	/**
	 * Set current room ID.
	 *
	 * @param int $id
	 */
	public static function setCurrentRoomId(int $id)
	{
		\App\Session::set('chat-current-room-id', $id);
	}

	/**
	 * Get current room ID.
	 *
	 * @return int
	 */
	public static function getCurrentRoomId()
	{
		if (!\App\Session::has('chat-current-room-id')) {
			return (int) 0;
		}
		return (int) \App\Session::get('chat-current-room-id');
	}

	/**
	 * Get instance by record ID.
	 *
	 * @param int      $id     room ID or crm ID
	 * @param int|null $userId
	 *
	 * @return \App\Chat
	 */
	public static function getInstanceById(int $id, ?int $userId = null)
	{
		if (empty($userId)) {
			$userId = \App\User::getCurrentUserId();
		}
		$instance = new self();
		$roomRow = (new \App\Db\Query())
			->select(['CR.*', 'CU.userid', 'CU.last_message', 'CU.favorite'])
			->from(['CR' => 'u_#__chat_rooms'])
			->leftJoin(['CU' => 'u_#__chat_users'], "CU.room_id = CR.room_id AND CU.userid={$userId}")
			->where(['CR.room_id' => $id])
			->one();
		if ($roomRow) {
			$instance->roomId = $roomRow['room_id'];
			$instance->nameOfRoom = $roomRow['name'];
			$instance->recordId = $id;
			$instance->lastMessage = $roomRow['last_message'];
			$instance->favorite = $roomRow['favorite'];
			$instance->isAssigned = !empty($roomRow['userid']);
		}
		return $instance;
	}

	/**
	 * Create new chat room by record ID.
	 *
	 * @param int $recordId
	 *
	 * @return \App\Chat
	 */
	public static function createRoom(int $recordId)
	{
		$instance = new self();
		$instance->recordId = $recordId;
		$instance->nameOfRoom = \Vtiger_Record_Model::getInstanceById($recordId)->getDisplayName();
		$instance->addRoom();
		return $instance;
	}

	/**
	 * Get all chat rooms.
	 *
	 * @return array
	 */
	public static function getRooms()
	{
		return (new \App\Db\Query())->from('u_#__chat_rooms')->all();
	}

	/**
	 * Get all chat rooms by user.
	 *
	 * @param int|null $userId
	 *
	 * @return array
	 */
	public static function getRoomsByUser(?int $userId = null)
	{
		if (empty($userId)) {
			$userId = \App\User::getCurrentUserId();
		}
		return (new \App\Db\Query())
			->from(['CR' => 'u_#__chat_rooms'])
			->innerJoin(['CU' => 'u_#__chat_users'], 'CU.room_id = CR.room_id')
			->where(['CU.userid' => $userId])
			->andWhere(['CU.favorite' => 1])
			->all();
	}

	/**
	 * Check if the chat room is a favorite.
	 *
	 * @return bool
	 */
	public function isFavorite()
	{
		return $this->favorite;
	}

	/**
	 * Check if chat room exists.
	 *
	 * @return bool
	 */
	public function isRoomExists()
	{
		return $this->roomId !== false;
	}

	/**
	 * Is the user assigned to the room.
	 *
	 * @return bool
	 */
	public function isAssigned()
	{
		return $this->isAssigned;
	}

	/**
	 * Return chat room ID.
	 *
	 * @return int
	 */
	public function getRoomId()
	{
		return $this->roomId;
	}

	/**
	 * Get name of chat room.
	 *
	 * @return string
	 */
	public function getNameOfRoom()
	{
		return $this->nameOfRoom;
	}

	/**
	 * Mark room as favorite.
	 *
	 * @param bool     $favorite
	 * @param int|null $userId
	 *
	 * @throws \yii\db\Exception
	 */
	public function markAsFavorite(bool $favorite, ?int $userId = null)
	{
		if (empty($userId)) {
			$userId = \App\User::getCurrentUserId();
		}
		\App\db::getInstance()->createCommand()
			->update('u_#__chat_users', ['favorite' => $favorite], [
				'room_id' => $this->roomId,
				'userid' => $userId
			])->execute();
		$this->favorite = $favorite;
	}

	/**
	 * Add chat room.
	 *
	 * @param int|null $userId
	 *
	 * @throws \yii\db\Exception
	 *
	 * @return int
	 */
	public function addRoom(?int $userId = null)
	{
		if (empty($userId)) {
			$userId = \App\User::getCurrentUserId();
		}
		if (!$this->isRoomExists()) {
			\App\db::getInstance()
				->createCommand()
				->insert('u_#__chat_rooms', [
					'name' => $this->nameOfRoom,
					'room_id' => $this->recordId
				])->execute();
			$this->roomId = $this->recordId;
			\App\db::getInstance()
				->createCommand()
				->insert('u_#__chat_users', [
					'room_id' => $this->roomId,
					'userid' => $userId,
					'last_message' => null,
					'favorite' => false
				])->execute();
		}
		return $this->roomId;
	}

	/**
	 * Add new message to chat room.
	 *
	 * @param string   $message
	 * @param int|null $userId
	 *
	 * @throws \yii\db\Exception
	 */
	public function addMessage(string $message, ?int $userId = null)
	{
		$currentUser = empty($userId) ? \App\User::getCurrentUserModel() : \App\User::getUserModel($userId);
		\App\Db::getInstance()->createCommand()
			->insert('u_#__chat_messages', [
				'userid' => $currentUser->getId(),
				'created' => strtotime('now'),
				'user_name' => $currentUser->getName(),
				'messages' => $message,
				'room_id' => $this->roomId
			])->execute();
		if (!$this->isAssigned) {
			\App\db::getInstance()
				->createCommand()
				->insert('u_#__chat_users', [
					'room_id' => $this->roomId,
					'userid' => $currentUser->getId(),
					'last_message' => null,
					'favorite' => false
				])->execute();
			$this->isAssigned = true;
		}
	}

	/**
	 * Get entries function.
	 *
	 * @param bool|int $messageId
	 *
	 * @return array
	 */
	public function getEntries($messageId = false)
	{
		$query = (new \App\Db\Query())
			->from('u_#__chat_messages')
			->limit(\AppConfig::module('Chat', 'ROWS_LIMIT'))
			->where(['room_id' => $this->roomId])
			->orderBy(['created' => \SORT_DESC]);
		if ($messageId) {
			$query->andWhere(['>', 'id', $messageId]);
		}
		$rows = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$row['created'] = date('Y-m-d H:i:s', $row['created']);
			$row['time'] = \App\Fields\DateTime::formatToViewDate($row['created']);
			$rows[] = $row;
		}
		$dataReader->close();
		return $rows;
	}
}
