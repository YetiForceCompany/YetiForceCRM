<?php

namespace App\Layout;

/**
 * Icon class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Icon
{
	private static $extensionIcon = [
		'application/pdf' => 'far fa-file-pdf',
		'application/msword' => 'far fa-file-word',
		'application/vnd.openxmlformats-officedocument.word' => 'far fa-file-word',
		'application/vnd.oasis.opendocument.text' => 'far fa-file-word',
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'far fa-file-excel',
		'application/vnd.oasis.opendocument.spreadsheet' => 'far fa-file-excel',
		'application/vnd.ms-excel' => 'far fa-file-excel',
		'text/plain' => 'far fa-file-alt',
		'application/rtf' => 'far fa-file-alt',
		'application/zip' => 'far fa-file-archive',
		'application/x-compressed-zip' => 'far fa-file-archive',
		'application/x-rar-compressed' => 'far fa-file-archive',
		'application/x-7z-compressed' => 'far fa-file-archive',
		'application/vnd.openxmlformats-officedocument.presentationml.template' => 'far fa-file-powerpoint',
		'application/vnd.openxmlformats-officedocument.presentationml.slideshow' => 'far fa-file-powerpoint',
		'application/vnd.ms-powerpointtd>' => 'far fa-file-powerpoint',
		'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'far fa-file-powerpoint',
		'application/vnd.oasis.opendocument.presentation' => 'far fa-file-powerpoint',
		'image' => 'far fa-file-image',
		'text/html' => 'fab fa-html5',
		'text/json' => 'far fa-file-code',
		'text/css' => 'fab fa-css3',
		'application/javascript' => 'far fa-file-code',
		'text/xml' => 'far fa-file-code',
		'application/x-shockwave-flash' => 'far fa-file-image',
		'video' => 'far fa-file-video',
		'audio' => 'far fa-file-audio',
		'application/vnd.oasis.opendocument.text' => 'far fa-file-word',
		'text/vcard' => 'fas fa-calendar-alt',
		'text/calendar' => 'fas fa-calendar-alt',
		'application/x-javascript' => 'far fa-file-code',
	];

	public static function getIconByFileType($exntension)
	{
		$explodeExtension = explode('/', $exntension);
		$explodeExtension = reset($explodeExtension);
		if (isset(self::$extensionIcon[$explodeExtension])) {
			$fileIcon = self::$extensionIcon[$explodeExtension];
		}
		if (isset(self::$extensionIcon[$exntension])) {
			$fileIcon = self::$extensionIcon[$exntension];
		}
		if (empty($fileIcon)) {
			$fileIcon = 'userIcon-Documents';
		}
		return $fileIcon;
	}
}
