<?php
namespace App\Layout;

/**
 * Icon class
 * @package YetiForce.App
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Adrian Koń <a.kon@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Icon
{

	private static $extensionIcon = [
		'application/pdf' => 'far fa-file-pdf',
		'application/msword' => 'far fa-file-word',
		'application/vnd.openxmlformats-officedocument.word' => 'far fa-file-word',
		'application/vnd.oasis.opendocument.text' => 'far fa-file-word',
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'fa fa-file-excel-o',
		'application/vnd.oasis.opendocument.spreadsheet' => 'fa fa-file-excel-o',
		'application/vnd.ms-excel' => 'fa fa-file-excel-o',
		'text/plain' => 'fa fa-file-text-o',
		'application/rtf' => 'fa fa-file-text-o',
		'application/zip' => 'fa fa-file-archive-o',
		'application/x-compressed-zip' => 'fa fa-file-archive-o',
		'application/x-rar-compressed' => 'fa fa-file-archive-o',
		'application/x-7z-compressed' => 'fa fa-file-archive-o',
		'application/vnd.openxmlformats-officedocument.presentationml.template' => 'far fa-file-powerpoint',
		'application/vnd.openxmlformats-officedocument.presentationml.slideshow' => 'far fa-file-powerpoint',
		'application/vnd.ms-powerpointtd>' => 'far fa-file-powerpoint',
		'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'far fa-file-powerpoint',
		'application/vnd.oasis.opendocument.presentation' => 'far fa-file-powerpoint',
		'image' => 'far fa-file-image',
		'text/html' => 'fa fa-html5',
		'text/json' => 'far fa-file-code',
		'text/css' => 'fab fa-css3',
		'application/javascript' => 'far fa-file-code',
		'text/xml' => 'far fa-file-code',
		'application/x-shockwave-flash' => 'far fa-file-image',
		'video' => 'far fa-file-video',
		'audio' => 'far fa-file-audio',
		'application/vnd.oasis.opendocument.text' => 'far fa-file-word',
		'text/vcard' => 'fa fa-calendar',
		'text/calendar' => 'fa fa-calendar',
		'application/x-javascript' => 'far fa-file-code',
	];

	public static function getIconByFileType($exntension)
	{
		$explodeExtension = explode('/', $exntension);
		$explodeExtension = reset($explodeExtension);
		if (isset(self::$extensionIcon[$explodeExtension]))
			$fileIcon = self::$extensionIcon[$explodeExtension];
		if (isset(self::$extensionIcon[$exntension]))
			$fileIcon = self::$extensionIcon[$exntension];
		if (empty($fileIcon)) {
			$fileIcon = 'userIcon-Documents';
		}
		return $fileIcon;
	}
}
