<?php
/**
 * Address book model class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Address book model class.
 */
class OSSMail_AddressBook_Model
{
	/**
	 * Table.
	 *
	 * @var string
	 */
	const TABLE = 'u_#__mail_address_book';

	/**
	 * Last record cache.
	 *
	 * @var string
	 */
	const LAST_RECORD_CACHE = 'cache/addressBook.php';

	/**
	 * Create address book file.
	 */
	public static function createABFile()
	{
		$mails = [];
		$db = \App\Db::getInstance();
		$usersIds = \App\Fields\Owner::getUsersIds();
		foreach ($usersIds as $userId) {
			$mails = (new \App\Db\Query())->select([new \yii\db\Expression('CONCAT(name,' . $db->quoteValue(' <') . ',email,' . $db->quoteValue('>') . ')'), 'vtiger_crmentity.setype'])
				->from(self::TABLE)->innerJoin('vtiger_crmentity', self::TABLE . '.id = vtiger_crmentity.crmid')
				->where(['or', ['like', self::TABLE . '.users', ",{$userId},"], ['like', self::TABLE . '.users', "%,{$userId}", false]])
				->orderBy(new \yii\db\Expression("CASE WHEN setype = 'OSSEmployees' THEN 1 WHEN setype = 'Contacts' THEN 2 ELSE 3 END"))->distinct()->column();
			if ($mails || file_exists("cache/addressBook/mails_{$userId}.php")) {
				\App\Utils::saveToFile("cache/addressBook/mails_{$userId}.php", '$bookMails =' . App\Utils::varExport($mails) . ';');
			}
		}
	}

	/**
	 * Get last record cache.
	 *
	 * @return int|bool
	 */
	public static function getLastRecord()
	{
		if (file_exists(self::LAST_RECORD_CACHE)) {
			return require self::LAST_RECORD_CACHE;
		}
		return false;
	}

	/**
	 * Save last record.
	 *
	 * @param int    $record
	 * @param string $module
	 */
	public static function saveLastRecord($record, $module)
	{
		\App\Utils::saveToFile(self::LAST_RECORD_CACHE, ['module' => $module, 'record' => $record], '', 0, true);
	}

	/**
	 * Clear last record.
	 */
	public static function clearLastRecord()
	{
		unlink(self::LAST_RECORD_CACHE);
	}
}
