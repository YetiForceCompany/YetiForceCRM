<?php
namespace Yeti;
use Sabre\CardDAV;

/**
 * PDO CardDAV backend
 *
 * This CardDAV backend uses PDO to store addressbooks
 *
 * @copyright Copyright (C) 2007-2015 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class CardDAV_Backend_PDO extends CardDAV\Backend\PDO{
    /**
     * Sets up the object
     *
     * @param \PDO $pdo
     * @param string $addressBooksTableName
     * @param string $cardsTableName
     * @deprecated We are going to remove all the tableName arguments in a
     *             future version, and rely on the public properties instead.
     *             Stop relying on them!
     */
    function __construct(\PDO $pdo, $addressBooksTableName = 'dav_addressbooks', $cardsTableName = 'dav_cards', $addressBookChangesTableName = 'dav_addressbookchanges') {
        $this->pdo = $pdo;
        $this->addressBooksTableName = $addressBooksTableName;
        $this->cardsTableName = $cardsTableName;
        $this->addressBookChangesTableName = $addressBookChangesTableName;
    }
}
