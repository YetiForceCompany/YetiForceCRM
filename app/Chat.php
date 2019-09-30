<?php

/**
 * Chat.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */

namespace App;

/**
 * Class Chat.
 */
final class Chat
{
	/**
	 * Information about allowed types of rooms.
	 */
	const ALLOWED_ROOM_TYPES = ['crm', 'group', 'global', 'private'];

	/**
	 * Information about the tables of the database.
	 */
	const TABLE_NAME = [
		'message' => ['crm' => 'u_#__chat_messages_crm', 'group' => 'u_#__chat_messages_group', 'global' => 'u_#__chat_messages_global', 'private' => 'u_#__chat_messages_private'],
		'room' => ['crm' => 'u_#__chat_rooms_crm', 'group' => 'u_#__chat_rooms_group', 'global' => 'u_#__chat_rooms_global', 'private' => 'u_#__chat_rooms_private'],
		'room_name' => ['crm' => 'u_#__crmentity_label', 'group' => 'vtiger_groups', 'global' => 'u_#__chat_global', 'private' => 'u_#__chat_private']
	];

	/**
	 * Information about the columns of the database.
	 */
	const COLUMN_NAME = [
		'message' => ['crm' => 'crmid', 'group' => 'groupid', 'global' => 'globalid', 'private' => 'privateid'],
		'room' => ['crm' => 'crmid', 'group' => 'groupid', 'global' => 'global_room_id', 'private' => 'private_room_id'],
		'room_name' => ['crm' => 'label', 'group' => 'groupname', 'global' => 'name', 'private' => 'name']
	];

	/**
	 * Type of chat room.
	 *
	 * @var string
	 */
	public $roomType;

	/**
	 * ID record associated with the chat room.
	 *
	 * @var int|null
	 */
	public $recordId;

	/**
	 * @var array|false
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
	 * Set default room as current room.
	 *
	 * @return array
	 */
	public static function setCurrentRoomDefault()
	{
		$defaultRoom = static::getDefaultRoom();
		return static::setCurrentRoom($defaultRoom['roomType'], $defaultRoom['recordId']);
	}

	/**
	 * Get current room ID, type.
	 *
	 * @return []|false
	 */
	public static function getCurrentRoom()
	{
		$recordId = $_SESSION['chat']['recordId'] ?? null;
		$roomType = $_SESSION['chat']['roomType'] ?? null;
		if (!isset($_SESSION['chat'])) {
			$result = static::getDefaultRoom();
		} elseif ('crm' === $roomType && (!Record::isExists($recordId) || !\Vtiger_Record_Model::getInstanceById($recordId)->isViewable())) {
			$result = static::getDefaultRoom();
		} elseif ('group' === $roomType && !isset(User::getCurrentUserModel()->getGroupNames()[$recordId])) {
			$result = static::getDefaultRoom();
		} else {
			$result = $_SESSION['chat'];
		}
		return $result;
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
		return $instance;
	}

