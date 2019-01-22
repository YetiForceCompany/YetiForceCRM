<?php
/**
 * CardDav address books class file.
 *
 * @package   Integrations
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Dav;

/**
 * CardDav class.
 */
class Card
{
	/**
	 * Delete card by crm id.
	 *
	 * @param int $id
	 *
	 * @throws \yii\db\Exception
	 */
	public static function deleteByCrmId(int $id)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$dataReader = (new \App\Db\Query())->select(['addressbookid'])->from('dav_cards')->where(['crmid' => $id])->createCommand()->query();
		$dbCommand->delete('dav_cards', ['crmid' => $id])->execute();
		while ($addressBookId = $dataReader->readColumn(0)) {
			static::addChange($addressBookId, $id . '.vcf', 3);
		}
		$dataReader->close();
	}

	/**
	 * Add change to address books .
	 *
	 * @param int    $addressBookId
	 * @param string $uri
	 * @param int    $operation
	 *
	 * @throws \yii\db\Exception
	 */
	public static function addChange(int $addressBookId, string $uri, int $operation)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$addressBook = static::getAddressBook($addressBookId);
		$dbCommand->insert('dav_addressbookchanges', [
			'uri' => $uri,
			'synctoken' => (int) $addressBook['synctoken'],
			'addressbookid' => $addressBookId,
			'operation' => $operation
		])->execute();
		$dbCommand->update('dav_addressbooks', [
			'synctoken' => ((int) $addressBook['synctoken']) + 1
		], ['id' => $addressBookId])
		->execute();
	}

	/**
	 * Get address books.
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	public static function getAddressBook(int $id)
	{
		return (new \App\Db\Query())->from('dav_addressbooks')->where(['id' => $id])->one();
	}
}
