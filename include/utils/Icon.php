<?php namespace includes\utils;

/**
 * Icon class
 * @package YetiForce.Include
 * @license licenses/License.html
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Icon
{

	private static $extensionIcon = [
		'application/pdf' => 'fa-file-pdf-o',
		'application/msword' => 'fa fa-file-word-o',
		'application/vnd.openxmlformats-officedocument.word' => 'fa fa-file-word-o',
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'fa fa-file-excel-o',
		'application/vnd.ms-excel' => 'fa fa-file-excel-o',
		'image/jpeg' => 'fa fa-file-image-o',
		'image/gif' => 'fa fa-file-image-o',
		'image/png' => 'fa fa-file-image-o',
		'text/plain' => 'fa fa-file-text-o',
		'application/rtf' => 'fa fa-file-text-o',
		'application/zip' => 'fa fa-file-archive-o',
		'application/x-compressed-zip' => 'fa fa-file-archive-o',
		'application/vnd.openxmlformats-officedocument.presentationml.template' => 'fa fa-file-powerpoint-o',
		'application/vnd.openxmlformats-officedocument.presentationml.slideshow' => 'fa fa-file-powerpoint-o',
		'application/vnd.ms-powerpointtd>' => 'fa fa-file-powerpoint-o',
		'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'fa fa-file-powerpoint-o',
	];

	public static function getIconByFileType($exntension)
	{
		$fileIcon = self::$extensionIcon[$exntension];
		if (!$fileIcon)
			$fileIcon = 'userIcon-Documents';
		return $fileIcon;
	}
}
