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
final class Chat
{
	/**
	 * Information about allowed types of rooms.
	 */
	const ALLOWED_ROOM_TYPES = ['crm', 'group', 'global'];

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
	 * ID record associated with the chat room.
	 *
	 * @var int|null
	 */
	private $recordId;

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
		} elseif ($roomType === 'crm' && (!Record::isExists($recordId) || !\Vtiger_Record_Model::getInstanceById($recordId)->isViewable())) {
			$result = static::getDefaultRoom();
		} elseif ($roomType === 'group' && !isset(User::getCurrentUserModel()->getGroupNames()[$recordId])) {
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
			->select(['name', 'recordid' => 'GL.global_room_id', 'CNT.cnt_new_message'])
			->from(['GL' => 'u_#__chat_global'])
			->leftJoin(['CNT' => $subQuery], "CNT.{$roomIdName} = GL.global_room_id AND CNT.userid = {$userId}")
			->createCommand()->query();
		$rooms = [];
		while ($row = $dataReader->read()) {
			$row['name'] = Language::translate($row['name'], 'Chat');
			$rooms[] = $row;
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
			->orWhere(['CR.last_message' => null])
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
				$rows[] = $row;
			}
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
				$rows[] = $row;
			}
		}
		$dataReader->close();
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
		];
		Cache::staticSave('ChatGetRoomsByUser', $userId);
		return $roomsByUser;
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
			$userRoleName = Language::translate($userModel->getRoleInstance()->getName());
		} else {
			$image = $userName = $userRoleName = null;
		}
		return [
			'user_name' => $userName,
			'role_name' => $userRoleName,
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
		if (($this->roomType === 'crm' || $this->roomType === 'group') && !$this->isRoomExists()) {
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
			$row['created'] = Fields\DateTime::formatToShort($row['created']);
			['user_name' => $row['user_name'],
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
		if ($condition === '>') {
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
		$query = (new Db\Query())
			->select([
				'id', 'messages', 'userid', 'created',
				'recordid' => static::COLUMN_NAME['message'][$roomType],
			])
			->from(['GL' => static::TABLE_NAME['message'][$roomType]])
			->where(['userid' => $this->userId])
			->orderBy(['id' => \SORT_DESC])
			->limit(\AppConfig::module('Chat', 'CHAT_ROWS_LIMIT') + 1);
		if (!\is_null($messageId)) {
			$query->andWhere(['<=', 'id', $messageId]);
		}
		$userModel = User::getUserModel($this->userId);
		$userImage = $userModel->getImage()['url'] ?? '';
		$userName = $userModel->getName();
		$userRoleName = $userModel->getRoleInstance()->getName();
		$rows = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$row['image'] = $userImage;
			$row['created'] = Fields\DateTime::formatToShort($row['created']);
			$row['user_name'] = $userName;
			$row['role_name'] = $userRoleName;
			$rows[] = $row;
		}
		return \array_reverse($rows);
	}

	/**
	 * Get default room.
	 *
	 * @return array|false
	 */
	private static function getDefaultRoom()
	{
		if (Cache::has('Chat', 'DefaultRoom')) {
			return Cache::get('Chat', 'DefaultRoom');
		}
		$room = false;
		$row = (new Db\Query())->from('u_#__chat_global')->where(['name' => 'LBL_GENERAL'])->one();
		if ($row !== false) {
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
	private static function getQueryForUnread(string $roomType = 'global'): \App\Db\Query
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
			case 'global':
				$query->select(['M.*', 'name' => 'RN.name', 'R.last_message', 'recordid' => "M.{$columnMessage}"])
					->leftJoin(
						['R' => static::TABLE_NAME['room'][$roomType]],
						"R.{$columnRoom} = M.{$columnMessage} AND R.userid = {$userId}"
					)
					->leftJoin(['RN' => 'u_#__chat_global'], "RN.global_room_id = M.{$columnMessage}");
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
					static::TABLE_NAME['room'][$roomType], ['last_message' => $lastMessage['id']],
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
			if ($roomType === 'global') {
				$row['name'] = Language::translate($row['name']);
			}
			$rows[] = [
				'id' => $row['id'],
				'userid' => $row['userid'],
				'messages' => $row['messages'],
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
			$user = $this->getUserInfo($row['userid']);
			$participants[] = [
				'user_id' => $row['userid'],
				'message' => $row['messages'],
				'user_name' => $user['user_name'],
				'role_name' => $user['role_name'],
				'image' => $user['image']
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
			unset($this->room['userid']);
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
			$this->room['userid'] = $this->userId;
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
			$query->limit(\AppConfig::module('Chat', 'CHAT_ROWS_LIMIT') + 1);
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
