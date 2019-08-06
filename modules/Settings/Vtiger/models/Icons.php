<?php

/**
 * Settings Icons Model Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_Vtiger_Icons_Model
{
	protected static $icons;

	public static function init()
	{
		if (isset(static::$icons)) {
			return true;
		}
		static::$icons = require 'app_data/icons.php';
	}

	public static function getUserIcon()
	{
		$icons = [];
		foreach (self::$icons['user'] as $icon) {
			$icons[$icon] = 'userIcon-' . $icon;
		}
		return $icons;
	}

	public static function getAdminIcon()
	{
		$icons = [];
		foreach (self::$icons['admin'] as $icon) {
			$icons[$icon] = 'adminIcon-' . $icon;
		}
		return $icons;
	}

	public static function getAdditionalIcon()
	{
		$icons = [];
		foreach (self::$icons['additional'] as $icon) {
			$icons[$icon] = 'AdditionalIcon-' . $icon;
		}
		return $icons;
	}

	public static function getFontAwesomeIcon()
	{
		$icons = [];
		foreach (self::$icons['fa'] as $icon) {
			$icons[$icon] = $icon;
		}
		return $icons;
	}

	public static function getMaterialDesignIcon()
	{
		$icons = [];
		foreach (self::$icons['mdi'] as $icon) {
			$icons[$icon] = $icon;
		}
		return $icons;
	}

	/**
	 * Function get images.
	 *
	 * @return string
	 */
	public static function getImageIcon()
	{
		$images = [];
		$path = 'public_html' . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . \App\Layout::getActiveLayout() . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
		$dir = new DirectoryIterator($path);
		foreach ($dir as $fileinfo) {
			$file = $fileinfo->getFilename();
			if (!$fileinfo->isDot()) {
				$mimeType = \App\Fields\File::getMimeContentType($path . $file);
				$mimeTypeContents = explode('/', $mimeType);
				if ('image' == $mimeTypeContents[0]) {
					$images[$file] = $file;
				}
			}
		}
		return $images;
	}

	public static function getAll()
	{
		$icons = [];
		$icons = array_merge($icons, self::getUserIcon());
		$icons = array_merge($icons, self::getAdminIcon());
		$icons = array_merge($icons, self::getAdditionalIcon());
		$icons = array_merge($icons, self::getMaterialDesignIcon());
		return array_merge($icons, self::getFontAwesomeIcon());
	}
}
Settings_Vtiger_Icons_Model::init();
