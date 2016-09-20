<?php namespace Yeti;

use Sabre\DAV;
use Sabre\VObject\ITip;

class CalDAV_Schedule extends DAV\ServerPlugin
{

	/**
	 * Reference to server object
	 *
	 * @var DAV\Server
	 */
	protected $server;

	const DEBUG_FILE = 'cache/logs/davDebug.log';
	const EXCEPTION_FILE = 'cache/logs/davException.log';

	public function initialize(DAV\Server $server)
	{
		$this->server = $server;
		$server->on('schedule', [$this, 'schedule'], 120);
	}

	/**
	 * Event handler for the 'schedule' event.
	 *
	 * @param ITip\Message $iTipMessage
	 * @return void
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

		$summary = $iTipMessage->message->VEVENT->SUMMARY;

		if (parse_url($iTipMessage->sender, PHP_URL_SCHEME) !== 'mailto')
			return;

		if (parse_url($iTipMessage->recipient, PHP_URL_SCHEME) !== 'mailto')
			return;

		$sender = substr($iTipMessage->sender, 7);
		$recipient = substr($iTipMessage->recipient, 7);

		if ($iTipMessage->senderName) {
			$sender = $iTipMessage->senderName . ' <' . $sender . '>';
		}
		if ($iTipMessage->recipientName) {
			$recipient = $iTipMessage->recipientName . ' <' . $recipient . '>';
		}

		$subject = 'SabreDAV iTIP message';
		switch (strtoupper($iTipMessage->method)) {
			case 'REPLY' :
				$subject = 'Re: ' . $summary;
				break;
			case 'REQUEST' :
				$subject = $summary;
				break;
			case 'CANCEL' :
				$subject = 'Cancelled: ' . $summary;
				break;
		}

		$headers = [
			'Reply-To: ' . $sender,
			'From: ' . $this->senderEmail,
			'Content-Type: text/calendar; charset=UTF-8; method=' . $iTipMessage->method,
		];
		if (DAV\Server::$exposeVersion) {
			$headers[] = 'X-Sabre-Version: ' . DAV\Version::VERSION;
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
	// @codeCoverageIgnoreEnd

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
