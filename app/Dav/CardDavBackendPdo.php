<?php

namespace App\Dav;

use Sabre\CardDAV;

/**
 * PDO CardDAV backend.
 *
 * This CardDAV backend uses PDO to store addressbooks
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author    Evert Pot (http://evertpot.com/)
 * @license   http://sabre.io/license/ Modified BSD License
 */
class CardDavBackendPdo extends CardDAV\Backend\PDO
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

		return $stmt->rowCount() === 1;
	}
}
