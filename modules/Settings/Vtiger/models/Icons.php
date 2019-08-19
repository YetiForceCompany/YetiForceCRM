<?php

/**
 * Settings Icons Model Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
class Settings_Vtiger_Icons_Model
{
	/**
	 * Icons.
	 *
	 * @var array
	 */
	protected static $icons;

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
	 * Get user icons.
	 *
	 * @return array
	 */
	public static function getUserIcons(): array
	{
		static::init();
		$icons = [];
		foreach (self::$icons['user'] as $icon) {
			$icons[$icon] = 'userIcon-' . $icon;
		}
		return $icons;
	}

	/**
	 * Get admin icons.
	 *
	 * @return array
	 */
	public static function getAdminIcons(): array
	{
		static::init();
		$icons = [];
		foreach (self::$icons['admin'] as $icon) {
			$icons[$icon] = 'adminIcon-' . $icon;
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
		static::init();
		$icons = [];
		foreach (self::$icons['additional'] as $icon) {
			$icons[$icon] = 'AdditionalIcon-' . $icon;
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
		static::init();
		$icons = [];
		foreach (self::$icons['fa'] as $icon) {
			$icons[$icon] = $icon;
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
		static::init();
		$icons = [];
		foreach (self::$icons['mdi'] as $icon) {
			$icons[$icon] = 'mdi mdi-' . $icon;
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
		static::init();
		$icons = [];
		foreach (self::$icons['yfi'] as $icon) {
			$icons[$icon] = 'yfi-' . $icon;
		}
		foreach ($icons['yfm'] as $icon) {
			$yetiIcons[$icon] = 'yfm-' . $icon;
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
		static::init();
		$images = [];
		$path = 'public_html' . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . \App\Layout::getActiveLayout() . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
		$dir = new DirectoryIterator($path);
		foreach ($dir as $fileinfo) {
			$file = $fileinfo->getFilename();
			if (!$fileinfo->isDot()) {
				$mimeType = \App\Fields\File::getMimeContentType($path . $file);
				$mimeTypeContents = explode('/', $mimeType);
				if ('image' == $mimeTypeContents[0]) {
					$images['img-' . $file] = \Vtiger_Theme::getImagePath($file);
				}
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
		$icons = [];
		$icons = array_merge($icons, self::getImageIcons());
		$icons = array_merge($icons, self::getUserIcons());
		$icons = array_merge($icons, self::getAdminIcons());
		$icons = array_merge($icons, self::getAdditionalIcons());
		$icons = array_merge($icons, self::getYetiForceIcons());
		$icons = array_merge($icons, self::getMaterialDesignIcons());
		return array_merge($icons, self::getFontAwesomeIcons());
	}
}
