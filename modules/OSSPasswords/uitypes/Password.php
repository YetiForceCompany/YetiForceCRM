<?php

/**
 * OSSPasswords password uitype class.
 *
 * @package UIType
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */

/**
 * Class OSSPasswords_Password_UIType.
 */
class OSSPasswords_Password_UIType extends Vtiger_Password_UIType
{
	/** {@inheritdoc} */
	public function isActiveSearchView()
	{
		return false;
	}

	/** {@inheritdoc} */
	public function getListViewDisplayValue($value, $record = false, $recordModel = false, $rawText = false)
	{
		return $rawText ? parent::getListViewDisplayValue($value, $record, $recordModel, $rawText) : str_repeat('*', 10);
	}

	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		$recordModel = Vtiger_Record_Model::getCleanInstance('OSSPasswords');
		return $recordModel->checkPassword($value)['error'];
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/Base.tpl';
	}

	/** {@inheritdoc} */
	public function convertToSave($value, Vtiger_Record_Model $recordModel)
	{
		return $value;
	}

	/** {@inheritdoc} */
	public function setValueFromRequest(App\Request $request, Vtiger_Record_Model $recordModel, $requestFieldName = false)
	{
		$fieldName = $this->getFieldModel()->getFieldName();
		if (!$requestFieldName) {
			$requestFieldName = $fieldName;
		}
		$value = $request->getRaw($requestFieldName);
		$this->validate($value, true);
		$recordModel->set($fieldName, $this->getDBValue($value, $recordModel));
	}
}
