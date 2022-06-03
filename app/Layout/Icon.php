<?php
/**
 * Icon file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Layout;

/**
 * Icon class.
 */
class Icon
{
	/**
	 * Icons.
	 *
	 * @var array
	 */
	protected static $icons;

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
		$explodeExtension = explode('/', $exntension ?? '');
		$explodeExtension = reset($explodeExtension);
		if (isset(self::$extensionIcon[$explodeExtension])) {
			$fileIcon = self::$extensionIcon[$explodeExtension];
		}
		if (isset(self::$extensionIcon[$exntension])) {
			$fileIcon = self::$extensionIcon[$exntension];
		}
		if (empty($fileIcon)) {
			$fileIcon = 'yfm-Documents';
		}
		return $fileIcon;
	}

	/**
	 * Init icons data.
	 *
	 * @return void
	 */
	public static function init()
	{
		if (!isset(static::$icons)) {
			static::$icons = require 'app_data/icons.php';
		}
	}

	/**
	 * Get admin icons.
	 *
	 * @return array
	 */
	public static function getAdminIcons(): array
	{
		$icons = [];
		foreach (self::$icons['admin'] as $icon) {
			$icons[] = ['type' => 'icon', 'name' => 'adminIcon-' . $icon];
		}
		return $icons;
	}

	/**
	 * Get additional icons.
	 *
	 * @return array
	 */
	public static function getAdditionalIcons(): array
	{
		$icons = [];
		foreach (self::$icons['additional'] as $icon) {
			$icons[] = ['type' => 'icon', 'name' => 'AdditionalIcon-' . $icon];
		}
		return $icons;
	}

	/**
	 * Get fa icons.
	 *
	 * @return array
	 */
	public static function getFontAwesomeIcons(): array
	{
		$icons = [];
		foreach (self::$icons['fa'] as $icon) {
			$icons[] = ['type' => 'icon', 'name' => $icon];
		}
		return $icons;
	}

	/**
	 * Get mdi icons.
	 *
	 * @return array
	 */
	public static function getMaterialDesignIcons(): array
	{
		$icons = [];
		foreach (self::$icons['mdi'] as $icon) {
			$icons[] = ['type' => 'icon', 'name' => 'mdi mdi-' . $icon];
		}
		return $icons;
	}

	/**
	 * Get yeti icons.
	 *
	 * @return array
	 */
	public static function getYetiForceIcons(): array
	{
		$icons = [];
		foreach (self::$icons['yfi'] as $icon) {
			$icons[] = ['type' => 'icon', 'name' => 'yfi-' . $icon];
		}
		foreach (self::$icons['yfm'] as $icon) {
			$icons[] = ['type' => 'icon', 'name' => 'yfm-' . $icon];
		}
		return $icons;
	}

	/**
	 * Get icon images.
	 *
	 * @return array
	 */
	public static function getImageIcons(): array
	{
		$images = [];
		$path = 'public_html' . \DIRECTORY_SEPARATOR . 'layouts' . \DIRECTORY_SEPARATOR . \App\Layout::getActiveLayout() . \DIRECTORY_SEPARATOR . 'images' . \DIRECTORY_SEPARATOR;
		$dir = new \DirectoryIterator($path);
		foreach ($dir as $fileinfo) {
			$file = $fileinfo->getFilename();
			if (!$fileinfo->isDot()) {
				$images[] = ['type' => 'image', 'name' => $file, 'path' => \Vtiger_Theme::getImagePath($file)];
			}
		}
		return $images;
	}

	/**
	 * Get all icons and images.
	 *
	 * @return array
	 */
	public static function getAll(): array
	{
		static::init();
		return array_merge(
			self::getImageIcons(),
			self::getAdminIcons(),
			self::getAdditionalIcons(),
			self::getYetiForceIcons(),
			self::getFontAwesomeIcons(),
			self::getMaterialDesignIcons()
		);
	}

	/**
	 * Get only icons excluding images.
	 *
	 * @return array
	 */
	public static function getIcons(): array
	{
		static::init();
		return array_merge(
			self::getAdminIcons(),
			self::getAdditionalIcons(),
			self::getYetiForceIcons(),
			self::getFontAwesomeIcons(),
			self::getMaterialDesignIcons()
		);
	}
}
