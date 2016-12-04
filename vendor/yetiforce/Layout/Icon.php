<?php
namespace App\Layout;

/**
 * Icon class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Adrian Koń <a.kon@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Icon
{

	private static $extensionIcon = [
		'application/pdf' => 'fa fa-file-pdf-o',
		'application/msword' => 'fa fa-file-word-o',
		'application/vnd.openxmlformats-officedocument.word' => 'fa fa-file-word-o',
		'application/vnd.oasis.opendocument.text' => 'fa fa-file-word-o',
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'fa fa-file-excel-o',
		'application/vnd.oasis.opendocument.spreadsheet' => 'fa fa-file-excel-o',
		'application/vnd.ms-excel' => 'fa fa-file-excel-o',
		'text/plain' => 'fa fa-file-text-o',
		'application/rtf' => 'fa fa-file-text-o',
		'application/zip' => 'fa fa-file-archive-o',
		'application/x-compressed-zip' => 'fa fa-file-archive-o',
		'application/x-rar-compressed' => 'fa fa-file-archive-o',
		'application/x-7z-compressed' => 'fa fa-file-archive-o',
		'application/vnd.openxmlformats-officedocument.presentationml.template' => 'fa fa-file-powerpoint-o',
		'application/vnd.openxmlformats-officedocument.presentationml.slideshow' => 'fa fa-file-powerpoint-o',
		'application/vnd.ms-powerpointtd>' => 'fa fa-file-powerpoint-o',
		'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'fa fa-file-powerpoint-o',
		'application/vnd.oasis.opendocument.presentation' => 'fa fa-file-powerpoint-o',
		'image' => 'fa fa-file-image-o',
		'text/html' => 'fa fa-html5',
		'text/json' => 'fa fa-file-code-o',
		'text/css' => 'fa fa-css3',
		'application/javascript' => 'fa fa-file-code-o',
		'text/xml' => 'fa fa-file-code-o',
		'application/x-shockwave-flash' => 'fa fa-file-image-o',
		'video' => 'fa fa-file-video-o',
		'audio' => 'fa fa-file-audio-o',
		'application/vnd.oasis.opendocument.text' => 'fa fa-file-word-o',
		'text/vcard' => 'fa fa-calendar',
		'text/calendar' => 'fa fa-calendar',
		'application/x-javascript' => 'fa fa-file-code-o',
	];

	public static function getIconByFileType($exntension)
	{
		$explodeExtension = explode('/', $exntension);
		$explodeExtension = reset($explodeExtension);
		if (isset(self::$extensionIcon[$explodeExtension]))
			$fileIcon = self::$extensionIcon[$explodeExtension];
		if (isset(self::$extensionIcon[$exntension]))
			$fileIcon = self::$extensionIcon[$exntension];
		if (!$fileIcon)
			$fileIcon = 'userIcon-Documents';
		return $fileIcon;
	}
}
