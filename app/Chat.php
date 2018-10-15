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
	 * Last message ID.
	 *
	 * @var int|null
	 */
	protected $lastId;

	/**
	 * User ID.
	 *
	 * @var int|null
	 */
	protected $userId;

	/**
	 * Get maximum displays the length of the name.
	 *
	 * @return int
	 */
	public static function getMaxDisplayLen(): int
	{
		return \AppConfig::module('Chat', 'MAX_DISPLAY_NAME');
	}

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
	public static function getCurrentRoomId(): int
	{
		if (!\App\Session::has('chat-current-room-id')) {
			return 0;
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
	public static function getInstanceById(int $id, ?int $userId = null): \App\Chat
	{
		$instance = new self($userId);
		$roomRow = (new \App\Db\Query())
			->select(['CR.*', 'CU.userid', 'CU.last_message', 'CU.favorite'])
			->from(['CR' => 'u_#__chat_rooms'])
			->leftJoin(['CU' => 'u_#__chat_users'], "CU.room_id = CR.room_id AND CU.userid={$instance->userId}")
			->where(['CR.room_id' => $id])
			->one();
		if ($roomRow) {
			$instance->roomId = $roomRow['room_id'];
			$instance->nameOfRoom = $roomRow['name'];
			$instance->recordId = $id;
			$instance->lastMessage = $roomRow['last_message'];
			$instance->favorite = $roomRow['favorite'] ?? false;
			$instance->isAssigned = !empty($roomRow['userid']);
		}
		return $instance;
	}

	/**
	 * Create new chat room by record ID.
	 *
	 * @param int      $recordId
	 * @param int|null $userId
	 *
	 * @throws \yii\db\Exception
	 *
	 * @return \App\Chat
	 */
	public static function createRoom(int $recordId, ?int $userId = null): \App\Chat
	{
		$instance = new self($userId);
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
		$sql = (new \App\Db\Query())
			->select(['COUNT(*)'])
			->from(['CM' => 'u_yf_chat_messages'])
			->where(['CM.room_id' => new \yii\db\Expression('CR.room_id')])
			->andWhere(['>', 'CM.id', new \yii\db\Expression('CU.last_message')])
			->createCommand()->getRawSql();
		return (new \App\Db\Query())
			->select([
				'number_of_new' => "({$sql})",
				'CR.*', 'CU.userid', 'CU.last_message', 'CU.favorite'
			])->from(['CR' => 'u_#__chat_rooms'])
				->innerJoin(['CU' => 'u_#__chat_users'], 'CU.room_id = CR.room_id')
				->where(['CU.userid' => $userId])
				->andWhere(['CU.favorite' => 1])
				->all();
	}

	/**
	 * Chat constructor.
	 *
	 * @param int|null $userId
	 */
	public function __construct(?int $userId = null)
	{
		if (empty($userId)) {
			$userId = \App\User::getCurrentUserId();
		}
		$this->userId = $userId;
	}

	/**
	 * Check if the chat room is a favorite.
	 *
	 * @return bool
	 */
	public function isFavorite(): bool
	{
		return $this->favorite;
	}

	/**
	 * @return int|null
	 */
	public function getLastMessage(): ?int
	{
		return $this->lastMessage;
	}

	/**
	 * Check if chat room exists.
	 *
	 * @return bool
	 */
	public function isRoomExists(): bool
	{
		return $this->roomId !== false;
	}

	/**
	 * Is the user assigned to the room.
	 *
	 * @return bool
	 */
	public function isAssigned(): bool
	{
		return $this->isAssigned;
	}

	/**
	 * Return chat room ID.
	 *
	 * @return int
	 */
	public function getRoomId(): int
	{
		return $this->roomId;
	}

	/**
	 * Get name of chat room.
	 *
	 * @return string
	 */
	public function getNameOfRoom(): string
	{
		return $this->nameOfRoom;
	}

	/**
	 * Get display name of chat room.
	 *
	 * @return string
	 */
	public function getDisplayNameOfRoom(): string
	{
		return \App\TextParser::textTruncate(\App\Language::translate($this->nameOfRoom), static::getMaxDisplayLen());
	}

	/**
	 * Set room as favorite.
	 *
	 * @param bool $favorite
	 *
	 * @throws \yii\db\Exception
	 */
	public function setFavorite(bool $favorite)
	{
		if ($this->isAssigned()) {
			\App\db::getInstance()->createCommand()
				->update('u_#__chat_users', ['favorite' => $favorite], [
					'room_id' => $this->roomId,
					'userid' => $this->userId
				])->execute();
		} else {
			\App\db::getInstance()
				->createCommand()
				->insert('u_#__chat_users', [
					'room_id' => $this->roomId,
					'userid' => $this->userId,
					'last_message' => null,
					'favorite' => $favorite
				])->execute();
		}
		$this->favorite = $favorite;
	}

	/**
	 * Set last message ID.
	 *
	 * @throws \yii\db\Exception
	 */
	public function setLastMessage(?int $lastMessageId = null)
	{
		$lastMessageId = $lastMessageId ?? $this->lastId;
		if ($this->isAssigned) {
			\App\Db::getInstance()->createCommand()
				->update(
					'u_#__chat_users',
					['last_message' => $lastMessageId],
					['room_id' => $this->getRoomId(), 'userid' => $this->userId]
				)->execute();
		}
	}

	/**
	 * Add chat room.
	 *
	 * @throws \yii\db\Exception
	 *
	 * @return int
	 */
	public function addRoom(): int
	{
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
					'userid' => $this->userId,
					'last_message' => null,
					'favorite' => false
				])->execute();
		}
		return $this->roomId;
	}

	/**
	 * Add new message to chat room.
	 *
	 * @param string $message
	 *
	 * @throws \yii\db\Exception
	 */
	public function addMessage(string $message)
	{
		$currentUser = \App\User::getUserModel($this->userId);
		\App\Db::getInstance()->createCommand()
			->insert('u_#__chat_messages', [
				'userid' => $currentUser->getId(),
				'created' => strtotime('now'),
				'user_name' => $currentUser->getName(),
				'messages' => $message,
				'room_id' => $this->roomId
			])->execute();
		$lastMessageId = \App\Db::getInstance()->getLastInsertID('u_#__chat_messages_id_seq');
		if ($this->isAssigned) {
			\App\db::getInstance()->createCommand()
				->update('u_#__chat_users', ['last_message' => $lastMessageId], [
					'room_id' => $this->roomId,
					'userid' => $currentUser->getId()
				])->execute();
		} else {
			\App\db::getInstance()
				->createCommand()
				->insert('u_#__chat_users', [
					'room_id' => $this->roomId,
					'userid' => $currentUser->getId(),
					'last_message' => $lastMessageId,
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
		$this->lastId = $messageId;
		$rows = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$row['created'] = date('Y-m-d H:i:s', $row['created']);
			$row['time'] = \App\Fields\DateTime::formatToViewDate($row['created']);
			$rows[] = $row;
			if (\is_null($this->lastId) || (int) $row['id'] > $this->lastId) {
				$this->lastId = (int) $row['id'];
			}
		}
		$dataReader->close();
		return $rows;
	}
}
