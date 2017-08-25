<?php
namespace App;

/**
 * Colors stylesheet generator class
 * @package YetiForce.App
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Sławomir Kłos <s.klos@yetiforce.com>
 */

/**
 * Custom colors stylesheet file generator
 */
class Colors
{

	/**
	 * Regenerate stylesheet file
	 * @param string $type type to generate, default all
	 */
	public static function generate($type = 'all')
	{
		switch ($type) {
			case 'user':
				self::generateUsers();
				break;
			case 'calendar':
				self::generateCalendar();
				break;
			default:
				self::generateCalendar();
				self::generateUsers();
				break;
		}
	}

	private static function generateUsers()
	{
		$css = '';
		foreach (\Settings_Calendar_Module_Model::getUserColors('colors') as $item) {
			$css .= '.userCol_' . $item['id'] . ' {' . "\r\n"
				. '	background: ' . $item['color'] . ' !important;' . "\r\n"
				. '}' . "\r\n";
		}
		$file = ROOT_DIRECTORY . '/public_html/layouts/resources/colors/users.css';
		file_put_contents($file, $css);
	}

	private static function generateCalendar()
	{
		$css = '';
		foreach (\Settings_Calendar_Module_Model::getCalendarConfig('colors') as $item) {
			$css .= '.calCol_' . $item['label'] . ' {' . "\r\n"
				. '	border: 1px solid ' . $item['value'] . ' !important;' . "\r\n"
				. '}' . "\r\n";
			$css .= '.listCol_' . $item['label'] . ' {' . "\r\n"
				. '	background: ' . $item['value'] . ' !important;' . "\r\n"
				. '}' . "\r\n";
		}
		$file = ROOT_DIRECTORY . '/public_html/layouts/resources/colors/calendar.css';
		file_put_contents($file, $css);
	}
}