	/**
	 * Get instance \App\Chat.
	 *
	 * @param string|null $roomType
	 * @param int|null    $recordId
	 *
	 * @throws \App\Exceptions\IllegalValue
	 *
	 * @return \App\Chat
	 */
	public static function getInstance(?string $roomType = null, ?int $recordId = null): self
	{
		if (empty($roomType) || null === $recordId) {
			$currentRoom = static::getCurrentRoom();
			if (false !== $currentRoom) {
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
		$roomIdName = static::COLUMN_NAME['room']['global'];
		$cntQuery = (new Db\Query())
			->select([new \yii\db\Expression('COUNT(*)')])
			->from(['CM' => 'u_yf_chat_messages_global'])
			->where([
				'CM.globalid' => new \yii\db\Expression('CR.global_room_id')
			])->andWhere(['>', 'CM.id', new \yii\db\Expression('CR.last_message')]);
		$subQuery = (new Db\Query())
			->select([
				'CR.*',
				'cnt_new_message' => $cntQuery
			])
			->from(['CR' => 'u_yf_chat_rooms_global']);
		$dataReader = (new Db\Query())
			->select(['name', 'recordid' => 'GL.global_room_id', 'CNT.cnt_new_message', 'CNT.userid'])
			->from(['GL' => 'u_#__chat_global'])
			->leftJoin(['CNT' => $subQuery], "CNT.{$roomIdName} = GL.global_room_id AND CNT.userid = {$userId}")
			->createCommand()->query();
		$rooms = [];
		while ($row = $dataReader->read()) {
			$row['name'] = Language::translate($row['name'], 'Chat');
			$row['roomType'] = 'global';
			$row['isPinned'] = isset($row['userid']);
			$rooms[$row['recordid']] = $row;
		}
		$dataReader->close();
		return $rooms;
	}

	/**
	 * List of private chat rooms.
	 *
	 * @param int|null $userId
	 *
	 * @return array
	 */
	public static function getRoomsPrivate(?int $userId = null)
	{
		if (empty($userId)) {
			$userId = User::getCurrentUserId();
		}
		$roomIdName = static::COLUMN_NAME['room']['private'];
		$cntQuery = (new Db\Query())
			->select([new \yii\db\Expression('COUNT(*)')])
			->from(['CM' => 'u_yf_chat_messages_private'])
			->where([
				'CM.privateid' => new \yii\db\Expression('CR.private_room_id')
			])->andWhere(['>', 'CM.id', new \yii\db\Expression('CR.last_message')]);
		$subQuery = (new Db\Query())
			->select([
				'CR.*',
				'cnt_new_message' => $cntQuery,
			])
			->from(['CR' => 'u_yf_chat_rooms_private']);
		$query = (new Db\Query())
			->select(['name', 'recordid' => 'GL.private_room_id', 'CNT.cnt_new_message', 'CNT.userid', 'creatorid', 'created'])
			->where(['archived' => 0])
			->from(['GL' => 'u_#__chat_private']);
		if (User::getUserModel($userId)->isAdmin()) {
			$query->leftJoin(['CNT' => $subQuery], "CNT.{$roomIdName} = GL.private_room_id AND CNT.userid = {$userId}");
		} else {
			$query->innerJoin(['CNT' => $subQuery], "CNT.{$roomIdName} = GL.private_room_id AND CNT.userid = {$userId}");
		}
		$dataReader = $query->createCommand()->query();
		$rooms = [];
		while ($row = $dataReader->read()) {
			$row['name'] = Language::translate($row['name'], 'Chat');
			$row['roomType'] = 'private';
			$row['isPinned'] = isset($row['userid']) ? true : false;
			$rooms[$row['recordid']] = $row;
		}
		$dataReader->close();
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
		$groups = User::getUserModel($userId)->getGroupNames();
		$subQuery = (new Db\Query())
			->select(['CR.groupid', 'CR.userid', 'cnt_new_message' => 'COUNT(*)'])
			->from(['CR' => static::TABLE_NAME['room']['group']])
			->innerJoin(['CM' => static::TABLE_NAME['message']['group']], 'CM.groupid = CR.groupid')
			->where(['>', 'CM.id', new \yii\db\Expression('CR.last_message')])
			->groupBy(['CR.groupid', 'CR.userid']);
		$dataReader = (new Db\Query())
			->select(['GR.roomid', 'GR.userid', 'recordid' => 'GR.groupid', 'name' => 'VGR.groupname', 'CNT.cnt_new_message'])
			->from(['GR' => 'u_#__chat_rooms_group'])
			->innerJoin(['VGR' => 'vtiger_groups'], 'VGR.groupid = GR.groupid')
			->leftJoin(['CNT' => $subQuery], 'CNT.groupid = GR.groupid AND CNT.userid = GR.userid')
			->where(['GR.userid' => $userId])->createCommand()->query();
		$rows = [];
		while ($row = $dataReader->read()) {
			if (isset($groups[$row['recordid']])) {
				$row['isPinned'] = true;
				$groups[$row['recordid']] = $row;
			}
		}
		foreach ($groups as $id => $group) {
			if (\is_string($group)) {
				$group = ['recordid' => $id, 'name' => $group, 'isPinned' => false];
			}
			$group['roomType'] = 'group';
			$rows[$id] = $group;
		}
		$dataReader->close();
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
		$subQuery = (new Db\Query())
			->select(['CR.crmid', 'CR.userid', 'cnt_new_message' => 'COUNT(*)'])
			->from(['CR' => static::TABLE_NAME['room']['crm']])
			->innerJoin(['CM' => static::TABLE_NAME['message']['crm']], 'CM.crmid = CR.crmid')
			->where(['>', 'CM.id', new \yii\db\Expression('CR.last_message')])
			->orWhere(['CR.last_message' => null])
			->groupBy(['CR.crmid', 'CR.userid']);
		$dataReader = (new Db\Query())
			->select(['C.roomid', 'C.userid', 'recordid' => 'C.crmid', 'name' => 'CL.label', 'CNT.cnt_new_message'])
			->from(['C' => 'u_#__chat_rooms_crm'])
			->leftJoin(['CL' => 'u_#__crmentity_label'], 'CL.crmid = C.crmid')
			->leftJoin(['CNT' => $subQuery], 'CNT.crmid = C.crmid AND CNT.userid = C.userid')
			->where(['C.userid' => $userId])->createCommand()->query();
		$rows = [];
		while ($row = $dataReader->read()) {
			$recordModel = \Vtiger_Record_Model::getInstanceById($row['recordid']);
			if ($recordModel->isViewable()) {
				$row['moduleName'] = $recordModel->getModuleName();
				$row['roomType'] = 'crm';
				$row['isPinned'] = true;
				$rows[$row['recordid']] = $row;
			}
		}
		$dataReader->close();
		return $rows;
	}

	/**
	 * Get room last message.
	 *
	 * @param int    $roomId
	 * @param string $roomType
	 *
	 * @return array
	 */
	public static function getRoomLastMessage(int $roomId, string $roomType): array
	{
		return (array) (new Db\Query())
			->from(static::TABLE_NAME['message'][$roomType])
			->where([static::COLUMN_NAME['message'][$roomType] => $roomId])
			->orderBy(['id' => \SORT_DESC])
			->one();
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
			$userId = User::getCurrentUserId();
		}
		if (Cache::staticHas('ChatGetRoomsByUser', $userId)) {
			return Cache::staticGet('ChatGetRoomsByUser', $userId);
		}
		$roomsByUser = [
			'crm' => static::getRoomsCrm($userId),
			'group' => static::getRoomsGroup($userId),
			'global' => static::getRoomsGlobal(),
			'private' => static::getRoomsPrivate($userId)
		];
		Cache::staticSave('ChatGetRoomsByUser', $userId);
		return $roomsByUser;
	}

	/**
	 * Rerun the number of new messages.
	 *
	 * @return array
	 */
	public static function getNumberOfNewMessages(): array
	{
		$numberOfNewMessages = 0;
		$roomInfo = static::getRoomsByUser();
		$roomList = [];
		foreach (['crm', 'group', 'global', 'private'] as $roomType) {
			foreach ($roomInfo[$roomType] as $room) {
				if (!empty($room['cnt_new_message'])) {
					$numberOfNewMessages += $room['cnt_new_message'];
					$roomList[$roomType][$room['recordid']]['cnt_new_message'] = $room['cnt_new_message'];
				}
			}
		}
		return ['roomList' => $roomList, 'amount' => $numberOfNewMessages];
	}

	/**
	 * Get user info.
	 *
	 * @param int $userId
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	public function getUserInfo(int $userId)
	{
		if (User::isExists($userId)) {
			$userModel = User::getUserModel($userId);
			$image = $userModel->getImage();
			$userName = $userModel->getName();
			$isAdmin = $userModel->isAdmin();
			$userRoleName = Language::translate($userModel->getRoleInstance()->getName());
		} else {
			$image = $isAdmin = $userName = $userRoleName = null;
		}
		return [
			'user_name' => $userName,
			'role_name' => $userRoleName,
			'isAdmin' => $isAdmin,
			'image' => $image['url'] ?? null,
		];
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
			->leftJoin(['GL' => static::TABLE_NAME['room']['global']], "GL.global_room_id = CG.global_room_id AND GL.userid = {$userId}")
			->where(['or', ['GL.userid' => null], ['GL.userid' => $userId]])
			->andWhere(['or', ['GL.last_message' => null], ['<', 'GL.last_message', new \yii\db\Expression('CM.id')]])
			->exists();
	}

	/**
	 * Is there any new message for global.
	 *
	 * @param int $userId
	 *
	 * @return bool
	 */
	public static function isNewMessagesForPrivate(int $userId): bool
	{
		$subQuery = (new Db\Query())
			->select([
				static::COLUMN_NAME['message']['private'],
				'id' => new \yii\db\Expression('max(id)')
			])->from(static::TABLE_NAME['message']['private'])
			->groupBy([static::COLUMN_NAME['message']['private']]);
		return (new Db\Query())
			->select(['CG.name', 'CM.id'])
			->from(['CG' => 'u_#__chat_private'])
			->innerJoin(['CM' => $subQuery], 'CM.privateid = CG.private_room_id')
			->innerJoin(['GL' => static::TABLE_NAME['room']['private']], "GL.private_room_id = CG.private_room_id AND GL.userid = {$userId}")
			->where(['or', ['GL.userid' => null], ['GL.userid' => $userId]])
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
			->andWhere(['or', ['C.last_message' => null], ['<', 'C.last_message', new \yii\db\Expression('CM.id')]])
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
			->andWhere(['or', ['GR.last_message' => null], ['<', 'GR.last_message', new \yii\db\Expression('CM.id')]])
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
			static::isNewMessagesForGroup($userId) ||
			static::isNewMessagesForPrivate($userId);
	}

	/**
	 * Chat constructor.
	 *
	 * @param string|null $roomType
	 * @param int|null    $recordId
	 *
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function __construct(?string $roomType, ?int $recordId)
	{
		$this->userId = User::getCurrentUserId();
		if (empty($roomType) || null === $recordId) {
			return;
		}
		$this->roomType = $roomType;
		$this->recordId = $recordId;
		$this->room = $this->getQueryRoom()->one();
		if (('crm' === $this->roomType || 'group' === $this->roomType) && !$this->isRoomExists()) {
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
		return false !== $this->room;
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
	 * Is private room allowed for specified user.
	 *
	 * @param int $recordId
	 * @param int $userId
	 *
	 * @return bool
	 */
	public function isPrivateRoomAllowed(int $recordId, ?int $userId = null): bool
	{
		if (empty($userId)) {
			$userId = $this->userId;
		}
		return (new Db\Query())
			->select(['userid', static::COLUMN_NAME['room']['private']])
			->from(static::TABLE_NAME['room']['private'])
			->where(['and', ['userid' => $userId], [static::COLUMN_NAME['room']['private'] => $recordId]])
			->exists();
	}

	/**
	 * Is room moderator.
	 *
	 * @param int $recordId
	 *
	 * @return bool
	 */
	public function isRoomModerator(int $recordId): bool
	{
		if (User::getUserModel($this->userId)->isAdmin()) {
			return true;
		}
		return (new Db\Query())
			->select(['creatorid', static::COLUMN_NAME['room']['private']])
			->from(static::TABLE_NAME['room_name']['private'])
			->where(['and', ['creatorid' => $this->userId], [static::COLUMN_NAME['room']['private'] => $recordId]])
			->exists();
	}

	/**
	 * Is record owner.
	 *
	 * @return bool
	 */
	public function isRecordOwner(): bool
	{
		return (new Db\Query())
			->select(['userid', static::COLUMN_NAME['room']['private']])
			->from(static::TABLE_NAME['room']['private'])
			->where(['and', ['userid' => $this->userId], [static::COLUMN_NAME['room'][$this->getRoomType()] => $this->getRecordId()]])
			->exists();
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
	 * @param ?string  $searchVal
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
			$row['messages'] = static::decodeMessage($row['messages']);
			$row['created'] = Fields\DateTime::formatToShort($row['created']);
			[
				'user_name' => $row['user_name'],
				'role_name' => $row['role_name'],
				'image' => $row['image']
			] = $this->getUserInfo($row['userid']);
			$rows[] = $row;
			$mid = (int) $row['id'];
			if ($this->lastMessageId < $mid) {
				$this->lastMessageId = $mid;
			}
		}
		$dataReader->close();
		if ('>' === $condition) {
			$this->updateRoom();
		}
		return \array_reverse($rows);
	}

