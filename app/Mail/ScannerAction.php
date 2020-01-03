<?php
/**
 * Mail record finder file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Mail;

/**
 * Mail record finder class.
 */
class ScannerAction
{
	/**
	 * Mail scanner actions directory.
	 *
	 * @var string
	 */
	private static $actionsDir = '/app/Mail/ScannerAction';

	/**
	 * Get mail scanner actions.
	 *
	 * @return array
	 */
	public static function getActions(): array
	{
		$actions = [];
		foreach ((new \DirectoryIterator(\ROOT_DIRECTORY . self::$actionsDir)) as $fileinfo) {
			if ('php' === $fileinfo->getExtension() && 'Base' !== ($fileName = $fileinfo->getBasename('.php'))) {
				$class = "App\\Mail\\ScannerAction\\{$fileName}";
				$actions[$class::$priority][] = $fileName;
			}
		}
		ksort($actions);
		return \Illuminate\Support\Arr::flatten($actions);
	}
}
