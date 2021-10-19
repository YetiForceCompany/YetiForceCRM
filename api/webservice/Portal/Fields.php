<?php
/**
 * File with custom functionality for portal fields.
 *
 * @package Api
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Portal;

/**
 * Class with custom functionality for portal fields.
 */
class Fields
{
	/**
	 * Get default value for specified field object.
	 *
	 * @param \Vtiger_Field_Model  $fieldModel
	 * @param array                $fieldData
	 * @param \Api\Core\BaseAction $actionModel
	 *
	 * @return mixed
	 */
	public static function getDefaultValue(\Vtiger_Field_Model $fieldModel, array $fieldData, \Api\Core\BaseAction $actionModel)
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