	/**
	 * Get history by type.
	 *
	 * @param string   $roomType
	 * @param int|null $messageId
	 *
	 * @return array
	 */
	public function getHistoryByType(string $roomType = 'global', ?int $messageId = null)
	{
		$columnMessage = static::COLUMN_NAME['message'][$roomType];
		$columnRoomName = static::COLUMN_NAME['room_name'][$roomType];
		$roomNameId = 'global' === $roomType || 'private' === $roomType ? static::COLUMN_NAME['room'][$roomType] : $columnMessage;
		$query = (new Db\Query())
			->select([
				'id', 'messages', 'userid', 'GL.created',
				'recordid' => "GL.{$columnMessage}", 'room_name' => "RN.{$columnRoomName}"
			])
			->from(['GL' => static::TABLE_NAME['message'][$roomType]])
			->leftJoin(['RN' => static::TABLE_NAME['room_name'][$roomType]], "RN.{$roomNameId} = GL.{$columnMessage}")
			->where(['userid' => $this->userId])
			->orderBy(['id' => \SORT_DESC])
			->limit(\App\Config::module('Chat', 'CHAT_ROWS_LIMIT') + 1);
		if (null !== $messageId) {
			$query->andWhere(['<', 'id', $messageId]);
		}
		$userModel = User::getUserModel($this->userId);
		$groups = $userModel->getGroupNames();
		$userImage = $userModel->getImage()['url'] ?? '';
		$userName = $userModel->getName();
		$userRoleName = $userModel->getRoleInstance()->getName();
		$rows = [];
		$dataReader = $query->createCommand()->query();
		$notPermittedIds = [];
		while ($row = $dataReader->read()) {
			if ('group' === $roomType && !isset($groups[$row['recordid']])) {
				continue;
			}
			if ('crm' === $roomType) {
				if (\in_array($row['recordid'], $notPermittedIds)) {
					continue;
				}
				if (!Record::isExists($row['recordid']) || !\Vtiger_Record_Model::getInstanceById($row['recordid'])->isViewable()) {
					$notPermittedIds[] = $row['recordid'];
					continue;
				}
			}
			if ('global' === $roomType) {
				$row['room_name'] = Language::translate($row['room_name']);
			}
			$row['image'] = $userImage;
			$row['created'] = Fields\DateTime::formatToShort($row['created']);
			$row['user_name'] = $userName;
			$row['role_name'] = $userRoleName;
			$row['messages'] = static::decodeMessage($row['messages']);
			$rows[] = $row;
		}
		return \array_reverse($rows);
	}

