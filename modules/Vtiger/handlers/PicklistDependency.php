<?php
/**
 * Picklist dependency handler file.
 *
 * @package		Handler
 *
 * @copyright	YetiForce S.A.
 * @license		YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author		Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author		Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Picklist dependency handler class.
 */
class Vtiger_PicklistDependency_Handler
{
	/**
	 * EditViewChangeValue handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 *
	 * @return array
	 */
	public function editViewChangeValue(App\EventHandler $eventHandler): array
	{
		$return = [];
		$recordModel = $eventHandler->getRecordModel();
		foreach (\App\Fields\Picklist::getDependencyForModule($eventHandler->getModuleName())['conditions'] as $fieldName => $values) {
			$availableValues = [];
			foreach ($values as $value => $conditions) {
				if (\App\Condition::checkConditions($conditions, $recordModel)) {
					$availableValues[] = $value;
				}
			}
			$return['changeOptions'][$fieldName] = $availableValues;
		}

		return $return;
	}

	/**
	 * Get variables for the current event.
	 *
	 * @param string $name
	 * @param array  $params
	 * @param string $moduleName
	 *
	 * @return array|null
	 */
	public function vars(string $name, array $params, string $moduleName): ?array
	{
		return \App\EventHandler::EDIT_VIEW_CHANGE_VALUE === $name ? \App\Fields\Picklist::getDependencyForModule($moduleName)['listener'] : null;
	}
}
