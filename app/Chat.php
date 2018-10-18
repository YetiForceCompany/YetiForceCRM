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
	 * @var []
	 */
	private $room;

	/**
	 * Set current room ID, type.
	 *
	 * @param string $roomType
	 * @param int    $roomId
	 */
	public static function setCurrentRoom(string $roomType, int $roomId)
	{
		$_SESSION['CHAT']['CURRENT-ROOM'] = ['roomType' => $roomType, 'roomId' => $roomId];
	}

	/**
	 * Get current room ID, type.
	 *
	 * @return []|false
	 */
	public static function getCurrentRoom()
	{
		if (!isset($_SESSION['CHAT']['CURRENT-ROOM'])) {
			return false;
		}
		return $_SESSION['CHAT']['CURRENT-ROOM'];
	}

	/**
	 * Get instance by record model.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return \App\Chat
	 */
	public static function getInstanceByRecordModel(\Vtiger_Record_Model $recordModel): \App\Chat
	{
		$instance = new self();
		return $instance;
	}

	/**
	 * Get instance \App\Chat.
	 *
	 * @param string $roomType
	 * @param int    $roomId
	 *
	 * @return \App\Chat
	 */
	public static function getInstance(string $roomType, int $roomId): \App\Chat
	{
		$instance = new self($roomType, $roomId);
		return $instance;
	}

	/**
	 * Global list of chat rooms.
	 *
	 * @return array
	 */
	public static function getRoomsGlobal()
	{
		return (new Db\Query())
			->select(['roomid' => 'global_room_id', 'name'])
			->from('u_#__chat_rooms_global')
			->all();
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
		return (new Db\Query())
			->select(['C.roomid', 'C.userid', 'recordid' => 'C.crmid', 'name' => 'CL.label'])
			->from(['C' => 'u_#__chat_rooms_crm'])
			->leftJoin(['CL' => 'u_yf_crmentity_label'], 'CL.crmid = C.crmid')
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
	 * @param string $roomType
	 * @param int    $roomId
	 */
	public function __construct(string $roomType, int $roomId)
	{
		$this->roomType = $roomType;
		$this->roomId = $roomId;
		$this->room = $this->getQueryRoom()->one();
		if ($this->isRoomExists() && isset($this->room['record_id'])) {
			$this->recordId = $this->room['record_id'];
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
	 * Get entries function.
	 *
	 * @param null|int $messageId
	 *
	 * @return array
	 */
	public function getEntries(?int $messageId = null)
	{
		$query = $this->getQueryMessage();
		if (!\is_null($messageId)) {
			$query->andWhere(['>', 'id', $messageId]);
		}
		$rows = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$row['image'] = \Vtiger_Record_Model::getInstanceById($row['userid'], 'Users')->getImage();
			$row['created'] = date('Y-m-d H:i:s', $row['created']);
			$row['time'] = Fields\DateTime::formatToViewDate($row['created']);
			$rows[] = $row;
		}
		$dataReader->close();
		return $rows;
	}

	/**
	 * Get a query for chat messages.
	 *
	 * @return \App\Db\Query
	 */
	private function getQueryMessage(): Db\Query
	{
		switch ($this->roomType) {
			case 'crm':
				return (new Db\Query())
					->select(['C.*', 'U.user_name', 'U.last_name'])
					->from(['C' => 'u_#__chat_messages_crm'])
					->leftJoin(['U' => 'vtiger_users'], 'U.id = C.userid')
					->limit(\AppConfig::module('Chat', 'ROWS_LIMIT'))
					->where(['crmid' => $this->recordId])
					->orderBy(['created' => \SORT_DESC]);
			case 'group':
				return (new Db\Query())
					->select(['C.*', 'U.user_name', 'U.last_name'])
					->from(['C' => 'u_#__chat_messages_group'])
					->leftJoin(['U' => 'vtiger_users'], 'U.id = C.userid')
					->limit(\AppConfig::module('Chat', 'ROWS_LIMIT'))
					->where(['groupid' => $this->recordId])
					->orderBy(['created' => \SORT_DESC]);
			case 'global':
				return (new Db\Query())
					->select(['C.*', 'U.user_name', 'U.last_name'])
					->from(['C' => 'u_#__chat_messages_global'])
					->leftJoin(['U' => 'vtiger_users'], 'U.id = C.userid')
					->limit(\AppConfig::module('Chat', 'ROWS_LIMIT'))
					->where(['globalid' => $this->roomId])
					->orderBy(['created' => \SORT_DESC]);
		}
		throw new Exceptions\IllegalValue("ERR_NOT_ALLOWED_VALUE||$this->roomType", 406);
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
					->select(['CR.roomid', 'CR.userid', 'record_id' => 'CR.crmid'])
					->from(['CR' => 'u_#__chat_rooms_crm'])
					->where(['CR.roomid' => $this->roomId]);
			case 'group':
				return (new Db\Query())
					->select(['CR.roomid', 'CR.userid', 'record_id' => 'CR.groupid'])
					->from(['CR' => 'u_#__chat_rooms_group'])
					->where(['CR.roomid' => $this->roomId]);
			case 'global':
				return (new Db\Query())
					->from(['CR' => 'u_#__chat_rooms_global'])
					->where(['CR.global_room_id' => $this->roomId]);
		}
		throw new Exceptions\IllegalValue("ERR_NOT_ALLOWED_VALUE||$this->roomType", 406);
	}
}
