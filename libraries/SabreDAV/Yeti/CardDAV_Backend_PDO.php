<?php namespace Yeti;

use Sabre\CardDAV;

/**
 * PDO CardDAV backend
 *
 * This CardDAV backend uses PDO to store addressbooks
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class CardDAV_Backend_PDO extends CardDAV\Backend\PDO
{

	/**
	 * The PDO table name used to store addressbooks
	 */
	public $addressBooksTableName = 'dav_addressbooks';

	/**
	 * The PDO table name used to store cards
	 */
	public $cardsTableName = 'dav_cards';

	/**
	 * The table name that will be used for tracking changes in address books.
	 *
	 * @var string
	 */
	public $addressBookChangesTableName = 'dav_addressbookchanges';

}