	/**
	 * Get default room.
	 *
	 * @return array|false
	 */
	public static function getDefaultRoom()
	{
		if (Cache::has('Chat', 'DefaultRoom')) {
			return Cache::get('Chat', 'DefaultRoom');
		}
		$room = false;
		$row = (new Db\Query())->from('u_#__chat_global')->where(['name' => 'LBL_GENERAL'])->one();
		if (false !== $row) {
			$room = [
				'roomType' => 'global',
				'recordId' => $row[static::COLUMN_NAME['room']['global']]
			];
		}
		Cache::save('Chat', 'DefaultRoom', $room);
		return $room;
	}

	/**
	 * Get query for unread messages.
	 *
	 * @param string $roomType
	 *
	 * @return \App\Db\Query
	 */
	private static function getQueryForUnread(string $roomType = 'global'): Db\Query
	{
		$userId = User::getCurrentUserId();
		$columnRoom = static::COLUMN_NAME['room'][$roomType];
		$columnMessage = static::COLUMN_NAME['message'][$roomType];
		$query = (new Db\Query())->from(['M' => static::TABLE_NAME['message'][$roomType]]);
		switch ($roomType) {
			case 'crm':
				$query->select(['M.*', 'name' => 'RN.label', 'R.last_message', 'recordid' => "M.{$columnMessage}"])
					->innerJoin(
						['R' => static::TABLE_NAME['room'][$roomType]],
						"R.{$columnRoom} = M.{$columnMessage} AND R.userid = {$userId}"
					)
					->leftJoin(['RN' => 'u_#__crmentity_label'], "RN.crmid = M.{$columnMessage}");
				break;
			case 'group':
				$query->select(['M.*', 'name' => 'RN.groupname', 'R.last_message', 'recordid' => "M.{$columnMessage}"])
					->innerJoin(
						['R' => static::TABLE_NAME['room'][$roomType]],
						"R.{$columnRoom} = M.{$columnMessage} AND R.userid = {$userId}"
					)
					->leftJoin(['RN' => 'vtiger_groups'], "RN.groupid = M.{$columnMessage}");
				break;
			case 'global' || 'private':
				$query->select(['M.*', 'name' => 'RN.name', 'R.last_message', 'recordid' => "M.{$columnMessage}"])
					->leftJoin(
						['R' => static::TABLE_NAME['room'][$roomType]],
						"R.{$columnRoom} = M.{$columnMessage} AND R.userid = {$userId}"
					)
					->leftJoin(['RN' => static::TABLE_NAME['room_name'][$roomType]], "RN.{$columnRoom} = M.{$columnMessage}");
				break;
			default:
				break;
		}
		return $query->where(['or', ['R.last_message' => null], ['<', 'R.last_message', new \yii\db\Expression('M.id')]])
			->orderBy(["M.{$columnMessage}" => \SORT_ASC, 'id' => \SORT_DESC]);
	}

