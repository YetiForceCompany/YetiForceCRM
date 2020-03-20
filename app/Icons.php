<?php

/**
 * Icons Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */

namespace App;

class Icons
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
			$icons[] = ['type' => 'icon', 'name' => 'yfm-' . $icon];
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
		static::init();
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
		static::init();
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
		static::init();
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
		static::init();
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
		static::init();
		$images = [];
		$path = 'public_html' . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . \App\Layout::getActiveLayout() . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
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
