<?php
/**
 * Address book model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
		$query = (new \App\Db\Query())->select([self::TABLE . '.name', self::TABLE . '.email', self::TABLE . '.users', 'vtiger_crmentity.setype'])->from(self::TABLE)->innerJoin('vtiger_crmentity', self::TABLE . '.id = vtiger_crmentity.crmid');
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$name = $row['name'];
			$email = $row['email'];
			$users = $row['users'];
			$setype = $row['setype'];
			if (!empty($users)) {
				$users = explode(',', ltrim($users, ','));
				foreach ($users as &$user) {
					$mails[$user][$setype][] = "$name <$email>";
				}
			}
		}
		foreach ($mails as $userId => $mail) {
			$mailsToFile[$userId] = array_merge($mail['OSSEmployees'] ?? [], $mail['Contacts'] ?? []);
			unset($mail['OSSEmployees'], $mail['Contacts']);
			foreach ($mail as $otherMail) {
				$mailsToFile[$userId] = array_merge($mailsToFile[$userId], $otherMail);
			}
		}
		$dataReader->close();
		$fstart = '<?php $bookMails =';
		if (!empty($mailsToFile)) {
			foreach ($mailsToFile as $user => $file) {
				$file = array_unique($file);
				file_put_contents("cache/addressBook/mails_{$user}.php", $fstart . App\Utils::varExport($file) . ';');
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
		file_put_contents(self::LAST_RECORD_CACHE, "<?php return ['module' => '$module','record' => $record];");
	}

	/**
	 * Clear last record.
	 */
	public static function clearLastRecord()
	{
		unlink(self::LAST_RECORD_CACHE);
	}
}
