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
	 * Information about the tables of the database.
	 */
	const TABLE_NAME = [
		'message' => ['crm' => 'u_#__chat_messages_crm', 'group' => 'u_#__chat_messages_group', 'global' => 'u_#__chat_messages_global'],
		'room' => ['crm' => 'u_#__chat_rooms_crm', 'group' => 'u_#__chat_rooms_group', 'global' => 'u_#__chat_rooms_global'],
	];

	/**
	 * Information about the columns of the database.
	 */
	const COLUMN_NAME = [
		'message' => ['crm' => 'crmid', 'group' => 'groupid', 'global' => 'globalid'],
		'room' => ['crm' => 'crmid', 'group' => 'groupid', 'global' => 'global_room_id'],
	];

	/**
	 * Type of chat room.
	 *
	 * @var string
	 */
	private $roomType;

	/**
	 * ID of chat room.
	 *
	 * @var int
	 */
	private $roomId;

	/**
	 * ID record associated with the chat room.
	 *
	 * @var int|null
	 */
	private $recordId;

	/**
	 * @var []|false
	 */
	private $room = false;

	/**
	 * User ID.
	 *
	 * @var int
	 */
	private $userId;

	/**
	 * Last message ID.
	 *
	 * @var int|null
	 */
	private $lastMessageId;

	/**
	 * Set current room ID, type.
	 *
	 * @param string   $roomType
	 * @param int|null $recordId
	 *
	 * @throws \App\Exceptions\IllegalValue
	 */
	public static function setCurrentRoom(string $roomType, ?int $recordId)
	{
		$_SESSION['chat'] = [
			'roomType' => $roomType, 'recordId' => $recordId
		];
	}

	/**
	 * Get current room ID, type.
	 *
	 * @return []|false
	 */
	public static function getCurrentRoom()
	{
		if (!isset($_SESSION['chat'])) {
			$row = (new Db\Query())->from('u_#__chat_global')->where(['name' => 'LBL_GENERAL'])->one();
			if ($row === false) {
				return false;
			}
			return [
				'roomType' => 'global',
				'recordId' => $row[static::COLUMN_NAME['room']['global']]
			];
		}
		return $_SESSION['chat'];
	}

	/**
	 * Create chat room.
	 *
	 * @param string $roomType
	 * @param int    $recordId
	 *
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \yii\db\Exception
	 *
	 * @return \App\Chat
	 */
	public static function createRoom(string $roomType, int $recordId)
	{
		$instance = new self($roomType, $recordId);
		$userId = User::getCurrentUserId();
		$table = static::TABLE_NAME['room'][$roomType];
		$recordIdName = static::COLUMN_NAME['room'][$roomType];
		Db::getInstance()->createCommand()->insert($table, [
			'userid' => $userId,
			'last_message' => null,
			$recordIdName => $recordId
		])->execute();
		$instance->recordId = $recordId;
		$instance->roomType = $roomType;
		Cache::delete("Chat_{$roomType}", $userId);
		return $instance;
	}

	/**
	 * Get instance \App\Chat.
	 *
	 * @param null|string $roomType
	 * @param int|null    $recordId
	 *
	 * @throws \App\Exceptions\IllegalValue
	 *
	 * @return \App\Chat
	 */
	public static function getInstance(?string $roomType = null, ?int $recordId = null): \App\Chat
	{
		if (empty($roomType) || \is_null($recordId)) {
			$currentRoom = static::getCurrentRoom();
			if ($currentRoom !== false) {
				$roomType = $currentRoom['roomType'];
				$recordId = $currentRoom['recordId'];
			}
		}
		return new self($roomType, $recordId);
	}

	/**
	 * Global list of chat rooms.
	 *
	 * @return array
	 */
	public static function getRoomsGlobal()
	{
		$userId = User::getCurrentUserId();
		if (Cache::has('Chat_global', $userId)) {
			return Cache::get('Chat_global', $userId);
		}
		$roomIdName = static::COLUMN_NAME['room']['global'];
		$cntQuery = (new \App\Db\Query())
			->select([new \yii\db\Expression('COUNT(*)')])
			->from(['CM' => 'u_yf_chat_messages_global'])
			->where([
				'CM.globalid' => new \yii\db\Expression('CR.global_room_id')
			])->andWhere(['>', 'CM.id', new \yii\db\Expression('CR.last_message')]);
		$subQuery = (new \App\Db\Query())
			->select([
				'CR.*',
				'cnt_new_message' => $cntQuery
			])
			->from(['CR' => 'u_yf_chat_rooms_global']);
		$query = (new Db\Query())
			->select(['name', 'recordid' => 'GL.global_room_id', 'CNT.cnt_new_message'])
			->from(['GL' => 'u_#__chat_global'])
			->leftJoin(['CNT' => $subQuery], "CNT.{$roomIdName} = GL.global_room_id AND CNT.userid = {$userId}");
		$dataReader = $query->createCommand()->query();
		$rooms = [];
		while ($row = $dataReader->read()) {
			$row['name'] = Language::translate($row['name'], 'Chat');
			$rooms[] = $row;
		}
		$dataReader->close();
		Cache::save('Chat_global', $userId, $rooms);
		return $rooms;
	}

	/**
	 * List of chat room groups.
	 *
	 * @param int|null $userId
	 *
	 * @return array
	 */
	public static function getRoomsGroup(?int $userId = null)
	{
		if (empty($userId)) {
			$userId = User::getCurrentUserId();
		}
		if (Cache::has('Chat_group', $userId)) {
			return Cache::get('Chat_group', $userId);
		}
		$subQuery = (new Db\Query())
			->select(['CR.groupid', 'CR.userid', 'cnt_new_message' => 'COUNT(*)'])
			->from(['CR' => static::TABLE_NAME['room']['group']])
			->innerJoin(['CM' => static::TABLE_NAME['message']['group']], 'CM.groupid = CR.groupid')
			->where(['>', 'CM.id', new \yii\db\Expression('CR.last_message')])
			->orWhere(['CR.last_message' => null])
			->groupBy(['CR.groupid', 'CR.userid']);
		$rows = (new Db\Query())
			->select(['GR.roomid', 'GR.userid', 'recordid' => 'GR.groupid', 'name' => 'VGR.groupname', 'CNT.cnt_new_message'])
			->from(['GR' => 'u_#__chat_rooms_group'])
			->innerJoin(['VGR' => 'vtiger_groups'], 'VGR.groupid = GR.groupid')
			->leftJoin(['CNT' => $subQuery], 'CNT.groupid = GR.groupid AND CNT.userid = GR.userid')
			->where(['GR.userid' => $userId])
			->all();
		Cache::save('Chat_group', $userId, $rows);
		return $rows;
	}

	/**
	 * CRM list of chat rooms.
	 *
	 * @param int|null $userId
	 *
	 * @return array
	 */
	public static function getRoomsCrm(?int $userId = null)
	{
		if (empty($userId)) {
			$userId = User::getCurrentUserId();
		}
		if (Cache::has('Chat_crm', $userId)) {
			return Cache::get('Chat_crm', $userId);
		}
		$subQuery = (new Db\Query())
			->select(['CR.crmid', 'CR.userid', 'cnt_new_message' => 'COUNT(*)'])
			->from(['CR' => static::TABLE_NAME['room']['crm']])
			->innerJoin(['CM' => static::TABLE_NAME['message']['crm']], 'CM.crmid = CR.crmid')
			->where(['>', 'CM.id', new \yii\db\Expression('CR.last_message')])
			->orWhere(['CR.last_message' => null])
			->groupBy(['CR.crmid', 'CR.userid']);
		$rows = (new Db\Query())
			->select(['C.roomid', 'C.userid', 'recordid' => 'C.crmid', 'name' => 'CL.label', 'CNT.cnt_new_message'])
			->from(['C' => 'u_#__chat_rooms_crm'])
			->leftJoin(['CL' => 'u_#__crmentity_label'], 'CL.crmid = C.crmid')
			->leftJoin(['CNT' => $subQuery], 'CNT.crmid = C.crmid AND CNT.userid = C.userid')
			->where(['C.userid' => $userId])
			->all();
		Cache::save('Chat_crm', $userId, $rows);
		return $rows;
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
		return [
			'crm' => static::getRoomsCrm($userId),
			'group' => static::getRoomsGroup($userId),
			'global' => static::getRoomsGlobal(),
		];
	}

	/**
	 * Rerun the number of new messages.
	 *
	 * @return int
	 */
	public static function getNumberOfNewMessages(): int
	{
		$numberOfNewMessages = 0;
		$roomInfo = static::getRoomsByUser();
		foreach (['crm', 'group', 'global'] as $roomType) {
			foreach ($roomInfo[$roomType] as $item) {
				$numberOfNewMessages += $item['cnt_new_message'];
			}
		}
		return $numberOfNewMessages;
	}

	/**
	 * Is there any new message for global.
	 *
	 * @param int $userId
	 *
	 * @return bool
	 */
	public static function isNewMessagesForGlobal(int $userId): bool
	{
		$subQueryGlobal = (new Db\Query())
			->select([
				static::COLUMN_NAME['message']['global'],
				'id' => new \yii\db\Expression('max(id)')
			])->from(static::TABLE_NAME['message']['global'])
				->groupBy([static::COLUMN_NAME['message']['global']]);
		return (new Db\Query())
			->select(['CG.name', 'CM.id'])
			->from(['CG' => 'u_#__chat_global'])
			->innerJoin(['CM' => $subQueryGlobal], 'CM.globalid = CG.global_room_id')
			->leftJoin(['GL' => static::TABLE_NAME['room']['global']], 'GL.global_room_id = CG.global_room_id')
			->where(['GL.userid' => $userId])
			->andWhere(['or', ['GL.last_message' => null], ['<', 'GL.last_message', new \yii\db\Expression('CM.id')]])
			->exists();
	}

	/**
	 * Is there any new message for crm.
	 *
	 * @param int $userId
	 *
	 * @return bool
	 */
	public static function isNewMessagesForCrm(int $userId): bool
	{
		$subQueryCrm = (new Db\Query())
			->select([
				static::COLUMN_NAME['message']['crm'],
				'id' => new \yii\db\Expression('max(id)')
			])->from(static::TABLE_NAME['message']['crm'])
				->groupBy([static::COLUMN_NAME['message']['crm']]);
		return (new Db\Query())
			->select(['CM.id'])
			->from(['C' => static::TABLE_NAME['room']['crm']])
			->innerJoin(['CM' => $subQueryCrm], 'CM.crmid = C.crmid')
			->where(['C.userid' => $userId])
			->andWhere(['<', 'C.last_message', new \yii\db\Expression('CM.id')])
			->exists();
	}

	/**
	 * Is there any new message for group.
	 *
	 * @param int $userId
	 *
	 * @return bool
	 */
	public static function isNewMessagesForGroup(int $userId): bool
	{
		$subQueryGroup = (new Db\Query())
			->select([
				static::COLUMN_NAME['message']['group'],
				'id' => new \yii\db\Expression('max(id)')
			])->from(static::TABLE_NAME['message']['group'])
				->groupBy([static::COLUMN_NAME['message']['group']]);
		return (new Db\Query())
			->select(['CM.id'])
			->from(['GR' => static::TABLE_NAME['room']['group']])
			->innerJoin(['CM' => $subQueryGroup], 'CM.groupid = GR.groupid')
			->where(['GR.userid' => $userId])
			->andWhere(['<', 'GR.last_message', new \yii\db\Expression('CM.id')])
			->exists();
	}

	/**
	 * Is there any new message.
	 *
	 * @return bool
	 */
	public static function isNewMessages(): bool
	{
		$userId = User::getCurrentUserId();
		return static::isNewMessagesForGlobal($userId) ||
			static::isNewMessagesForCrm($userId) ||
			static::isNewMessagesForGroup($userId);
	}

	/**
	 * Chat constructor.
	 *
	 * @param null|string $roomType
	 * @param int|null    $recordId
	 *
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function __construct(?string $roomType, ?int $recordId)
	{
		$this->userId = User::getCurrentUserId();
		if (empty($roomType) || \is_null($recordId)) {
			return;
		}
		$this->roomType = $roomType;
		$this->recordId = $recordId;
		$this->room = $this->getQueryRoom()->one();
		if ($this->isRoomExists() && isset($this->room['room_id'])) {
			$this->roomId = $this->room['room_id'];
		}
		if ($this->roomType === 'crm' && !$this->isRoomExists()) {
			$this->room = [
				'roomid' => null,
				'userid' => null,
				'record_id' => $recordId,
				'last_message' => null
			];
		}
	}

	/**
	 * Get room type.
	 *
	 * @return string|null
	 */
	public function getRoomType(): ?string
	{
		return $this->roomType;
	}

	/**
	 * Get record ID.
	 *
	 * @return int|null
	 */
	public function getRecordId(): ?int
	{
		return $this->recordId;
	}

	/**
	 * Check if chat room exists.
	 *
	 * @return bool
	 */
	public function isRoomExists(): bool
	{
		return $this->room !== false;
	}

	/**
	 * Is the user assigned to the chat room.
	 *
	 * @return bool
	 */
	public function isAssigned()
	{
		return !empty($this->room['userid']);
	}

	/**
	 * Add new message to chat room.
	 *
	 * @param string $message
	 *
	 * @throws \yii\db\Exception
	 *
	 * @return int
	 */
	public function addMessage(string $message): int
	{
		$table = static::TABLE_NAME['message'][$this->roomType];
		$db = Db::getInstance();
		$db->createCommand()->insert($table, [
			'userid' => $this->userId,
			'messages' => $message,
			'created' => date('Y-m-d H:i:s'),
			static::COLUMN_NAME['message'][$this->roomType] => $this->recordId
		])->execute();
		Cache::delete("Chat_{$this->roomType}", '');
		return $this->lastMessageId = (int) $db->getLastInsertID("{$table}_id_seq");
	}

	/**
	 * Get entries function.
	 *
	 * @param int|null $messageId
	 * @param string   $condition
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \yii\db\Exception
	 *
	 * @return array
	 */
	public function getEntries(?int $messageId = null, string $condition = '>', ?string $searchVal = null)
	{
		if (!$this->isRoomExists()) {
			return [];
		}
		$this->lastMessageId = $messageId;
		$rows = [];
		$dataReader = $this->getQueryMessage($messageId, $condition, $searchVal)->createCommand()->query();
		while ($row = $dataReader->read()) {
			$userModel = User::getUserModel($row['userid']);
			$row['image'] = $userModel->getImage();
			$row['created'] = Fields\DateTime::formatToShort($row['created']);
			$row['user_name'] = $userModel->getName();
			$row['role_name'] = Language::translate($userModel->getRoleInstance()->getName());
			$rows[] = $row;
			$mid = (int) $row['id'];
			if ($this->lastMessageId < $mid) {
				$this->lastMessageId = $mid;
			}
		}
		$dataReader->close();
		if ($condition === '>') {
			$this->updateRoom();
		}
		return \array_reverse($rows);
	}

	/**
	 * Get history.
	 *
	 * @param null|string $since
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	public function getHistory(?string $since = null)
	{
		$queryGroup = (new Db\Query())
			->select([
				'messages', 'userid', 'created',
				'recordid' => static::COLUMN_NAME['message']['group'],
				'room_type' => new \yii\db\Expression("'group'")
			])
			->from(['GR' => static::TABLE_NAME['message']['group']])
			->where(['userid' => $this->userId]);
		$queryGlobal = (new Db\Query())
			->select([
				'messages', 'userid', 'created',
				'recordid' => static::COLUMN_NAME['message']['global'],
				'room_type' => new \yii\db\Expression("'global'")
			])
			->from(['GR' => static::TABLE_NAME['message']['global']])
			->where(['userid' => $this->userId]);
		$queryCrm = (new Db\Query())
			->select([
				'messages', 'userid', 'created',
				'recordid' => static::COLUMN_NAME['message']['crm'],
				'room_type' => new \yii\db\Expression("'crm'")
			])
			->from(['C' => static::TABLE_NAME['message']['crm']])
			->union($queryGroup, true)
			->union($queryGlobal, true)
			->where(['userid' => $this->userId]);
		$query = (new Db\Query())->from(['T' => $queryCrm])
			->orderBy(['created' => \SORT_DESC])
			->limit(\AppConfig::module('Chat', 'rows_limit') + 1);
		if (!empty($since)) {
			$query->where(['<', 'created', $since]);
		}
		$userModel = User::getUserModel($this->userId);
		$rows = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$row['id'] = $row['created'];
			$row['image'] = $userModel->getImage();
			$row['created'] = Fields\DateTime::formatToShort($row['created']);
			$row['user_name'] = $userModel->getName();
			$row['role_name'] = Language::translate($userModel->getRoleInstance()->getName());
			$rows[] = $row;
		}
		return \array_reverse($rows);
	}

	/**
	 * Get getParticipants.
	 *
	 * @param int[] $excludedId
	 *
	 * @return array
	 */
	public function getParticipants()
	{
		if (empty($this->recordId) || empty($this->roomType)) {
			return [];
		}
		$subQuery = (new DB\Query())
			->select(['userid', 'last_id' => new \yii\db\Expression('max(id)')])
			->from(static::TABLE_NAME['message'][$this->roomType])
			->where([static::COLUMN_NAME['message'][$this->roomType] => $this->recordId])
			->groupBy(['userid']);
		$query = (new DB\Query())
			->from(['GL' => static::TABLE_NAME['message'][$this->roomType]])
			->innerJoin(['LM' => $subQuery], 'LM.last_id = GL.id');
		$dataReader = $query->createCommand()->query();
		$participants = [];
		while ($row = $dataReader->read()) {
			$userModel = User::getUserModel($row['userid']);
			$participants[] = [
				'user_id' => $row['userid'],
				'message' => $row['messages'],
				'user_name' => $userModel->getName(),
				'role_name' => Language::translate($userModel->getRoleInstance()->getName()),
				'image' => $userModel->getImage()
			];
		}
		$dataReader->close();
		return $participants;
	}

	/**
	 * Remove room from favorites.
	 *
	 * @throws \yii\db\Exception
	 */
	public function removeFromFavorites()
	{
		if (!empty($this->roomType) && !empty($this->recordId)) {
			Db::getInstance()->createCommand()->delete(
				static::TABLE_NAME['room'][$this->roomType], [
				'userid' => $this->userId,
				static::COLUMN_NAME['room'][$this->roomType] => $this->recordId
			])->execute();
		}
	}

	/**
	 * Add room to favorites.
	 *
	 * @throws \yii\db\Exception
	 */
	public function addToFavorites()
	{
		if (!empty($this->roomType) && !empty($this->recordId)) {
			Db::getInstance()->createCommand()->insert(
				static::TABLE_NAME['room'][$this->roomType], [
				'userid' => $this->userId,
				'last_message' => null,
				static::COLUMN_NAME['room'][$this->roomType] => $this->recordId
			])->execute();
		}
	}

	/**
	 * Get a query for chat messages.
	 *
	 * @param int|null $messageId
	 * @param string   $condition
	 * @param bool     $isLimit
	 *
	 * @throws \App\Exceptions\IllegalValue
	 *
	 * @return \App\Db\Query
	 */
	private function getQueryMessage(?int $messageId, string $condition = '>', ?string $searchVal = null, bool $isLimit = true): Db\Query
	{
		$query = null;
		switch ($this->roomType) {
			case 'crm':
				$query = (new Db\Query())
					->select(['C.*', 'U.user_name', 'U.last_name'])
					->from(['C' => 'u_#__chat_messages_crm'])
					->leftJoin(['U' => 'vtiger_users'], 'U.id = C.userid')
					->where(['crmid' => $this->recordId]);
				break;
			case 'group':
				$query = (new Db\Query())
					->select(['C.*', 'U.user_name', 'U.last_name'])
					->from(['C' => 'u_#__chat_messages_group'])
					->leftJoin(['U' => 'vtiger_users'], 'U.id = C.userid')
					->where(['groupid' => $this->recordId]);
				break;
			case 'global':
				$query = (new Db\Query())
					->select(['C.*', 'U.user_name', 'U.last_name'])
					->from(['C' => 'u_#__chat_messages_global'])
					->leftJoin(['U' => 'vtiger_users'], 'U.id = C.userid')
					->where(['globalid' => $this->recordId]);
				break;
			default:
				throw new Exceptions\IllegalValue("ERR_NOT_ALLOWED_VALUE||$this->roomType", 406);
		}
		if (!\is_null($messageId)) {
			$query->andWhere([$condition, 'C.id', $messageId]);
		}
		if (!empty($searchVal)) {
			$query->andWhere(['LIKE', 'C.messages', $searchVal]);
		}
		if ($isLimit) {
			$query->limit(\AppConfig::module('Chat', 'rows_limit') + 1);
		}
		return $query->orderBy(['created' => \SORT_DESC]);
	}

	/**
	 * Get a query for chat room.
	 *
	 * @return \App\Db\Query
	 */
	private function getQueryRoom(): Db\Query
	{
		switch ($this->roomType) {
			case 'crm':
				return (new Db\Query())
					->select(['CR.roomid', 'CR.userid', 'record_id' => 'CR.crmid', 'CR.last_message'])
					->from(['CR' => 'u_#__chat_rooms_crm'])
					->where(['CR.crmid' => $this->recordId])
					->andWhere(['CR.userid' => $this->userId]);
			case 'group':
				return (new Db\Query())
					->select(['CR.roomid', 'CR.userid', 'record_id' => 'CR.groupid', 'CR.last_message'])
					->from(['CR' => 'u_#__chat_rooms_group'])
					->where(['CR.groupid' => $this->recordId])
					->andWhere(['CR.userid' => $this->userId]);
			case 'global':
				return (new Db\Query())
					->select(['CG.*', 'CR.userid', 'record_id' => 'CR.global_room_id', 'CR.last_message'])
					->from(['CG' => 'u_#__chat_global'])
					->leftJoin(['CR' => 'u_#__chat_rooms_global'], "CR.global_room_id = CG.global_room_id AND CR.userid = {$this->userId}")
					->where(['CG.global_room_id' => $this->recordId]);
		}
		throw new Exceptions\IllegalValue("ERR_NOT_ALLOWED_VALUE||$this->roomType", 406);
	}

	/**
	 * Update last message ID.
	 *
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \yii\db\Exception
	 */
	private function updateRoom()
	{
		if ($this->roomType === 'global' && !$this->isAssigned()) {
			Db::getInstance()->createCommand()
				->insert(static::TABLE_NAME['room'][$this->roomType], [
					static::COLUMN_NAME['room'][$this->roomType] => $this->recordId,
					'last_message' => $this->lastMessageId,
					'userid' => $this->userId,
				])->execute();
			$this->room['last_message'] = $this->lastMessageId;
			$this->room['record_id'] = $this->recordId;
			$this->room['userid'] = $this->userId;
		} elseif (
			\is_array($this->room) && $this->isAssigned() &&
			(empty($this->room['last_message']) || $this->lastMessageId > (int) $this->room['last_message'])
		) {
			Db::getInstance()
				->createCommand()
				->update(static::TABLE_NAME['room'][$this->roomType], ['last_message' => $this->lastMessageId], [
					static::COLUMN_NAME['room'][$this->roomType] => $this->recordId,
					'userid' => $this->userId
				])->execute();
			$this->room['last_message'] = $this->lastMessageId;
		}
	}
}
