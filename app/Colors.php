<?php
/**
 * Colors stylesheet generator file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * Colors stylesheet generator class.
 */
class Colors
{
	public const EMPTY_COLOR = 'rgba(0,0,0,0.1)';

	/**
	 * Generate stylesheet file.
	 *
	 * @param string $type type to generate, default all
	 */
	public static function generate($type = 'all')
	{
		switch ($type) {
			case 'user':
			case 'group':
			case 'owner':
				static::generateOwners();
				break;
			case 'module':
				static::generateModules();
				break;
			case 'picklist':
				static::generatePicklists();
				break;
			case 'field':
				static::generateFields();
				break;
			default:
				static::generateOwners();
				static::generateModules();
				static::generatePicklists();
				static::generateFields();
				break;
		}
	}

	/**
	 * Generate owners colors stylesheet.
	 */
	private static function generateOwners()
	{
		$css = '';
		$colors = [];
		foreach (static::getAllUserColor() as $item) {
			if (null !== $item['color'] && ltrim($item['color'], '#')) {
				$css .= '.ownerCBg_' . $item['id'] . ' { background: ' . $item['color'] . ' !important; font-weight: 500 !important; color: ' . static::getContrast($item['color']) . ' !important;}' . PHP_EOL;
				$css .= '.ownerCT_' . $item['id'] . ' { color: ' . $item['color'] . ' !important; }' . PHP_EOL;
				$css .= '.ownerCBr_' . $item['id'] . ' { border-color: ' . $item['color'] . ' !important; }' . PHP_EOL;
				$colors[$item['id']] = $item['color'];
			}
		}
		foreach (static::getAllGroupColor() as $item) {
			if (null !== $item['color'] && ltrim($item['color'], '#')) {
				$css .= '.ownerCBg_' . $item['id'] . ' { background: ' . $item['color'] . ' !important; font-weight: 500 !important; color: ' . static::getContrast($item['color']) . ' !important;}' . PHP_EOL;
				$css .= '.ownerCT_' . $item['id'] . ' { color: ' . $item['color'] . ' !important; }' . PHP_EOL;
				$css .= '.ownerCBr_' . $item['id'] . ' { border-color: ' . $item['color'] . ' !important; }' . PHP_EOL;
				$colors[$item['id']] = $item['color'];
			}
		}
		file_put_contents(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/owners.css', $css, LOCK_EX);
		file_put_contents(ROOT_DIRECTORY . '/app_data/owners_colors.php', '<?php return ' . Utils::varExport($colors) . ';', LOCK_EX);
	}

	/**
	 * Generate modules colors stylesheet.
	 */
	private static function generateModules()
	{
		$css = '';
		foreach (static::getAllModuleColor() as $item) {
			if (ltrim($item['color'], '#')) {
				$css .= '.modCrBr_' . $item['module'] . ' { border-color: ' . $item['color'] . '; }' . PHP_EOL;
				$css .= '.modCBg_' . $item['module'] . ' { background: ' . $item['color'] . ' !important; font-weight: 500 !important; color: ' . static::getContrast($item['color']) . ' !important;}' . PHP_EOL;
				$css .= '.modCT_' . $item['module'] . ' { color: ' . $item['color'] . '; }' . PHP_EOL;
			}
		}
		file_put_contents(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/modules.css', $css, LOCK_EX);
	}

	/**
	 * Generate picklists colors stylesheet.
	 */
	private static function generatePicklists()
	{
		$css = '';
		foreach (Fields\Picklist::getModules() as $module) {
			$fields = static::getPicklistFieldsByModule($module['tabname']);
			foreach ($fields as $field) {
				$values = \App\Fields\Picklist::getValues($field->getName());
				if ($values && ($firstRow = reset($values)) && \array_key_exists('color', $firstRow)) {
					foreach ($values as $item) {
						if (($color = $item['color'] ?? '') && '#' !== $color) {
							if (false === strpos($color, '#')) {
								$color = '#' . $color;
							}
							$contrastColor = static::getContrast($color);
							$css .= '.picklistCBr_' . $module['tabname'] . '_' . static::sanitizeValue($field->getName()) . '_' . static::sanitizeValue($item['picklistValue']) . ' { border-color: ' . $color . ' !important; }' . PHP_EOL;
							$css .= '.picklistCT_' . $module['tabname'] . '_' . static::sanitizeValue($field->getName()) . '_' . static::sanitizeValue($item['picklistValue']) . ' { color: ' . $color . ' !important; }' . PHP_EOL;
							$css .= '.picklistCBg_' . $module['tabname'] . '_' . static::sanitizeValue($field->getName()) . '_' . static::sanitizeValue($item['picklistValue']) . ' { background: ' . $color . ' !important; font-weight: 500 !important; color: ' . $contrastColor . ' !important;}' . PHP_EOL;
							$css .= '.picklistLb_' . $module['tabname'] . '_' . static::sanitizeValue($field->getName()) . '_' . static::sanitizeValue($item['picklistValue']) . ' { background: ' . $color . ' !important; font-weight: 500 !important; color: ' . $contrastColor . ' !important;  padding: 2px 7px 3px 7px;}' . PHP_EOL;
						}
					}
				}
			}
		}
		file_put_contents(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/picklists.css', $css, LOCK_EX);
	}

	/**
	 * Get normalized color or generate if empty.
	 *
	 * @param string $color
	 * @param mixed  $value
	 *
	 * @return string
	 */
	public static function get($color, $value)
	{
		if (empty($color)) {
			return static::getRandomColor($value);
		}
		$color = ltrim($color, "#\t ");
		if (empty($color)) {
			return static::getRandomColor($value);
		}
		return '#' . $color;
	}

	/**
	 * Sanitize value for use in css class name.
	 *
	 * @param string $value
	 */
	public static function sanitizeValue($value): string
	{
		return empty($value) ? '' : str_replace([' ', '-', '=', '+', '@', '*', '!', '#', '$', '%', '^', '&', '(', ')', '[', ']', '{', '}', ';', ':', "\\'", '"', ',', '<', '.', '>', '/', '?', '\\', '|'], '_', \App\Utils::sanitizeSpecialChars($value));
	}

	/**
	 * Get random color code.
	 *
	 * @param mixed $fromValue
	 *
	 * @return string
	 */
	public static function getRandomColor($fromValue = false)
	{
		if (false !== $fromValue) {
			$hash = md5('color' . $fromValue);
			return '#' . substr($hash, 0, 2) . substr($hash, 2, 2) . substr($hash, 4, 2);
		}
		return '#' . str_pad(dechex(random_int(0, 255)), 2, '0', STR_PAD_LEFT) . str_pad(dechex(random_int(0, 255)), 2, '0', STR_PAD_LEFT) . str_pad(dechex(random_int(0, 255)), 2, '0', STR_PAD_LEFT);
	}

	/**
	 * Function to update color for picklist value.
	 *
	 * @param int    $picklistId
	 * @param int    $picklistValueId
	 * @param string $color
	 */
	public static function updatePicklistValueColor($picklistId, $picklistValueId, $color)
	{
		$table = (new Db\Query())->select(['fieldname'])->from('vtiger_field')->where(['fieldid' => $picklistId])->scalar();
		if (empty($table)) {
			return;
		}
		Db::getInstance()->createCommand()->update('vtiger_' . $table, ['color' => ltrim($color, '#')], [Fields\Picklist::getPickListId($table) => $picklistValueId])->execute();
		Cache::clear();
		static::generate('picklist');
	}

	/**
	 * Function to add color column in picklist table.
	 *
	 * @param int $fieldId
	 */
	public static function addPicklistColorColumn($fieldId)
	{
		$table = (new Db\Query())->select(['fieldname'])->from('vtiger_field')->where(['fieldid' => $fieldId])->scalar();
		Db::getInstance()->createCommand()->addColumn('vtiger_' . $table, 'color', 'string(25)')->execute();
		Cache::clear();
	}

	/**
	 * Function gives fields based on the module.
	 *
	 * @param string $moduleName
	 *
	 * @return Vtiger_Field_Model[] - list of field models
	 */
	public static function getPicklistFieldsByModule($moduleName)
	{
		$types = ['picklist', 'multipicklist'];
		return \Vtiger_Module_Model::getInstance($moduleName)->getFieldsByType($types, true);
	}

	/**
	 * Update user color code and generate stylesheet file.
	 *
	 * @param int    $id
	 * @param string $color
	 */
	public static function updateUserColor($id, $color)
	{
		Db::getInstance()->createCommand()->update('vtiger_users', ['cal_color' => $color], ['id' => $id])->execute();
		static::generate('user');
	}

	/**
	 * Get all users colors.
	 *
	 * @return array
	 */
	public static function getAllUserColor()
	{
		return (new Db\Query())->select(['id', 'first' => 'first_name', 'last' => 'last_name', 'color' => 'cal_color'])->from('vtiger_users')->all();
	}

	/**
	 * Update group color code and generate stylesheet file.
	 *
	 * @param int    $id
	 * @param string $color
	 */
	public static function updateGroupColor($id, $color)
	{
		Db::getInstance()->createCommand()->update('vtiger_groups', ['color' => $color], ['groupid' => $id])->execute();
		static::generate('group');
	}

	/**
	 * Get all group color.
	 *
	 * @return array
	 */
	public static function getAllGroupColor()
	{
		return (new Db\Query())->select(['id' => 'groupid', 'groupname', 'color'])->from('vtiger_groups')->all();
	}

	/**
	 * Get all module color.
	 *
	 * @param bool $active
	 *
	 * @return array
	 */
	public static function getAllModuleColor($active = false)
	{
		$allModules = \vtlib\Functions::getAllModules(false, false, false, $active);
		$modules = [];
		foreach ($allModules as $tabid => $module) {
			$modules[] = [
				'id' => $tabid,
				'module' => $module['name'],
				'color' => '' !== $module['color'] ? '#' . $module['color'] : '',
				'active' => $module['coloractive'],
			];
		}
		return $modules;
	}

	/**
	 * Function to update color for module.
	 *
	 * @param array $params
	 * @param mixed $id
	 * @param mixed $color
	 */
	public static function updateModuleColor($id, $color)
	{
		Db::getInstance()->createCommand()->update('vtiger_tab', ['color' => ltrim($color, '#')], ['tabid' => $id])->execute();
		Cache::clear();
		static::generate('module');
	}

	/**
	 * Set module color active flag.
	 *
	 * @param int    $id
	 * @param bool   $active
	 * @param string $color
	 *
	 * @return string
	 */
	public static function activeModuleColor($id, $active, $color)
	{
		$color = empty($color) && $active ? static::getRandomColor() : $color;
		$set = ['coloractive' => (int) $active, 'color' => $active ? ltrim($color, '#') : null];
		Db::getInstance()->createCommand()->update('vtiger_tab', $set, ['tabid' => $id])->execute();
		Cache::clear();
		static::generate('module');
		return $color;
	}

	/**
	 * Get all filter colors.
	 *
	 * @param mixed $byFilterValue
	 *
	 * @return string[]
	 */
	public static function getAllFilterColors($byFilterValue = false)
	{
		if (Cache::has('getAllFilterColors', $byFilterValue)) {
			return Cache::get('getAllFilterColors', $byFilterValue);
		}
		$customViews = (new Db\Query())->select(['cvid', 'viewname', 'color'])->from('vtiger_customview')->all();
		$filterColors = [];
		$by = $byFilterValue ? 'viewname' : 'cvid';
		foreach ($customViews as $viewData) {
			$filterColors[$viewData[$by]] = $viewData['color'] ?: static::getRandomColor($viewData[$by]);
		}
		Cache::save('getAllFilterColors', $byFilterValue, $filterColors);
		return $filterColors;
	}

	/**
	 * Get contrast color.
	 *
	 * @param mixed $hexColor
	 *
	 * @return string
	 */
	public static function getContrast($hexColor)
	{
		$hexColor = ltrim(ltrim($hexColor), '#');
		return ((((hexdec(substr($hexColor, 0, 2)) * 299) + (hexdec(substr($hexColor, 2, 2)) * 587) + (hexdec(substr($hexColor, 4, 2)) * 114)) / 1000) >= 128) ? 'black' : 'white';
	}

	/**
	 * Update field color code and generate stylesheet file.
	 *
	 * @param int    $fieldId
	 * @param string $color
	 *
	 * @return bool
	 */
	public static function updateFieldColor($fieldId, $color): bool
	{
		$result = Db::getInstance()->createCommand()->update('vtiger_field', ['color' => $color], ['fieldid' => $fieldId])->execute();
		static::generate('field');
		if (!$result) {
			$result = $color === (new \App\Db\Query())->select(['color'])->from('vtiger_field')->where(['fieldid' => $fieldId])->scalar();
		}
		return $result;
	}

	/**
	 * Generate fields colors stylesheet.
	 */
	public static function generateFields()
	{
		$css = '';
		$query = (new \App\Db\Query())->select(['tabid', 'fieldname', 'color'])->from('vtiger_field')->where(['presence' => [0, 2]])->andWhere(['<>', 'color', '']);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			if (ltrim($row['color'], '#')) {
				$css .= '.flCT_' . Module::getModuleName($row['tabid']) . '_' . $row['fieldname'] . '{ color: ' . $row['color'] . ' !important; }' . PHP_EOL;
			}
		}
		file_put_contents(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/fields.css', $css, LOCK_EX);
	}
}
