<?php
namespace Yeti;
use Sabre\CalDAV;

/**
 * PDO CalDAV backend
 *
 * This backend is used to store calendar-data in a PDO database, such as
 * sqlite or MySQL
 *
 * @copyright Copyright (C) 2007-2015 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class CalDAV_Backend_PDO extends CalDAV\Backend\PDO {
    /**
     * Creates the backend
     *
     * @param \PDO $pdo
     * @param string $calendarTableName
     * @param string $calendarObjectTableName
     * @param string $calendarChangesTable
     * @param string $schedulingObjectTable
     * @param string $calendarSubscriptionsTableName
     * @deprecated We are going to remove all the 'tableName' arguments and
     *             move to public properties for those. Stop relying on them!
     */
    function __construct(\PDO $pdo, $calendarTableName = 'dav_calendars', $calendarObjectTableName = 'dav_calendarobjects', $calendarChangesTableName = 'dav_calendarchanges', $calendarSubscriptionsTableName = "dav_calendarsubscriptions", $schedulingObjectTableName = "dav_schedulingobjects") {
        $this->pdo = $pdo;
        $this->calendarTableName = $calendarTableName;
        $this->calendarObjectTableName = $calendarObjectTableName;
        $this->calendarChangesTableName = $calendarChangesTableName;
        $this->schedulingObjectTableName = $schedulingObjectTableName;
        $this->calendarSubscriptionsTableName = $calendarSubscriptionsTableName;
    }
}
