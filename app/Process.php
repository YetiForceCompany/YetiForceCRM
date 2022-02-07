<?php
/**
 * Process main class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * Process class.
 */
class Process
{
	/**
	 * Request start time.
	 *
	 * @var int
	 */
	public static $startTime;

	/**
	 * Request mode.
	 *
	 * @var string
	 */
	public static $requestMode;

	/**
	 * CRM root directory.
	 *
	 * @var string
	 */
	public static $rootDirectory;

	/**
	 * Request process type.
	 *
	 * @var string
	 */
	public static $processType;

	/**
	 * Request process name.
	 *
	 * @var string
	 */
	public static $processName;

	/**
	 * List of events.
	 *
	 * @var array
	 */
	private static $events = [];

	/**
	 * Initialization of events.
	 *
	 * @return void
	 */
	public static function init(): void
	{
		self::$events = Session::get('processEvents') ?? [];
	}

	/**
	 * Add event.
	 *
	 * App\Process::addEvent([
	 *	'name' => 'notify test',
	 *	'execution' => 'once',
	 *	'type' => 'notify',
	 *	'notify' => [
	 *		'text' => 'test',
	 *		'type' => 'info' // alert, notice, info, success, error
	 *	]
	 * ]);
	 * alert, notice, info, success, error
	 *
	 * @param array $event
	 *
	 * @return void
	 */
	public static function addEvent(array $event): void
	{
		if (empty($event['name']) || empty($event['type'])) {
			throw new Exceptions\AppException('Incorrect data');
		}
		if (empty($event['priority'])) {
			$event['priority'] = 5;
		}
		if (empty($event['execution'])) {
			$event['execution'] = 'constant';
		}
		self::$events[$event['name']] = $event;
		self::writeSession();
	}

	/**
	 * Remove event.
	 *
	 * @param string $name
	 *
	 * @return void
	 */
	public static function removeEvent(string $name): void
	{
		if (isset(self::$events[$name])) {
			unset(self::$events[$name]);
			self::writeSession();
		}
	}

	/**
	 * Has event.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public static function hasEvent(string $name): bool
	{
		return isset(self::$events[$name]);
	}

	/**
	 * Get events.
	 *
	 * @return array
	 */
	public static function getEvents(): array
	{
		$events = [];
		$writeSession = false;
		foreach (self::$events as $name => $row) {
			$events[] = $row;
			if ('once' === $row['execution']) {
				unset(self::$events[$name]);
				$writeSession = true;
			}
		}
		if ($writeSession) {
			self::writeSession();
		}
		$priority = array_column($events, 'priority');
		array_multisort($priority, SORT_DESC, $events);
		return $events;
	}

	/**
	 * Write session data.
	 *
	 * @return void
	 */
	private static function writeSession(): void
	{
		Session::set('processEvents', self::$events);
	}
}
