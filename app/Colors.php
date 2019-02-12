<?php
/**
 * Colors stylesheet generator class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace App;

/**
 * Custom colors stylesheet file generator.
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
			default:
				static::generateOwners();
				static::generateModules();
				static::generatePicklists();
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
			if (ltrim($item['color'], '#')) {
				$css .= '.ownerCBg_' . $item['id'] . ' { background: ' . $item['color'] . ' !important; font-weight: 500 !important; color: ' . static::getContrast($item['color']) . ' !important;}' . PHP_EOL;
				$css .= '.ownerCT_' . $item['id'] . ' { color: ' . $item['color'] . ' !important; }' . PHP_EOL;
				$css .= '.ownerCBr_' . $item['id'] . ' { border-color: ' . $item['color'] . ' !important; }' . PHP_EOL;
				$colors[$item['id']] = $item['color'];
			}
		}
		foreach (static::getAllGroupColor() as $item) {
			if (ltrim($item['color'], '#')) {
				$css .= '.ownerCBg_' . $item['id'] . ' { background: ' . $item['color'] . ' !important; font-weight: 500 !important; color: ' . static::getContrast($item['color']) . ' !important;}' . PHP_EOL;
				$css .= '.ownerCT_' . $item['id'] . ' { color: ' . $item['color'] . ' !important; }' . PHP_EOL;
				$css .= '.ownerCBr_' . $item['id'] . ' { border-color: ' . $item['color'] . ' !important; }' . PHP_EOL;
				$colors[$item['id']] = $item['color'];
			}
		}
		file_put_contents(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/owners.css', $css);
		file_put_contents(ROOT_DIRECTORY . '/user_privileges/owners_colors.php', '<?php return ' . Utils::varExport($colors) . ';');
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
		file_put_contents(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/modules.css', $css);
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
				if ($values) {
					$firstRow = reset($values);
					if (array_key_exists('color', $firstRow)) {
						foreach ($values as $item) {
							if (ltrim($item['color'], '#')) {
								if (strpos($item['color'], '#') === false) {
									$item['color'] = '#' . $item['color'];
								}
								$contrastColor = static::getContrast($item['color']);
								$css .= '.picklistCBr_' . $module['tabname'] . '_' . static::sanitizeValue($field->getName()) . '_' . static::sanitizeValue($item['picklistValue']) . ' { border-color: ' . $item['color'] . ' !important; }' . PHP_EOL;
								$css .= '.picklistCBg_' . $module['tabname'] . '_' . static::sanitizeValue($field->getName()) . '_' . static::sanitizeValue($item['picklistValue']) . ' { background: ' . $item['color'] . ' !important; font-weight: 500 !important; color: ' . $contrastColor . ' !important;}' . PHP_EOL;
								$css .= '.picklistCT_' . $module['tabname'] . '_' . static::sanitizeValue($field->getName()) . '_' . static::sanitizeValue($item['picklistValue']) . ' { color: ' . $item['color'] . ' !important; }' . PHP_EOL;
								$css .= '.picklistLb_' . $module['tabname'] . '_' . static::sanitizeValue($field->getName()) . '_' . static::sanitizeValue($item['picklistValue']) . ' { background: ' . $item['color'] . '; color: ' . $contrastColor . ' !important; font-weight: 500 !important; padding: 2px 7px 3px 7px;}' . PHP_EOL;
							}
						}
					}
				}
			}
		}
		file_put_contents(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/picklists.css', $css);
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
	public static function sanitizeValue($value)
	{
		return str_replace([' ', '-', '=', '+', '@', '*', '!', '#', '$', '%', '^', '&', '(', ')', '[', ']', '{', '}', ';', ':', "\'", '"', ',', '<', '.', '>', '/', '?', '\\', '|'], '_', $value);
	}

	/**
	 * Get random color code.
	 *
	 * @return string
	 */
	public static function getRandomColor($fromValue = false)
	{
		if ($fromValue !== false) {
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
		$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
		$moduleBlockFields = \Vtiger_Field_Model::getAllForModule($moduleModel);
		$type = ['picklist', 'multipicklist'];
		$fieldList = [];
		foreach ($moduleBlockFields as $moduleFields) {
			foreach ($moduleFields as $moduleField) {
				$block = $moduleField->get('block');
				if (!$block || !in_array($moduleField->getFieldDataType(), $type)) {
					continue;
				}
				$fieldList[$moduleField->get('name')] = $moduleField;
			}
		}
		return $fieldList;
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
				'color' => $module['color'] !== '' ? '#' . $module['color'] : '',
				'active' => $module['coloractive'],
			];
		}
		return $modules;
	}

	/**
	 * Function to update color for module.
	 *
	 * @param array $params
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
			$filterColors[$viewData[$by]] = $viewData['color'] ? $viewData['color'] : static::getRandomColor($viewData[$by]);
		}
		Cache::save('getAllFilterColors', $byFilterValue, $filterColors);
		return $filterColors;
	}

	/**
	 * Get contrast color.
	 *
	 * @param $hexcolor
	 *
	 * @return string
	 */
	public static function getContrast($hexcolor)
	{
		$contrastRatio = 1.9; // higher number = more black color
		return hexdec($hexcolor) > 0xffffff / $contrastRatio ? 'black' : 'white';
	}
}
