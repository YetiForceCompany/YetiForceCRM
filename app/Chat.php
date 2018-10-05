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
	 * Get instance by record id.
	 *
	 * @param int $id
	 *
	 * @return \App\Chat
	 */
	public static function getInstanceById(int $id)
	{
		$instance = new self();
		$instance->recordId = $id;
		$chatRoomRow = (new \App\Db\Query())
			->from('u_#__chat_rooms')
			->where(['crmid' => $instance->recordId])
			->one();
		if ($chatRoomRow) {
			$instance->chatRoomId = $chatRoomRow['room_id'];
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
	 * Check if chat room exists.
	 *
	 * @return bool
	 */
	public function isCharRoomExists()
	{
		return $this->chatRoomId !== false;
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
					'crmid' => $this->recordId
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
}
