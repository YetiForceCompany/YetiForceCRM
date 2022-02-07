<?php
/**
 * SabreDav PDO CalDAV backend file.
 * This backend is used to store calendar-data in a PDO database, such as
 * sqlite or MySQL.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Dav\Backend;

use Sabre\CalDAV;

/**
 * SabreDav PDO CalDAV backend class.
 */
class Calendar extends CalDAV\Backend\PDO
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
		if (!\is_array($calendarId)) {
			throw new \InvalidArgumentException('The value passed to $calendarId is expected to be an array with a calendarId and an instanceId');
		}
		[$calendarId] = $calendarId;

		$stmt = $this->pdo->prepare(sprintf('UPDATE vtiger_crmentity SET deleted = ? WHERE crmid IN (SELECT crmid FROM %s WHERE calendarid = ? && uri = ?);', $this->calendarObjectTableName));
		$stmt->execute([1, $calendarId, $objectUri]);

		$stmt = $this->pdo->prepare(sprintf('DELETE FROM %s WHERE calendarid = ? && uri = ?', $this->calendarObjectTableName));
		$stmt->execute([$calendarId, $objectUri]);
		$this->addChange($calendarId, $objectUri, 3);
	}
}