	/**
	 * Get last message id.
	 *
	 * @param array $messages
	 *
	 * @return array
	 */
	private static function getLastMessageId($messages = [])
	{
		$room = [];
		foreach ($messages as $message) {
			$id = $message['id'];
			$recordId = $message['recordid'];
			if (!isset($room[$recordId]['id']) || $room[$recordId]['id'] < $id) {
				$room[$recordId] = ['id' => $id, 'last_message' => $message['last_message']];
			}
		}
		return $room;
	}

	/**
	 * Mark as read.
	 *
	 * @param string $roomType
	 * @param array  $messages
	 *
	 * @throws \yii\db\Exception
	 */
	private static function markAsRead(string $roomType, $messages = [])
	{
		$room = static::getLastMessageId($messages);
		foreach ($room as $id => $lastMessage) {
			if (empty($lastMessage['last_message'])) {
				Db::getInstance()->createCommand()->insert(static::TABLE_NAME['room'][$roomType], [
					'last_message' => $lastMessage['id'],
					static::COLUMN_NAME['room'][$roomType] => $id,
					'userid' => User::getCurrentUserId(),
				])->execute();
			} else {
				Db::getInstance()->createCommand()->update(
					static::TABLE_NAME['room'][$roomType],
					['last_message' => $lastMessage['id']],
					[static::COLUMN_NAME['room'][$roomType] => $id, 'userid' => User::getCurrentUserId()]
				)->execute();
			}
		}
	}

