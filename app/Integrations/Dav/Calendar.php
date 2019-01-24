<?php
/**
 * CalDav calendar class file.
 *
 * @package   Integrations
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Dav;

/**
 * Calendar class.
 */
class Calendar
{
	/**
	 * Delete calendar event by crm id.
	 *
	 * @param int $id
	 *
	 * @throws \yii\db\Exception
	 */
	public static function deleteByCrmId(int $id)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$dataReader = (new \App\Db\Query())->select(['id'])->from('dav_calendarobjects')->where(['crmid' => $id])->createCommand()->query();
		$dbCommand->delete('dav_calendarobjects', ['crmid' => $id])->execute();
		while ($calendarId = $dataReader->readColumn(0)) {
			static::addChange($calendarId, $id . '.vcf', 3);
		}
		$dataReader->close();
	}

	/**
	 * Add change to calendar.
	 *
	 * @param int    $calendarId
	 * @param string $uri
	 * @param int    $operation
	 *
	 * @throws \yii\db\Exception
	 */
	public static function addChange(int $calendarId, string $uri, int $operation)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$calendar = static::getCalendar($calendarId);
		$dbCommand->insert('dav_calendarchanges', [
			'uri' => $uri,
			'synctoken' => (int) $calendar['synctoken'],
			'calendarid' => $calendarId,
			'operation' => $operation
		])->execute();
		$dbCommand->update('dav_calendars', [
			'synctoken' => ((int) $calendar['synctoken']) + 1
		], ['id' => $calendarId])
		->execute();
	}

	/**
	 * Get calendar.
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	public static function getCalendar(int $id)
	{
		return (new \App\Db\Query())->from('dav_calendars')->where(['id' => $id])->one();
	}
}
