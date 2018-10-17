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
		return [
			'favorite' => [
				['name' => 'f.room1'],
				['name' => 'f.room2'],
				['name' => 'f.room3'],
			],
			'group' => [
				['name' => 'g.room1'],
				['name' => 'g.room2'],
				['name' => 'g.room3'],
			],
			'global' => [
				['name' => 'gl.room1'],
				['name' => 'gl.room2'],
				['name' => 'gl.room3'],
			],
		];
	}

	/**
	 * Check if chat room exists.
	 *
	 * @return bool
	 */
	public function isRoomExists(): bool
	{
		return false;
	}
}