	/**
	 * Get unread messages.
	 *
	 * @param string $roomType
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	public static function getUnreadByType(string $roomType = 'global')
	{
		$dataReader = static::getQueryForUnread($roomType)->createCommand()->query();
		$rows = [];
		while ($row = $dataReader->read()) {
			$userModel = User::getUserModel($row['userid']);
			$image = $userModel->getImage();
			if ('global' === $roomType) {
				$row['name'] = Language::translate($row['name']);
			}
			$rows[] = [
				'id' => $row['id'],
				'userid' => $row['userid'],
				'messages' => static::decodeMessage($row['messages']),
				'created' => Fields\DateTime::formatToShort($row['created']),
				'user_name' => $userModel->getName(),
				'role_name' => Language::translate($userModel->getRoleInstance()->getName()),
				'image' => $image['url'] ?? '',
				'room_name' => $row['name'],
				'recordid' => $row['recordid'],
				'last_message' => $row['last_message'],
			];
		}
		$dataReader->close();
		static::markAsRead($roomType, $rows);
		return $rows;
	}

	/**
	 * Get all unread messages.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	public static function getUnread()
	{
		return [
			'crm' => static::getUnreadByType('crm'),
			'group' => static::getUnreadByType('group'),
			'global' => static::getUnreadByType('global'),
			'private' => static::getUnreadByType('private'),
		];
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
		$columnRoom = static::COLUMN_NAME['room'][$this->roomType];
		$allUsersQuery = (new DB\Query())
			->select(['userid'])
			->from(static::TABLE_NAME['room'][$this->roomType])
			->where([$columnRoom => $this->recordId]);
		$subQuery = (new DB\Query())
			->select(['CR.userid', 'last_id' => new \yii\db\Expression('max(id)')])
			->from(['CR' => static::TABLE_NAME['message'][$this->roomType]])
			->where([static::COLUMN_NAME['message'][$this->roomType] => $this->recordId])
			->groupBy(['CR.userid']);
		$query = (new DB\Query())
			->from(['GL' => static::TABLE_NAME['message'][$this->roomType]])
			->innerJoin(['LM' => $subQuery], 'LM.last_id = GL.id');
		$participants = [];
		$dataReader = $allUsersQuery->createCommand()->query();
		while ($row = $dataReader->read()) {
			$user = $this->getUserInfo($row['userid']);
			$participants[$row['userid']] = [
				'user_id' => $row['userid'],
				'user_name' => $user['user_name'],
				'role_name' => $user['role_name'],
				'isAdmin' => $user['isAdmin'],
				'image' => $user['image'],
			];
		}
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			if (isset($participants[$row['userid']])) {
				$participants[$row['userid']]['message'] = static::decodeNoHtmlMessage($row['messages']);
			}
		}
		$dataReader->close();
		return array_values($participants);
	}

	/**
	 * Remove room from favorites.
	 *
	 * @param ?int $userId
	 *
	 * @throws \yii\db\Exception
	 */
	public function removeFromFavorites(?int $userId = null)
	{
		if (empty($userId)) {
			$userId = $this->userId;
		}
		if (!empty($this->roomType) && !empty($this->recordId)) {
			Db::getInstance()->createCommand()->delete(
				static::TABLE_NAME['room'][$this->roomType],
				[
					'userid' => $userId,
					static::COLUMN_NAME['room'][$this->roomType] => $this->recordId
				]
			)->execute();
		}
		if ($userId === $this->userId) {
			unset($this->room['userid']);
			$currentRoom = static::getCurrentRoom();
			if ($currentRoom['recordId'] === $this->recordId && $currentRoom['roomType'] === $this->roomType) {
				static::setCurrentRoomDefault();
			}
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
			$lastMessage = static::getRoomLastMessage($this->recordId, $this->roomType);
			Db::getInstance()->createCommand()->insert(
				static::TABLE_NAME['room'][$this->roomType],
				[
					'last_message' => $lastMessage['id'] ?? 0,
					'userid' => $this->userId,
					static::COLUMN_NAME['room'][$this->roomType] => $this->recordId
				]
			)->execute();
			$this->room['userid'] = $this->userId;
		}
	}

