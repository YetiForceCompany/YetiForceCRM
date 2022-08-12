<?php
/**
 * File with custom functionality for fields.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\WebserviceStandard;

/**
 * Class with custom functionality for fields.
 */
class Fields
{
	/** @var array Webservice apps fields data. */
	private static $webserviceAppsFields = [];

	/**
	 * Load custom fields data for the webservice app.
	 *
	 * @param \Vtiger_Module_Model $moduleModel
	 * @param \Api\Core\BaseAction $actionModel
	 *
	 * @return void
	 */
	public static function loadWebserviceFields(\Vtiger_Module_Model $moduleModel, \Api\Core\BaseAction $actionModel): void
	{
		$fields = $moduleModel->getFields();
		foreach (self::getFields($actionModel->controller->app['id'], $moduleModel->getId()) as $fieldName => $fieldData) {
			if (isset($fields[$fieldName])) {
				self::loadWebserviceByField($fields[$fieldName], $actionModel, $fieldData);
			}
		}
	}

	/**
	 * Load custom field data for the webservice app.
	 *
	 * @param \Vtiger_Field_Model  $fieldModel
	 * @param \Api\Core\BaseAction $actionModel
	 * @param array|null           $fieldData
	 *
	 * @return void
	 */
	public static function loadWebserviceByField(\Vtiger_Field_Model $fieldModel, \Api\Core\BaseAction $actionModel, ?array $fieldData = null): void
	{
		if (null === $fieldData) {
			$fieldData = self::getFields($actionModel->controller->app['id'], $fieldModel->getModuleId())[$fieldModel->getName()] ?? [];
		}
		if ($fieldData) {
			if (1 !== $actionModel->getUserData('type') && !empty($fieldData['is_default'])) {
				$fieldModel->set('defaultvalue', self::getDefaultValue($fieldModel, $fieldData, $actionModel));
			}
			if (!empty($fieldData['visibility'])) {
				$fieldModel->set('displaytype', $fieldData['visibility']);
			}
		}
	}

	/**
	 * Get fields for current webservice app.
	 *
	 * @param int $serverId
	 * @param int $moduleId
	 *
	 * @return array
	 */
	public static function getFields(int $serverId, int $moduleId): array
	{
		$cacheKey = "{$serverId}_{$moduleId}";
		if (isset(self::$webserviceAppsFields[$cacheKey])) {
			return self::$webserviceAppsFields[$cacheKey];
		}
		if (\App\Cache::has('WebserviceAppsFields', $cacheKey)) {
			return \App\Cache::get('WebserviceAppsFields', $cacheKey);
		}
		self::$webserviceAppsFields[$cacheKey] = $response = (new \App\Db\Query())->select(['vtiger_field.fieldname', 'w_#__fields_server.*'])
			->from('w_#__fields_server')
			->where(['w_#__fields_server.serverid' => $serverId, 'vtiger_field.tabid' => $moduleId])
			->innerJoin('vtiger_field', 'w_#__fields_server.fieldid = vtiger_field.fieldid')
			->indexBy('fieldname')
			->all(\App\Db::getInstance('webservice')) ?: [];
		\App\Cache::save('WebserviceAppsFields', $cacheKey, $response);
		return $response;
	}

	/**
	 * Get default value for specified field object.
	 *
	 * @param \Vtiger_Field_Model  $fieldModel
	 * @param array                $fieldData
	 * @param \Api\Core\BaseAction $actionModel
	 *
	 * @return mixed
	 */
	private static function getDefaultValue(\Vtiger_Field_Model $fieldModel, array $fieldData, \Api\Core\BaseAction $actionModel)
	{
		$value = $fieldData['default_value'];
		$list = \App\Field::getCustomListForDefaultValue($fieldModel);
		if (isset($list[$value])) {
			switch ($value) {
				case 'loggedContact':
					$value = $actionModel->getUserData('crmid');
					break;
				case 'accountOnContact':
					$value = \App\Record::getParentRecord($actionModel->getUserCrmId());
					break;
				case 'accountLoggedContact':
					$value = $actionModel->controller->request->getHeader('x-parent-id');
					if (!$value) {
						$value = \App\Record::getParentRecord($actionModel->getUserCrmId());
					}
					break;
			}
		}
		return $value;
	}
}
