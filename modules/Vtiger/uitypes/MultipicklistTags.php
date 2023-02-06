<?php

/**
 * UIType MultipicklistTags field file.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * UIType MultipicklistTags field class.
 */
class Vtiger_MultipicklistTags_UIType extends Vtiger_Multipicklist_UIType
{
	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value)
		|| 'preSaveValidation' === App\Request::_get('mode')
		|| isset($this->validate[\is_array($value) ? implode('|', $value) : $value])) {
			return;
		}
		if (\is_string($value)) {
			$value = explode(self::SEPARATOR, $value);
		}
		$picklistValues = App\Fields\Picklist::getValuesName($this->getFieldModel()->getName());
		if ($missingValues = \array_diff($value, $picklistValues)) {
			$moduleModel = Vtiger_Module_Model::getInstance($this->getFieldModel()->getModuleName());
			$fieldModel = Settings_Picklist_Field_Model::getInstance($this->getFieldModel()->getName(), $moduleModel);
			foreach ($missingValues as $missingValue) {
				try {
					$itemModel = $fieldModel->getItemModel();
					$itemModel->validateValue('name', $missingValue);
					$itemModel->set('name', $missingValue);
					$itemModel->save();
				} catch (\Throwable $th) {
					\App\Log::error($th->__toString());
				}
			}
		}
		parent::validate($value, $isUserFormat);
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/MultiPicklist.tpl';
	}

	/** {@inheritdoc} */
	public function getListSearchTemplateName()
	{
		return 'List/Field/MultiPicklist.tpl';
	}
}
