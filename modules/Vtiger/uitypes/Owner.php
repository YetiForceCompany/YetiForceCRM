<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Vtiger_Owner_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function getDBValue($value, $recordModel = false)
	{
		return empty($value) ? \App\User::getCurrentUserRealId() : (int) $value;
	}

	/** {@inheritdoc} */
	public function getDbConditionBuilderValue($value, string $operator)
	{
		$values = [];
		if (!\is_array($value)) {
			$value = $value ? explode('##', $value) : [];
		}
		foreach ($value as $val) {
			if (false !== strpos($val, ':') && \in_array($operator, ['e', 'n'])) {
				[$type, $val] = explode(':', $val);
				switch ($type) {
					case \App\PrivilegeUtil::MEMBER_TYPE_GROUPS:
						$val = parent::getDbConditionBuilderValue($val, $operator);
						break;
					case \App\PrivilegeUtil::MEMBER_TYPE_ROLES:
					case \App\PrivilegeUtil::MEMBER_TYPE_ROLE_AND_SUBORDINATES:
						if (!preg_match('/^H[1-9]+$/', $val)) {
							throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . \App\Utils::varExport($value), 406);
						}
						break;
					default:
						throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . \App\Utils::varExport($value), 406);
						break;
				}
				$values[] = "{$type}:{$val}";
			} else {
				$values[] = parent::getDbConditionBuilderValue($val, $operator);
			}
		}
		return implode('##', $values);
	}

	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		if (!is_numeric($value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$maximumLength = $this->getFieldModel()->get('maximumlength');
		if ($maximumLength) {
			$rangeValues = explode(',', $maximumLength);
			if (($rangeValues[1] ?? $rangeValues[0]) < $value || (isset($rangeValues[1]) ? $rangeValues[0] : 0) > $value) {
				throw new \App\Exceptions\Security('ERR_VALUE_IS_TOO_LONG||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
			}
		}
		$this->validate[$value] = true;
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (empty($value)) {
			return '';
		}
		$ownerName = \App\Fields\Owner::getLabel($value);
		if ($rawText) {
			return $ownerName;
		}
		if (\is_int($length)) {
			$ownerName = \App\TextUtils::textTruncate($ownerName, $length);
		}
		switch (\App\Fields\Owner::getType($value)) {
			case 'Users':
				if (!\App\User::isExists($value, false)) {
					$ownerName = '<span class="text-muted"><s>' . ($ownerName ?: '---') . '</s></span>';
				} else {
					$userModel = Users_Privileges_Model::getInstanceById($value);
					$userModel->setModule('Users');
					if ('Inactive' === $userModel->get('status')) {
						$ownerName = '<span class="redColor"><s>' . $ownerName . '</s></span>';
					} elseif (\App\User::getCurrentUserModel()->isAdmin()) {
						$detailViewUrl = 'index.php?module=Users&view=Detail&record=' . $value;
						$popoverRecordClass = 'class="js-popover-tooltip--record"';
					}
				}
				break;
			case 'Groups':
				if (\App\User::getCurrentUserModel()->isAdmin()) {
					$groupRecordModel = new Settings_Groups_Record_Model();
					$groupRecordModel->set('groupid', $value);
					$detailViewUrl = $groupRecordModel->getDetailViewUrl();
					$popoverRecordClass = '';
				}
				break;
			default:
				$ownerName = '<span class="redColor">---</span>';
				break;
		}
		if (isset($detailViewUrl)) {
			if (!empty($this->fullUrl)) {
				$detailViewUrl = Config\Main::$site_URL . $detailViewUrl;
			}
			return "<a $popoverRecordClass href=\"$detailViewUrl\"> $ownerName </a>";
		}
		return $ownerName;
	}

	/** {@inheritdoc} */
	public function getValueToExport($value, int $recordId)
	{
		return \App\Fields\Owner::getLabel($value);
	}

	/** {@inheritdoc} */
	public function getValueFromImport($value, $defaultValue = null)
	{
		$ownerId = \App\User::getUserIdByName(trim($value));
		if (empty($ownerId)) {
			$ownerId = \App\User::getUserIdByFullName(trim($value));
		}
		if (empty($ownerId)) {
			$ownerId = \App\Fields\Owner::getGroupId($value);
		}
		if (empty($ownerId) && null !== $defaultValue) {
			$ownerId = $defaultValue;
		}
		if (!empty($ownerId) && 'Users' === \App\Fields\Owner::getType($ownerId) && !\array_key_exists($ownerId, \App\Fields\Owner::getInstance($this->getFieldModel()->getModuleName())->getAccessibleUsers('', 'owner'))) {
			$ownerId = $defaultValue;
		}
		return $ownerId;
	}

	/** {@inheritdoc} */
	public function getRelatedListDisplayValue($value)
	{
		return $value;
	}

	/** {@inheritdoc} */
	public function getListSearchTemplateName()
	{
		return 'List/Field/Owner.tpl';
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/Owner.tpl';
	}

	/** {@inheritdoc} */
	public function isAjaxEditable()
	{
		$userPrivModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$roleModel = Settings_Roles_Record_Model::getInstanceById($userPrivModel->get('roleid'));
		if ($roleModel->get('changeowner')) {
			return true;
		}
		return false;
	}

	/** {@inheritdoc} */
	public function getAllowedColumnTypes()
	{
		return ['integer', 'smallint'];
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['e', 'n', 'y', 'ny', 'om', 'nom', 'ogr', 'ogu', 'wr', 'nwr', 'ef', 'nf'];
	}

	/** {@inheritdoc} */
	public function getOperatorTemplateName(string $operator = '')
	{
		return 'ConditionBuilder/Owner.tpl';
	}
}
