<?php
/**
 * Layout class
 * @package YetiForce.App
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @copyright YetiForce Sp. z o.o.
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
namespace App;

/**
 * Layout class
 */
class Layout
{

	/**
	 * Get active layout name
	 * @return string
	 */
	public static function getActiveLayout()
	{
		$layout = \Vtiger_Session::get('layout');
		if (!empty($layout)) {
			return $layout;
		}
		return \AppConfig::main('defaultLayout');
	}

	/**
	 * Get file from layout
	 * @param string $name
	 * @return string
	 */
	public static function getLayoutFile($name)
	{
		$basePath = 'layouts' . '/' . \AppConfig::main('defaultLayout') . '/';
		$filePath = \Vtiger_Loader::resolveNameToPath('~' . $basePath . $name);
		if (is_file($filePath)) {
			if (!IS_PUBLIC_DIR) {
				$basePath = 'public/' . $basePath;
			}
			return $basePath . $name;
		}
		$basePath = 'layouts' . '/' . \Vtiger_Viewer::getDefaultLayoutName() . '/';
		if (!IS_PUBLIC_DIR) {
			$basePath = 'public/' . $basePath;
		}
		return $basePath . $name;
	}

	/**
	 * Get all layouts list
	 * @return string[]
	 */
	public static function getAllLayouts()
	{
		$all = (new \App\Db\Query())->select(['name', 'label'])->from('vtiger_layout')->all();
		$folders = [
			'basic' => Language::translate('LBL_DEFAULT')
		];
		foreach ($all as $row) {
			$folders[$row['name']] = Language::translate($row['label']);
		}
		return $folders;
	}

	/**
	 * Get public url from file
	 * @param string $name
	 * @param bool $full
	 * @return string
	 */
	public static function getPublicUrl($name, $full = false)
	{
		$basePath = '';
		if ($full) {
			$basePath .= AppConfig::main('site_URL');
		}
		if (!IS_PUBLIC_DIR) {
			$basePath = 'public/';
		}
		return $basePath . $name;
	}
}
