<?php
/**
 * File with custom functionality for fields.
 *
 * @package Api
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\RestApi;

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
	 * @param \Vtiger_Field_Model[] $fields
	 * @param \Api\Core\BaseAction  $actionModel
	 *
	 * @return void
	 */
	public static function loadWebserviceFields(array $fields, \Api\Core\BaseAction $actionModel): void
	{
		foreach (self::getFields($actionModel->controller->app['id']) as $fieldName => $fieldData) {
			if (isset($fields[$fieldName])) {
				self::loadWebserviceByField($fields[$fieldName], $actionModel, $fieldData);
			} else {
				\App\Log::warning('No field found: ' . $fieldName);
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
			$fieldData = self::getFields($actionModel->controller->app['id'])[$fieldModel->getName()] ?? [];
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
	 *
	 * @return array
	 */
	public static function getFields(int $serverId): array
	{
		if (isset(self::$webserviceAppsFields[$serverId])) {
			return self::$webserviceAppsFields[$serverId];
		}
		if (\App\Cache::has('WebserviceAppsFields', $serverId)) {
			return \App\Cache::get('WebserviceAppsFields', $serverId);
		}
		self::$webserviceAppsFields[$serverId] = $response = (new \App\Db\Query())->select(['vtiger_field.fieldname', 'w_#__fields_server.*'])
			->from('w_#__fields_server')
			->where(['w_#__fields_server.serverid' => $serverId])
			->innerJoin('vtiger_field', 'w_#__fields_server.fieldid = vtiger_field.fieldid')
			->indexBy('fieldname')
			->all(\App\Db::getInstance('webservice')) ?: [];
		\App\Cache::save('WebserviceAppsFields', $serverId, $response);
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