	/**
	 * Create private room.
	 *
	 * @param string $name
	 */
	public function createPrivateRoom(string $name)
	{
		$table = static::TABLE_NAME['room_name']['private'];
		$roomIdColumn = static::COLUMN_NAME['room']['private'];
		Db::getInstance()->createCommand()->insert(
				$table,
				[
					'name' => $name,
					'creatorid' => $this->userId,
					'created' => date('Y-m-d H:i:s')
				]
			)->execute();
		Db::getInstance()->createCommand()->insert(
				static::TABLE_NAME['room']['private'],
				[
					'userid' => $this->userId,
					'last_message' => 0,
					static::COLUMN_NAME['room']['private'] => Db::getInstance()->getLastInsertID("{$table}_{$roomIdColumn}_seq")
				]
			)->execute();
	}

	/**
	 * Archive private room.
	 *
	 * @param string $name
	 * @param int    $recordId
	 */
	public function archivePrivateRoom(int $recordId)
	{
		Db::getInstance()->createCommand()
			->update(
			static::TABLE_NAME['room_name']['private'], ['archived' => 1], [static::COLUMN_NAME['room']['private'] => $recordId])
			->execute();
	}

	/**
	 * Add participant to private room.
	 *
	 * @param int $userId
	 *
	 * @return bool $alreadyInvited
	 */
	public function addParticipantToPrivate(int $userId): bool
	{
		$privateRoomsTable = static::TABLE_NAME['room']['private'];
		$privateRoomsIdColumn = static::COLUMN_NAME['room']['private'];
		$alreadyInvited = (new Db\Query())
			->select(['userid', $privateRoomsIdColumn])
			->from($privateRoomsTable)
			->where(['and', [$privateRoomsIdColumn => $this->recordId], ['userid' => $userId]])
			->exists();
		if (!$alreadyInvited) {
			Db::getInstance()->createCommand()->insert(
					$privateRoomsTable,
					[
						'userid' => $userId,
						'last_message' => null,
						$privateRoomsIdColumn => $this->recordId,
					]
				)->execute();
		}
		return $alreadyInvited;
	}

