<?php
/**
 * Layout class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App;

/**
 * Layout class.
 */
class Layout
{
	/**
	 * Get active layout name.
	 *
	 * @return string
	 */
	public static function getActiveLayout()
	{
		if (Session::has('layout')) {
			return Session::get('layout');
		}
		return \AppConfig::main('defaultLayout');
	}

	/**
	 * Get file from layout.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public static function getLayoutFile($name)
	{
		$basePath = 'layouts' . '/' . \AppConfig::main('defaultLayout') . '/';
		$filePath = \Vtiger_Loader::resolveNameToPath('~' . $basePath . $name);
		if (is_file($filePath)) {
			if (!IS_PUBLIC_DIR) {
				$basePath = 'public_html/' . $basePath;
			}

			return $basePath . $name;
		}
		$basePath = 'layouts' . '/' . \Vtiger_Viewer::getDefaultLayoutName() . '/';
		if (!IS_PUBLIC_DIR) {
			$basePath = 'public_html/' . $basePath;
		}
		return $basePath . $name;
	}

	/**
	 * Get all layouts list.
	 *
	 * @return string[]
	 */
	public static function getAllLayouts()
	{
		$all = (new \App\Db\Query())->select(['name', 'label'])->from('vtiger_layout')->all();
		$folders = [
			'basic' => Language::translate('LBL_DEFAULT'),
		];
		foreach ($all as $row) {
			$folders[$row['name']] = Language::translate($row['label']);
		}
		return $folders;
	}

	/**
	 * Get public url from file.
	 *
	 * @param string $name
	 * @param bool   $full
	 *
	 * @return string
	 */
	public static function getPublicUrl($name, $full = false)
	{
		$basePath = '';
		if ($full) {
			$basePath .= \AppConfig::main('site_URL');
		}
		if (!IS_PUBLIC_DIR) {
			$basePath .= 'public_html/';
		}
		return $basePath . $name;
	}

	/**
	 * The function get path  to the image.
	 *
	 * @param string $imageName
	 *
	 * @return array
	 */
	public static function getImagePath($imageName)
	{
		return \Vtiger_Theme::getImagePath($imageName);
	}

	/**
	 * Function takes a template path.
	 *
	 * @param string $templateName
	 * @param string $moduleName
	 *
	 * @return array
	 */
	public static function getTemplatePath($templateName, $moduleName = '')
	{
		return \Vtiger_Viewer::getInstance()->getTemplatePath($templateName, $moduleName);
	}

	/**
	 * Get unique id for HTML ids.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public static function getUniqueId($name = '')
	{
		return $name . random_int(100, 99999);
	}
}
