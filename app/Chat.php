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
		$queryGlobal = (new \App\Db\Query())
			->select([
				'room_type' => new \yii\db\Expression("'global'"),
				'GL.global_room_id',
				'userid' => new \yii\db\Expression('NULL'),
				'id' => new \yii\db\Expression('NULL'),
				'GL.name'
			])->from(['GL' => 'u_#__chat_rooms_global']);
		$queryGroup = (new \App\Db\Query())
			->select([
				'room_type' => new \yii\db\Expression("'group'"),
				'GR.roomid', 'GR.userid',
				'id' => new \yii\db\Expression('GR.groupid'),
				'VGR.groupname'
			])->from(['GR' => 'u_#__chat_rooms_group'])
				->innerJoin(['VGR' => 'vtiger_groups'], 'VGR.groupid = GR.groupid')
				->where(['GR.userid' => $userId]);
		$dataReader = (new \App\Db\Query())
			->select([
				'room_type' => new \yii\db\Expression("'crm'"),
				'C.roomid', 'C.userid',
				'id' => new \yii\db\Expression('C.crmid'),
				'name' => new \yii\db\Expression('NULL')
			])->from(['C' => 'u_#__chat_rooms_crm'])
				->where(['C.userid' => $userId])
				->union($queryGroup, true)
				->union($queryGlobal, true)
				->createCommand()->query();
		$arr = [
			'crm' => [],
			'group' => [],
			'global' => [],
		];
		while ($row = $dataReader->read()) {
			$arr[$row['room_type']][] = $row;
		}
		$dataReader->close();
		return $arr;
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
