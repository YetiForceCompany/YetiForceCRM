<?php
/**
 * Base fields dependency handler file.
 *
 * @package		Handler
 *
 * @copyright	YetiForce S.A.
 * @license		YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author		Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Base fields dependency handler class.
 */
class Vtiger_FieldsDependency_Handler
{
	/**
	 * EditViewChangeValue handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function editViewChangeValue(App\EventHandler $eventHandler)
	{
		$return = [];
		$recordModel = $eventHandler->getRecordModel();
		$fieldsDependency = \App\FieldsDependency::getByRecordModel(\App\Request::_getByType('fromView'), $recordModel);
		if ($fieldsDependency['show']['frontend']) {
			$return['showFields'] = $fieldsDependency['show']['frontend'];
		}
		if ($fieldsDependency['hide']['frontend']) {
			$return['hideFields'] = $fieldsDependency['hide']['frontend'];
		}
		return $return;
	}

	/**
	 * EditViewPreSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function editViewPreSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$response = ['result' => true];
		$fieldsDependency = \App\FieldsDependency::getByRecordModel(\App\Request::_getByType('fromView'), $recordModel);
		if ($fieldsDependency['show']['mandatory']) {
			$mandatoryFields = [];
			foreach ($fieldsDependency['show']['mandatory'] as $fieldName) {
				if ('' === $recordModel->get($fieldName) && ($fieldModel = $recordModel->getField($fieldName)) && $fieldModel->isActiveField()) {
					$mandatoryFields[] = $fieldModel->getFullLabelTranslation();
				}
			}
			if ($mandatoryFields) {
				$response = [
					'result' => false,
					'hoverField' => reset($fieldsDependency['show']['mandatory']),
					'message' => \App\Language::translate('LBL_NOT_FILLED_MANDATORY_FIELDS') . ': <br /> - ' . implode('<br /> - ', $mandatoryFields),
				];
			}
		}
		return $response;
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
		if (\App\EventHandler::EDIT_VIEW_CHANGE_VALUE === $name) {
			[$recordModel,$view] = $params;
			if (empty($recordModel)) {
				$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			}
			return \App\FieldsDependency::getByRecordModel($view, $recordModel)['conditionsFields'];
		}
		return null;
	}
}
