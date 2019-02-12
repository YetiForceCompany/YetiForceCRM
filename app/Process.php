<?php
/**
 * Process main class.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	 * Requesrt start time.
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
}
