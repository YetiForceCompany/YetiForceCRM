<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_Theme extends Vtiger_Viewer
{
	/**
	 * Function to get the path of a given style sheet or default style sheet.
	 *
	 * @return <string / Boolean> - file path , false if not exists
	 */
	public static function getThemeStyle()
	{
		$filePath = self::getThemePath() . '/' . 'style.css';
		$completeFilePath = Vtiger_Loader::resolveNameToPath('~public_html/' . $filePath);
		if (file_exists($completeFilePath)) {
			return $filePath;
		}
		// Exception should be thrown???
		return false;
	}

	/**
	 * Function to get the image path
	 * This checks image in selected theme if not in images folder if it doest nor exists either case will retutn false.
	 *
	 * @param string $imageFileName - file name with extension
	 *
	 * @return <string/boolean> - returns file path if exists or false;
	 */
	public static function getImagePath($imageFileName)
	{
		$basePath = '';
		if (!IS_PUBLIC_DIR) {
			$basePath = 'public_html/';
		}
		$imageFilePath = 'layouts/' . self::getLayoutName() . '/images/' . $imageFileName;
		$completeImageFilePath = Vtiger_Loader::resolveNameToPath('~' . 'public_html/' . $imageFilePath);
		if (file_exists($completeImageFilePath)) {
			return $basePath . $imageFilePath;
		}
		$imageFilePath = self::getThemePath() . '/images/' . $imageFileName;
		$completeImageFilePath = Vtiger_Loader::resolveNameToPath('~public_html/' . $imageFilePath);
		if (file_exists($completeImageFilePath)) {
			return $basePath . $imageFilePath;
		}
		$fallbackPath = self::getBaseThemePath() . '/images/' . $imageFileName;
		$completeFallBackThemePath = Vtiger_Loader::resolveNameToPath('~public_html/' . $fallbackPath);
		if (file_exists($completeFallBackThemePath)) {
			return $basePath . $fallbackPath;
		}
		return false;
	}

	/**
	 * Function to get the image path or get defaulf
	 * This function searches for an image, it takes a default name in case it's missing,
	 * if there's no image with a default name it will return false.
	 *
	 * @param string $imageFileName   - file name
	 * @param string $defaultFileName - file name
	 *
	 * @return <string/boolean> - returns file path if exists or false;
	 */
	public static function getOrignOrDefaultImgPath($imageFileName, $defaultFileName)
	{
		$basePath = '';
		if (!IS_PUBLIC_DIR) {
			$basePath = 'public_html/';
		}
		$allowedImgTypes = ['.gif', '.jpg', '.png'];
		foreach ($allowedImgTypes as $type) {
			$imageFilePath = self::getThemePath() . '/' . 'images' . '/' . $imageFileName . $type;
			$completeImageFilePath = Vtiger_Loader::resolveNameToPath('~public_html/' . $imageFilePath);
			if (file_exists($completeImageFilePath)) {
				return $basePath . $imageFilePath;
			}
			$fallbackPath = self::getBaseThemePath() . '/' . 'images' . '/' . $imageFileName . $type;
			$completeFallBackThemePath = Vtiger_Loader::resolveNameToPath('~public_html/' . $fallbackPath);
			if (file_exists($completeFallBackThemePath)) {
				return $basePath . $fallbackPath;
			}
		}
		foreach ($allowedImgTypes as $type) {
			$imageFilePath = self::getThemePath() . '/' . 'images' . '/' . $defaultFileName . $type;
			$completeImageFilePath = Vtiger_Loader::resolveNameToPath('~public_html/' . $imageFilePath);
			if (file_exists($completeImageFilePath)) {
				return $basePath . $imageFilePath;
			}
			$fallbackPath = self::getBaseThemePath() . '/' . 'images' . '/' . $defaultFileName . $type;
			$completeFallBackThemePath = Vtiger_Loader::resolveNameToPath('~public_html/' . $fallbackPath);
			if (file_exists($completeFallBackThemePath)) {
				return $basePath . $fallbackPath;
			}
		}
		return false;
	}

	/**
	 * Function to get the Base Theme Path, until theme folder not selected theme folder.
	 *
	 * @return string - theme folder
	 */
	public static function getBaseThemePath()
	{
		return 'layouts/' . self::getLayoutName() . '/skins';
	}

	/**
	 *  * @return string -  path to base style
	 */
	public static function getBaseStylePath()
	{
		return 'layouts/' . self::getLayoutName() . '/styles/Main.css';
	}

	/**
	 * Function to get the selected theme folder path.
	 *
	 * @param mixed $theme
	 *
	 * @return string -  selected theme path
	 */
	public static function getThemePath($theme = '')
	{
		if (empty($theme)) {
			$theme = self::getDefaultThemeName();
		}
		$selectedThemePath = self::getBaseThemePath() . '/' . $theme;
		$completeSelectedThemePath = Vtiger_Loader::resolveNameToPath('~public_html/' . $selectedThemePath);
		if (file_exists($completeSelectedThemePath)) {
			return $selectedThemePath;
		}
		$fallBackThemePath = self::getBaseThemePath() . '/' . self::getDefaultThemeName();
		$completeFallBackThemePath = Vtiger_Loader::resolveNameToPath('~public_html/' . $fallBackThemePath);
		if (file_exists($completeFallBackThemePath)) {
			return $fallBackThemePath;
		}
		return false;
	}

	/**
	 * Function to get the default theme name.
	 *
	 * @return string - Default theme name
	 */
	public static function getDefaultThemeName()
	{
		$theme = \App\User::getCurrentUserModel()->getDetail('theme');

		return empty($theme) ? self::DEFAULTTHEME : $theme;
	}

	/**
	 * Function to returns all skins(themes).
	 *
	 * @return <Array>
	 */
	public static function getAllSkins()
	{
		return Vtiger_Util_Helper::getAllSkins();
	}

	/**
	 * Function returns the current users skin(theme) path.
	 */
	public static function getCurrentUserThemePath()
	{
		$themeName = self::getDefaultThemeName();
		$baseLayoutPath = self::getBaseThemePath();

		return $baseLayoutPath . '/' . $themeName;
	}
}
