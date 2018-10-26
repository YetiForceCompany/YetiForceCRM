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
		'room' => ['crm' => 'u_#__chat_rooms_crm', 'group' => 'u_#__chat_rooms_group', 'global' => 'u_#__chat_rooms_global']
	];

	/**
	 * Information about the columns of the database.
	 */
	const COLUMN_NAME = [
		//'record' => ['crm' => 'crmid', 'group' => 'groupid', 'global' => 'globalid'],
		//'recordRoom' => ['crm' => 'crmid', 'group' => 'groupid', 'global' => 'global_room_id'],
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
		$table = static::TABLE_NAME['room'][$roomType];
		$recordIdName = static::COLUMN_NAME['room'][$roomType];
		Db::getInstance()->createCommand()->insert($table, [
			'userid' => User::getCurrentUserId(),
			'last_message' => null,
			$recordIdName => $recordId
		])->execute();
		$instance->recordId = $recordId;
		$instance->roomType = $roomType;
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
		if (Cache::has('Chat', 'chat_global')) {
			return Cache::get('Chat', 'chat_global');
		}
		/*$roomIdName = static::COLUMN_NAME['room']['global'];
		$messageIdName = static::COLUMN_NAME['message']['global'];
		$subQuery = (new Db\Query())
			->select(['CR.' . $roomIdName, 'CR.userid', 'cnt_new_message' => 'COUNT(*)'])
			->from(['CR' => static::TABLE_NAME['room']['global']])
			->innerJoin(['CM' => static::TABLE_NAME['message']['global']], "CM.{$messageIdName} = CR.{$roomIdName}")
			->where(['>', 'CM.id', new \yii\db\Expression('CR.last_message')])
			->orWhere(['CR.last_message' => null])
			->groupBy(['CR.' . $roomIdName, 'CR.userid']);*/
		$dataReader = (new Db\Query())
			->select(['name', 'recordid' => 'GL.global_room_id', 'cnt_new_message' => new \yii\db\Expression('0')])
			->from(['GL' => 'u_#__chat_global'])
			//->leftJoin(['CNT' => $subQuery], 'CNT.global_room_id = GL.global_room_id AND CNT.userid = GL.userid')
			->createCommand()->query();
		$rooms = [];
		while ($row = $dataReader->read()) {
			$row['name'] = Language::translate($row['name'], 'Chat');
			$rooms[] = $row;
		}
		$dataReader->close();
		return Cache::save('Chat', 'chat_global', $rooms);
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
		$subQuery = (new Db\Query())
			->select(['CR.groupid', 'CR.userid', 'cnt_new_message' => 'COUNT(*)'])
			->from(['CR' => static::TABLE_NAME['room']['group']])
			->innerJoin(['CM' => static::TABLE_NAME['message']['group']], 'CM.groupid = CR.groupid')
			->where(['>', 'CM.id', new \yii\db\Expression('CR.last_message')])
			->orWhere(['CR.last_message' => null])
			->groupBy(['CR.groupid', 'CR.userid']);
		return (new Db\Query())
			->select(['GR.roomid', 'GR.userid', 'recordid' => 'GR.groupid', 'name' => 'VGR.groupname', 'CNT.cnt_new_message'])
			->from(['GR' => 'u_#__chat_rooms_group'])
			->innerJoin(['VGR' => 'vtiger_groups'], 'VGR.groupid = GR.groupid')
			->leftJoin(['CNT' => $subQuery], 'CNT.groupid = GR.groupid AND CNT.userid = GR.userid')
			->where(['GR.userid' => $userId])
			->all();
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
		$subQuery = (new Db\Query())
			->select(['CR.crmid', 'CR.userid', 'cnt_new_message' => 'COUNT(*)'])
			->from(['CR' => static::TABLE_NAME['room']['crm']])
			->innerJoin(['CM' => static::TABLE_NAME['message']['crm']], 'CM.crmid = CR.crmid')
			->where(['>', 'CM.id', new \yii\db\Expression('CR.last_message')])
			->orWhere(['CR.last_message' => null])
			->groupBy(['CR.crmid', 'CR.userid']);
		return (new Db\Query())
			->select(['C.roomid', 'C.userid', 'recordid' => 'C.crmid', 'name' => 'CL.label', 'CNT.cnt_new_message'])
			->from(['C' => 'u_#__chat_rooms_crm'])
			->leftJoin(['CL' => 'u_#__crmentity_label'], 'CL.crmid = C.crmid')
			->leftJoin(['CNT' => $subQuery], 'CNT.crmid = C.crmid AND CNT.userid = C.userid')
			->where(['C.userid' => $userId])
			->all();
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
			$query->limit(\AppConfig::module('Chat', 'ROWS_LIMIT') + 1);
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
					->where(['CG.global_room_id' => $this->recordId])
					->andWhere(['CR.userid' => $this->userId]);
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
		if (\is_array($this->room) && (empty($this->room['last_message']) || $this->lastMessageId > (int) $this->room['last_message'])) {
			Db::getInstance()
				->createCommand()
				->update(static::TABLE_NAME['room'][$this->roomType], ['last_message' => $this->lastMessageId], [
					static::COLUMN_NAME['message'][$this->roomType] => $this->recordId,
					'userid' => $this->userId
				])->execute();
			$this->room['last_message'] = $this->lastMessageId;
		}
	}
}
