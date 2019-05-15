<?php

/*
 * MultiDomain class
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Rafal Pospiech <r.pospiech@yetiforce.com>
*/

class Vtiger_MultiDomain_UIType extends Vtiger_Multipicklist_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		$hashValue = is_array($value) ? implode('|', $value) : $value;
		if (isset($this->validate[$hashValue]) || empty($value)) {
			return;
		}
		if (is_string($value)) {
			$value = explode(' |##| ', $value);
		}
		if (!is_array($value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$fieldModel = $this->getFieldModel();
		$fieldParams = $fieldModel->getFieldParams();
		foreach ($value as $item) {
			if (!is_string($item)) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
			}
			if ($item != strip_tags($item)) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
			}
			if (!preg_match('/(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]/ui', $item)) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
			}
		}
		$this->validate[$hashValue] = true;
	}

	/**
	 * Check if value is unique.
	 *
	 * @param mixed               $value
	 * @param int                 $recordId
	 * @param \Vtiger_Field_Model $fieldModel
	 *
	 * @return bool
	 */
	public function validateUnique($value, int $recordId, Vtiger_Field_Model $fieldModel)
	{
		if ($recordId) {
			if (is_string($value)) {
				$value = explode(' |##| ', $value);
			}
			foreach ($value as $domain) {
				$crmIds = \App\Fields\MultiDomain::getCrmIds($domain, $fieldModel);
				$count = count($crmIds);
				if (!($count === 0 || ($count === 1 && $crmIds[0] === $recordId))) {
					throw new \App\Exceptions\AppException(\App\Language::translateArgs('ERR_DUPLICATES_VALUES_FOUND', 'Other.Exceptions', $domain), 513);
				}
			}
		}
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTemplateName()
	{
		return 'Edit/Field/MultiDomain.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getListSearchTemplateName()
	{
		return 'List/Field/MultiDomain.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAllowedColumnTypes()
	{
		return ['text'];
	}
}
