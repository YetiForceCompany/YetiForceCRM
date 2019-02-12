<?php

namespace App\Dav;

use Sabre\CalDAV;

/**
 * PDO CalDAV backend.
 *
 * This backend is used to store calendar-data in a PDO database, such as
 * sqlite or MySQL
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author    Evert Pot (http://evertpot.com/)
 * @license   http://sabre.io/license/ Modified BSD License
 */
class CalDavBackendPdo extends CalDAV\Backend\PDO
{
	/**
	 * The table name that will be used for calendars.
	 *
	 * @var string
	 */
	public $calendarTableName = 'dav_calendars';

	/**
	 * The table name that will be used for calendars instances.
	 *
	 * A single calendar can have multiple instances, if the calendar is
	 * shared.
	 *
	 * @var string
	 */
	public $calendarInstancesTableName = 'dav_calendarinstances';
	/**
	 * The table name that will be used for calendar objects.
	 *
	 * @var string
	 */
	public $calendarObjectTableName = 'dav_calendarobjects';

	/**
	 * The table name that will be used for tracking changes in calendars.
	 *
	 * @var string
	 */
	public $calendarChangesTableName = 'dav_calendarchanges';

	/**
	 * The table name that will be used inbox items.
	 *
	 * @var string
	 */
	public $schedulingObjectTableName = 'dav_schedulingobjects';

	/**
	 * The table name that will be used for calendar subscriptions.
	 *
	 * @var string
	 */
	public $calendarSubscriptionsTableName = 'dav_calendarsubscriptions';

	/**
	 * Deletes an existing calendar object.
	 *
	 * The object uri is only the basename, or filename and not a full path.
	 *
	 * @param mixed  $calendarId
	 * @param string $objectUri
	 */
	public function deleteCalendarObject($calendarId, $objectUri)
	{
		list($calendarId) = $calendarId;

		$stmt = $this->pdo->prepare(sprintf('UPDATE vtiger_crmentity SET deleted = ? WHERE crmid IN (SELECT crmid FROM %s WHERE calendarid = ? && uri = ?);', $this->calendarObjectTableName));
		$stmt->execute([1, $calendarId, $objectUri]);

		$stmt = $this->pdo->prepare(sprintf('DELETE FROM %s WHERE calendarid = ? && uri = ?', $this->calendarObjectTableName));
		$stmt->execute([$calendarId, $objectUri]);
		$this->addChange($calendarId, $objectUri, 3);
	}
}