	/**
	 * Get a query for chat messages.
	 *
	 * @param int|null $messageId
	 * @param string   $condition
	 * @param bool     $isLimit
	 * @param ?string  $searchVal
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
			case 'private':
				$query = (new Db\Query())
					->select(['C.*', 'U.user_name', 'U.last_name'])
					->from(['C' => 'u_#__chat_messages_private'])
					->leftJoin(['U' => 'vtiger_users'], 'U.id = C.userid')
					->where(['privateid' => $this->recordId]);
				break;
			default:
				throw new Exceptions\IllegalValue("ERR_NOT_ALLOWED_VALUE||$this->roomType", 406);
		}
		if (null !== $messageId) {
			$query->andWhere([$condition, 'C.id', $messageId]);
		}
		if (!empty($searchVal)) {
			$query->andWhere(['LIKE', 'C.messages', $searchVal]);
		}
		if ($isLimit) {
			$query->limit(\App\Config::module('Chat', 'CHAT_ROWS_LIMIT') + 1);
		}
		return $query->orderBy(['id' => \SORT_DESC]);
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
			case 'private':
				return (new Db\Query())
					->select(['CG.*', 'CR.userid', 'record_id' => 'CR.private_room_id', 'CR.last_message'])
					->from(['CG' => 'u_#__chat_private'])
					->leftJoin(['CR' => 'u_#__chat_rooms_private'], "CR.private_room_id = CG.private_room_id AND CR.userid = {$this->userId}")
					->where(['CG.private_room_id' => $this->recordId]);
			default:
				throw new Exceptions\IllegalValue("ERR_NOT_ALLOWED_VALUE||$this->roomType", 406);
				break;
		}
	}

	/**
	 * Update last message ID.
	 *
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \yii\db\Exception
	 */
	private function updateRoom()
	{
		if ('global' === $this->roomType && !$this->isAssigned()) {
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
			\is_array($this->room) && $this->isAssigned() && (empty($this->room['last_message']) || $this->lastMessageId > (int) $this->room['last_message'])
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

	/**
	 * Decode message.
	 *
	 * @param string $message
	 *
	 * @return string
	 */
	private static function decodeMessage(string $message): string
	{
		return nl2br(\App\Utils\Completions::decode(\App\Purifier::purifyHtml(\App\Purifier::decodeHtml($message))));
	}

	/**
	 * Decode message without html except completions.
	 *
	 * @param string $message
	 *
	 * @return string
	 */
	private static function decodeNoHtmlMessage(string $message): string
	{
		return static::decodeMessage(strip_tags(\App\Purifier::decodeHtml($message)));
	}

	/**
	 * Get chat modules.
	 *
	 * @return array
	 */
	public static function getChatModules(): array
	{
		$activeModules = [];
		$userPrivilegesModel = \Users_Privileges_Model::getCurrentUserPrivilegesModel();
		foreach (array_keys(ModuleHierarchy::getModulesHierarchy()) as $moduleName) {
			if ($userPrivilegesModel->hasModulePermission($moduleName)) {
				$activeModules[] = [
					'id' => $moduleName,
					'label' => Language::translate($moduleName, $moduleName)
				];
			}
		}
		return $activeModules;
	}
}
