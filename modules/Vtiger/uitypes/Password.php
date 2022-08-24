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

class Vtiger_Password_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	protected $search = false;

	/** {@inheritdoc} */
	protected $sortable = false;

	/** {@inheritdoc} */
	public function setValueFromRequest(App\Request $request, Vtiger_Record_Model $recordModel, $requestFieldName = false)
	{
		$fieldName = $this->getFieldModel()->getName();
		if (!$requestFieldName) {
			$requestFieldName = $fieldName;
		}

		$value = $request->getRaw($requestFieldName);
		$this->validate($value, true);
		$recordModel->set($fieldName, $this->getDBValue($value, $recordModel));
	}

	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		$maximumLength = $this->getFieldModel()->getMaxValue();
		if (!$isUserFormat && \App\Encryption::getInstance()->isActive()) {
			$maximumLength = $this->getFieldModel()->getDbValueLength();
		}
		if ($maximumLength && App\TextUtils::getTextLength($value) > $maximumLength) {
			throw new \App\Exceptions\Security('ERR_VALUE_IS_TOO_LONG||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$this->validate[$value] = true;
	}

	/** {@inheritdoc} */
	public function getDBValue($value, $recordModel = false)
	{
		$encryptInstance = \App\Encryption::getInstance($this->getFieldModel()->getModuleId());
		if ($encryptInstance->isActive()) {
			$value = $encryptInstance->encrypt($value);
		}
		return $value;
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$value = '******';
		if (!$rawText && $recordModel && $recordModel->isViewable() && !\App\Encryption::getInstance($this->getFieldModel()->getModuleId())->isRunning()) {
			$moduleName = $recordModel->getModuleName();
			$fieldName = $this->getFieldModel()->getName();
			$id = $recordModel->getId();
			$uniqueId = \App\Layout::getUniqueId("PWD-{$fieldName}");
			$value = "<span class=\"text-muted u-cursor-pointer js-no-link js-copy-clipboard\" id=\"{$uniqueId}\" data-url=\"index.php?module={$moduleName}&action=Password&mode=getPwd&field={$fieldName}&record={$id}\" title=\"" . \App\Language::translate('LBL_PWD_CLIPBOARD_DBCLICK', $moduleName) . "\">{$value}</span>";
		}
		return $value;
	}

	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		return $this->getDisplayValue($value, false, false, true);
	}

	/** {@inheritdoc} */
	public function isDuplicable(): bool
	{
		return false;
	}

	/** {@inheritdoc} */
	public function isWritable(): bool
	{
		return parent::isWritable() && !\App\Encryption::getInstance($this->getFieldModel()->getModuleId())->isRunning();
	}

	/** {@inheritdoc} */
	public function getValueToExport($value, int $recordId)
	{
		return $this->getPwd($value);
	}

	/** {@inheritdoc} */
	public function getValueFromImport($value, $defaultValue = null)
	{
		return $this->getDBValue($value);
	}

	/** {@inheritdoc} */
	public function getHistoryDisplayValue($value, Vtiger_Record_Model $recordModel, $rawText = false)
	{
		return $this->getDisplayValue($value, false, false, true);
	}

	/** {@inheritdoc} */
	public function getRawValue($value)
	{
		return '******';
	}

	/** {@inheritdoc} */
	public function getApiDisplayValue($value, Vtiger_Record_Model $recordModel, array $params = [])
	{
		if (!empty($params['showHiddenData'])) {
			$value = $this->getPwd($value);
			(new App\EventHandler())->setRecordModel($recordModel)->setModuleName($recordModel->getModuleName())->trigger('EntityAfterShowHiddenData');
		} else {
			$value = parent::getApiDisplayValue($value, $recordModel, $params);
		}
		return $value;
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return [];
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/Password.tpl';
	}

	/**
	 * Check password.
	 *
	 * @param string $value
	 *
	 * @return bool|string[]
	 */
	public function checkPwd(string $value)
	{
		$conf = Settings_Password_Record_Model::getUserPassConfig();
		$fieldConfig = $this->getFieldModel()->getFieldParams()['validate'] ?? [];
		$notice = [];
		if (\in_array('config', $fieldConfig)) {
			$moduleName = 'Settings:Password';
			if (\strlen($value) > $conf['max_length']) {
				$notice[] = \App\Language::translate('Maximum password length', $moduleName) . ' ' . $conf['max_length'] . ' ' . \App\Language::translate('characters', $moduleName);
			}
			if (\strlen($value) < $conf['min_length']) {
				$notice[] = \App\Language::translate('Minimum password length', $moduleName) . ' ' . $conf['min_length'] . ' ' . \App\Language::translate('characters', $moduleName);
			}
			if ('true' === $conf['numbers'] && !preg_match('#[0-9]+#', $value)) {
				$notice[] = \App\Language::translate('Password should contain numbers', $moduleName);
			}
			if ('true' === $conf['big_letters'] && !preg_match('#[A-Z]+#', $value)) {
				$notice[] = \App\Language::translate('Uppercase letters from A to Z', $moduleName);
			}
			if ('true' === $conf['small_letters'] && !preg_match('#[a-z]+#', $value)) {
				$notice[] = \App\Language::translate('Lowercase letters a to z', $moduleName);
			}
			if ('true' === $conf['special'] && !preg_match('~[!"#$%&\'()*+,-./:;<=>?@[\]^_{|}]~', $value)) {
				$notice[] = \App\Language::translate('Password should contain special characters', $moduleName);
			}
		}
		if (\in_array('pwned', $fieldConfig) && ($passStatus = App\Extension\PwnedPassword::check($value)) && !$passStatus['status']) {
			$notice[] = $passStatus['message'];
		}
		return $notice ?: true;
	}

	/**
	 * Gets raw password.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function getPwd(string $value)
	{
		$encryptInstance = \App\Encryption::getInstance($this->getFieldModel()->getModuleId());
		if ($encryptInstance->isActive()) {
			$value = $encryptInstance->decrypt($value);
		}
		return $value;
	}

	/**
	 * Get actions urls.
	 *
	 * @return array
	 */
	public function getActionsUrl(): array
	{
		$fieldModel = $this->getFieldModel();
		return [
			'generate' => "index.php?module={$fieldModel->getModuleName()}&action=Password&mode=generatePwd&field={$fieldModel->getName()}",
			'validate' => "index.php?module={$fieldModel->getModuleName()}&action=Password&mode=validatePwd&field={$fieldModel->getName()}",
		];
	}
}
