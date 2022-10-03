<?php
/**
 * Mail composer file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Mail;

/**
 * Mail composer class.
 */
class Composer
{
	/**
	 * Get all composers.
	 *
	 * @return string[]
	 */
	public static function getAll(): array
	{
		$composers = [];
		foreach ((new \DirectoryIterator(ROOT_DIRECTORY . '/app/Mail/Composers')) as $fileinfo) {
			if ('php' === $fileinfo->getExtension()) {
				$fileName = $fileinfo->getBasename('.php');
				$class = "App\\Mail\\Composers\\{$fileName}";
				$composers[$fileName] = (new $class())::NAME;
			}
		}
		return $composers;
	}
}
