<?php

/**
 * Chat module model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Chat_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Get chat entries.
	 *
	 * @param int|bool $time
	 *
	 * @return array
	 */
	public function getEntries($id = false)
	{
		$query = (new \App\Db\Query())->from('u_#__chat_messages')->limit(AppConfig::module('Chat', 'ROWS_LIMIT'));
		if ($id) {
			$query->where(['>', 'id', $id]);
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

	/**
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
				'messages' => $message,
			])->execute();
	}
}
