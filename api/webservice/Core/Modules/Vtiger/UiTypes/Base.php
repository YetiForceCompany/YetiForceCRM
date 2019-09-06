<?php
/**
 * UIType Base Field Class.
 *
 * @package   Api
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Api\Core\Modules\Vtiger\UiTypes;

/**
 * UIType Base Field Class.
 */
class Base extends \Vtiger_Base_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public static function getInstanceFromField($fieldModel)
	{
		$uiTypeClassSuffix = ucfirst($fieldModel->getFieldDataType());
		$instance = false;
		foreach ([$fieldModel->getModuleName(), 'Vtiger'] as $moduleName) {
			$className = "\\Api\\Core\\Modules\\{$moduleName}\\UiTypes\\{$uiTypeClassSuffix}";
			if (class_exists($className)) {
				$instance = new $className();
				$instance->set('field', $fieldModel);
				break;
			}
		}
		if (!$instance) {
			$instance = \Vtiger_Base_UIType::getInstanceFromField($fieldModel);
		}
		return $instance;
	}
}
