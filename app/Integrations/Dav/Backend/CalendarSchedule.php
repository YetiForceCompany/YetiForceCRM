<?php
/**
 * SabreDav PDO CalDAV Schedule backend file.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Dav\Backend;

use Sabre\DAV;
use Sabre\VObject\ITip;

/**
 * SabreDav PDO CalDAV Schedule backend class.
 */
class CalendarSchedule extends DAV\ServerPlugin
{
	/**
	 * Reference to server object.
	 *
	 * @var DAV\Server
	 */
	protected $server;

	/**
	 * Initializes the schedule.
	 *
	 * @param \Sabre\DAV\Server $server
	 */
	public function initialize(DAV\Server $server)
	{
		$this->server = $server;
		$server->on('schedule', [$this, 'schedule'], 120);
	}

	/**
	 * Event handler for the 'schedule' event.
	 *
	 * @param ITip\Message $iTipMessage
	 */
	public function schedule(ITip\Message $iTipMessage)
	{
		// Not sending any emails if the system considers the update
		// insignificant.
		if (!$iTipMessage->significantChange) {
			if (!$iTipMessage->scheduleStatus) {
				$iTipMessage->scheduleStatus = '1.0;We got the message, but it\'s not significant enough to warrant an email';
			}

			return;
		}
		if ('mailto' !== parse_url($iTipMessage->sender, PHP_URL_SCHEME)) {
			return;
		}
		if ('mailto' !== parse_url($iTipMessage->recipient, PHP_URL_SCHEME)) {
			return;
		}
		$iTipMessage->scheduleStatus = '1.1; Scheduling message is sent via ' . $this->getPluginName();
	}

	/**
	 * Returns a plugin name.
	 *
	 * Using this name other plugins will be able to access other plugins
	 * using \Sabre\DAV\Server::getPlugin
	 *
	 * @return string
	 */
	public function getPluginName()
	{
		return 'Yeti CalDAV Schedule';
	}

	/** @codeCoverageIgnoreEnd */

	/**
	 * Returns a bunch of meta-data about the plugin.
	 *
	 * Providing this information is optional, and is mainly displayed by the
	 * Browser plugin.
	 *
	 * The description key in the returned array may contain html and will not
	 * be sanitized.
	 *
	 * @return array
	 */
	public function getPluginInfo()
	{
		return [
			'name' => $this->getPluginName(),
			'description' => 'Adds support for invitations, integrated with YetiForce CRM',
			'link' => 'https://yetiforce.com/',
		];
	}
}
