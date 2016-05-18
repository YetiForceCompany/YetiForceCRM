<?php

namespace Sabre\CalDAV\Backend;

use Sabre\CalDAV\Xml\Notification\NotificationInterface;

/**
 * Adds caldav notification support to a backend.
 *
 * Note: This feature is experimental, and may change in between different
 * SabreDAV versions.
 *
 * Notifications are defined at:
 * http://svn.calendarserver.org/repository/calendarserver/CalendarServer/trunk/doc/Extensions/caldav-notifications.txt
 *
 * These notifications are basically a list of server-generated notifications
 * displayed to the user. Users can dismiss notifications by deleting them.
 *
 * The primary usecase is to allow for calendar-sharing.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
interface NotificationSupport extends BackendInterface {

    /**
     * Returns a list of notifications for a given principal url.
     *
     * @param string $principalUri
     * @return NotificationInterface[]
     */
    function getNotificationsForPrincipal($principalUri);

    /**
     * This deletes a specific notifcation.
     *
     * This may be called by a client once it deems a notification handled.
     *
     * @param string $principalUri
     * @param NotificationInterface $notification
     * @return void
     */
    function deleteNotification($principalUri, NotificationInterface $notification);

}
