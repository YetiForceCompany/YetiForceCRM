<?php

/**
 * UIType Boolean Field Class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Users_Boolean_UIType extends Vtiger_Boolean_UIType
{
	/** {@inheritdoc} */
	public function getDBValue($value, $recordModel = false)
	{
		if ('is_admin' === $this->getFieldModel()->getName()) {
			if ('on' === $value || '1' === (string) $value) {
				return 'on';
			}
			return 'off';
		}
		return parent::getDBValue($value, $recordModel);
	}

	/** {@inheritdoc} */
	public function setValueFromRequest(App\Request $request, Vtiger_Record_Model $recordModel, $requestFieldName = false)
	{
		$currentModel = \App\User::getCurrentUserModel();
		if ('is_admin' === $this->getFieldModel()->getName() && (!$currentModel->isAdmin() || $currentModel->getId() === $recordModel->getId())) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName(), 406);
		}
		parent::setValueFromRequest($request, $recordModel, $requestFieldName);
	}
}
