<?php

/**
 * UIType Multi Depend Field Class.
 *
 * @package   UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Class Users_MultiDependField_UIType.
 */
class Users_MultiDependField_UIType extends Vtiger_MultiDependField_UIType
{
	/** {@inheritdoc} */
	public function getFieldsModel()
	{
		if (!isset($this->fieldsModels)) {
			$this->fieldsModels = [];
			$fieldModel = $this->getFieldModel();
			$this->fieldsModels['activitytype'] = Vtiger_Module_Model::getInstance('Calendar')->getFieldByName('activitytype');
			$fieldName = 'duration';
			$params = ['uitype' => 16, 'name' => $fieldName, 'displaytype' => 1, 'typeofdata' => 'V~M', 'presence' => 0, 'picklistValues' => [5 => 5, 10 => 10, 30 => 30, 60 => 60, 120 => 120]];
			$this->fieldsModels[$fieldName] = Settings_Vtiger_Field_Model::init($fieldModel->getModuleName(), $params);
			$this->fieldsModels[$fieldName]->setModule($fieldModel->getModule());
		}
		return $this->fieldsModels;
	}

	/** {@inheritdoc} */
	public function getDefaultValue()
	{
		$defaultValue = $this->getFieldModel()->get('defaultvalue');
		if (!$defaultValue && 'othereventduration' === $this->getFieldModel()->getName()) {
			$picklist = \App\Fields\Picklist::getValuesName('activitytype');
			foreach ($picklist as $label) {
				$value[] = ['activitytype' => $label, 'duration' => 60];
			}
			$defaultValue = \App\Json::encode($value);
		}
		return $defaultValue;
	}
}
