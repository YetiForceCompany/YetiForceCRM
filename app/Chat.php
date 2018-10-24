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
		'record' => ['crm' => 'crmid', 'group' => 'groupid', 'global' => 'globalid'],
		'recordRoom' => ['crm' => 'crmid', 'group' => 'groupid', 'global' => 'global_room_id'],
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
				'recordId' => $row[static::COLUMN_NAME['recordRoom']['global']]
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
		$recordIdName = static::COLUMN_NAME['recordRoom'][$roomType];
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
		return Cache::save('Chat', 'chat_global',
			(new Db\Query())->select(['name', 'recordid' => 'global_room_id'])
				->from('u_#__chat_global')->all()
		);
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
		return (new Db\Query())
			->select(['GR.roomid', 'GR.userid', 'recordid' => 'GR.groupid', 'name' => 'VGR.groupname'])
			->from(['GR' => 'u_#__chat_rooms_group'])
			->innerJoin(['VGR' => 'vtiger_groups'], 'VGR.groupid = GR.groupid')
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
		$subQuery = (new \App\Db\Query())
			->select(['CR.crmid', 'CR.userid', 'cnt_new_message' => 'COUNT(*)'])
			->from(['CR' => static::TABLE_NAME['room']['crm']])
			->innerJoin(['CM' => static::TABLE_NAME['message']['crm']], 'CM.crmid = CR.crmid')
			->where(['>', 'CM.id', new \yii\db\Expression('CR.last_message')])
			->orWhere(['CR.last_message' => null])
			->groupBy(['CR.crmid', 'CR.userid']);
		return (new Db\Query())
			->select(['C.roomid', 'C.userid', 'recordid' => 'C.crmid', 'name' => 'CL.label', 'CNT.cnt_new_message'])
			->from(['C' => 'u_#__chat_rooms_crm'])
			->leftJoin(['CL' => 'u_yf_crmentity_label'], 'CL.crmid = C.crmid')
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
			static::COLUMN_NAME['record'][$this->roomType] => $this->recordId
		])->execute();
		return $this->lastMessageId = (int) $db->getLastInsertID("{$table}_id_seq");
	}

	/**
	 * Get entries function.
	 *
	 * @param null|int $messageId
	 *
	 * @return array
	 */
	public function getEntries(?int $messageId = null)
	{
		if (!$this->isRoomExists()) {
			return [];
		}
		$query = $this->getQueryMessage();
		if (!\is_null($messageId)) {
			$query->andWhere(['>', 'C.id', $messageId]);
		}
		$this->lastMessageId = $messageId;
		$rows = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$userModel = User::getUserModel($row['userid']);
			$row['image'] = \Vtiger_Record_Model::getInstanceById($row['userid'], 'Users')->getImage();
			$row['created'] = Fields\DateTime::formatToMoreReadable($row['created']);
			$row['user_name'] = $userModel->getName();
			$row['role_name'] = Language::translate($userModel->getRoleInstance()->getName());
			$rows[] = $row;
			$mid = (int) $row['id'];
			if ($this->lastMessageId < $mid) {
				$this->lastMessageId = $mid;
			}
		}
		$dataReader->close();
		$this->updateRoom();
		return $rows;
	}

	/**
	 * Get getParticipants.
	 *
	 * @param int[] $excludedId
	 *
	 * @return array
	 */
	public function getParticipants($excludedId = [])
	{
		if (empty($this->recordId) || empty($this->roomType)) {
			return [];
		}
		$subQuery = (new \App\DB\Query())
			->select(['userid', 'last_id' => new \yii\db\Expression('max(id)')])
			->from(static::TABLE_NAME['message'][$this->roomType])
			->where([static::COLUMN_NAME['record'][$this->roomType] => $this->recordId])
			->groupBy(['userid']);
		$query = (new \App\DB\Query())
			->from(['GL' => static::TABLE_NAME['message'][$this->roomType]])
			->innerJoin(['LM' => $subQuery], 'LM.last_id = GL.id');
		/*if ($excludedId) {
			$query->where(['not', ['GL.userid' => $excludedId]]);
		}*/
		$dataReader = $query->createCommand()->query();
		$users = [];
		while ($row = $dataReader->read()) {
			$userModel = User::getUserModel($row['userid']);
			$users[] = [
				'user_id' => $row['userid'],
				'message' => $row['messages'],
				'user_name' => $userModel->getName(),
				'role_name' => Language::translate($userModel->getRoleInstance()->getName()),
				'image' => \Vtiger_Record_Model::getInstanceById($row['userid'], 'Users')->getImage()
			];
		}
		$dataReader->close();
		return $users;
	}

	/**
	 * Get a query for chat messages.
	 *
	 * @return \App\Db\Query
	 */
	private function getQueryMessage(bool $isLimit = true): Db\Query
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
		$query->orderBy(['created' => \SORT_ASC]);
		if ($isLimit) {
			$query->limit(\AppConfig::module('Chat', 'ROWS_LIMIT'));
		}
		return $query;
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
				return (new \App\Db\Query())
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
					static::COLUMN_NAME['record'][$this->roomType] => $this->recordId,
					'userid' => $this->userId
				])->execute();
			$this->room['last_message'] = $this->lastMessageId;
		}
	}
}
