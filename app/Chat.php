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
	 * Set current room ID, type.
	 *
	 * @param string $roomType
	 * @param int    $roomId
	 */
	public static function setCurrentRoom(string $roomType, int $roomId)
	{
		\App\Session::set('chat-current-room', ['roomType' => $roomType, 'roomId' => $roomId]);
	}

	/**
	 * Get current room ID, type.
	 *
	 * @return []|false
	 */
	public static function getCurrentRoom()
	{
		if (!\App\Session::has('chat-current-room')) {
			return false;
		}
		return \App\Session::get('chat-current-room');
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
		return (new \App\Db\Query())
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
			$userId = \App\User::getCurrentUserId();
		}
		return (new \App\Db\Query())
			->select(['GR.roomid', 'GR.userid', 'id' => 'GR.groupid', 'name' => 'VGR.groupname'])
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
			$userId = \App\User::getCurrentUserId();
		}
		return (new \App\Db\Query())
			->select(['C.roomid', 'C.userid', 'id' => 'C.crmid', 'name' => 'CL.label'])
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
	}

	/**
	 * Check if chat room exists.
	 *
	 * @return bool
	 */
	public function isRoomExists(): bool
	{
		return true;
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
		return [];
	}
}
