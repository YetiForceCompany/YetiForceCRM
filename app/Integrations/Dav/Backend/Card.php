<?php
/**
 * SabreDav PDO CardDAV backend file.
 * This CardDAV backend uses PDO to store addressbooks.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Dav\Backend;

use Sabre\CardDAV;

/**
 * SabreDav PDO CardDAV backend class.
 */
class Card extends CardDAV\Backend\PDO
{
	/**
	 * The PDO table name used to store addressbooks.
	 *
	 * @var string
	 */
	public $addressBooksTableName = 'dav_addressbooks';

	/**
	 * The PDO table name used to store cards.
	 *
	 * @var string
	 */
	public $cardsTableName = 'dav_cards';

	/**
	 * The table name that will be used for tracking changes in address books.
	 *
	 * @var string
	 */
	public $addressBookChangesTableName = 'dav_addressbookchanges';

	/**
	 * Deletes a card.
	 *
	 * @param mixed  $addressBookId
	 * @param string $cardUri
	 *
	 * @return bool
	 */
	public function deleteCard($addressBookId, $cardUri)
	{
		$stmt = $this->pdo->prepare(sprintf('UPDATE vtiger_crmentity SET deleted = ? WHERE crmid IN (SELECT crmid FROM %s WHERE addressbookid = ? && uri = ?);', $this->cardsTableName));
		$stmt->execute([1, $addressBookId, $cardUri]);

		$stmt = $this->pdo->prepare(sprintf('DELETE FROM %s WHERE addressbookid = ? && uri = ?', $this->cardsTableName));
		$stmt->execute([$addressBookId, $cardUri]);

		$this->addChange($addressBookId, $cardUri, 3);

		return 1 === $stmt->rowCount();
	}
}
