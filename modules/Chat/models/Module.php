<?php

/**
 * Chat module model class
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Chat_Module_Model extends Vtiger_Module_Model
{

	/**
	 * Get chat entries
	 * @param int|bool $time
	 * @return array
	 */
	public function getEntries($time = false)
	{

		$query = (new \App\Db\Query())->select(['userid', 'created', 'user_name', 'messages'])->from('u_#__chat_messages');
		if ($time) {
			$query->where(['>', 'created', $time]);
		} else {
			$query->limit(AppConfig::module('Chat', 'ROWS_LIMIT'));
		}
		$rows = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$row['created'] = date('Y-m-d H:i:s', $row['created']);
			$row['time'] = Vtiger_Util_Helper::formatDateDiffInStrings($row['created']);
			$rows[] = $row;
		}
		return $rows;
	}

	/**
	 * 
	 * @param string $message
	 */
	public static function add($message)
	{
		$currentUser = \App\User::getCurrentUserModel();
		\App\Db::getInstance()->createCommand()
			->insert('u_#__chat_messages', [
				'userid' => $currentUser->getId(),
				'created' => strtotime('now'),
				'user_name' => $currentUser->getName(),
				'messages' => $message
			])->execute();
	}
}
