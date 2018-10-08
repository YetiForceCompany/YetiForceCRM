<?php
/**
 * Chat.
 */

namespace App;

/**
 * Class Chat.
 */
class Chat
{
	/**
	 * @var int
	 */
	protected $recordId;
	/**
	 * @var string
	 */
	protected $nameOfRoom;

	/**
	 * @var int
	 */
	protected $chatRoomId = false;

	/**
	 * Chat constructor.
	 */
	public function __construct()
	{
	}

	/**
	 * Set current room id.
	 *
	 * @param int $id
	 */
	public static function setCurrentRoomId($id)
	{
		\App\Session::set('chat-current-room-id', $id);
	}

	/**
	 * Get current room id.
	 *
	 * @return int
	 */
	public static function getCurrentRoomId()
	{
		if (!\App\Session::has('chat-current-room-id')) {
			return (int) 0;
		}
		return \App\Session::get('chat-current-room-id');
	}

	/**
	 * Get instance by record id.
	 *
	 * @param int $id
	 *
	 * @return \App\Chat
	 */
	public static function getInstanceById(int $id)
	{
		$instance = new self();
		$chatRoomRow = (new \App\Db\Query())
			->from('u_#__chat_rooms')
			->where(['room_id' => $id])
			->one();
		if ($chatRoomRow) {
			$instance->chatRoomId = $chatRoomRow['room_id'];
			$instance->recordId = $id;
		} elseif ($id !== 0) {
			$instance->nameOfRoom = \Vtiger_Record_Model::getInstanceById($id)->getDisplayName();
		}
		return $instance;
	}

	/**
	 * Get instance by name of room.
	 *
	 * @param string $nameOfRoom
	 *
	 * @return \App\Chat
	 */
	public static function getInstanceByName(string $nameOfRoom)
	{
		$instance = new self();
		$instance->nameOfRoom = $nameOfRoom;
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
	 * Check if chat room exists.
	 *
	 * @return bool
	 */
	public function isCharRoomExists()
	{
		return $this->chatRoomId !== false;
	}

	/**
	 * Return chat room id.
	 *
	 * @return int
	 */
	public function getChatRoomId()
	{
		return $this->chatRoomId;
	}

	public function save()
	{
		$id = false;
		if ($this->isCharRoomExists()) {
		} else {
			\App\db::getInstance()
				->createCommand()
				->insert('u_#__chat_rooms', [
					'name' => $this->nameOfRoom,
					'room_id' => $this->recordId
				])->execute();
			$id = \App\db::getInstance()->getLastInsertID('u_#__chat_rooms_room_id_seq');
		}
		return $id;
	}

	public function getRecords()
	{
		/*$dataReader = (new \App\Db\Query())
			->from('u_#__chat_rooms')
			->where(['crmid' => $instance->recordId])
			->createCommand()
			->query();*/
		//AppConfig::module('Chat', 'ROWS_LIMIT')
	}

	/**
	 * Get entries function.
	 *
	 * @param bool|int $id
	 *
	 * @return array
	 */
	public function getEntries($id = false)
	{
		$query = (new \App\Db\Query())
			->from('u_#__chat_messages')
			->limit(\AppConfig::module('Chat', 'ROWS_LIMIT'));
		if ($id) {
			$query->where(['>', 'id', $id]);
		}
		if ($this->chatRoomId !== false) {
			$query->where(['room_id' => $this->chatRoomId]);
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
