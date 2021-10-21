<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Vtiger_Reference_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function getDBValue($value, $recordModel = false)
	{
		if (empty($value)) {
			$value = 0;
		}
		return (int) $value;
	}

	/** {@inheritdoc} */
	public function getDbConditionBuilderValue($value, string $operator)
	{
		return \App\Purifier::decodeHtml($value);
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

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value.
	 *
	 * @param int $value
	 *
	 * @return Vtiger_Module_Model|null
	 */
	public function getReferenceModule($value): ?Vtiger_Module_Model
	{
		$fieldModel = $this->getFieldModel();
		$referenceModuleList = $fieldModel->getReferenceList();
		$referenceEntityType = \App\Record::getType($value);
		if (!empty($referenceModuleList) && \in_array($referenceEntityType, $referenceModuleList)) {
			return Vtiger_Module_Model::getInstance($referenceEntityType);
		}
		if (!empty($referenceModuleList) && \in_array('Users', $referenceModuleList)) {
			return Vtiger_Module_Model::getInstance('Users');
		}
		return null;
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (empty($value) || !($referenceModule = $this->getReferenceModule($value))) {
			return '';
		}
		$referenceModuleName = $referenceModule->getName();

		if ('Users' === $referenceModuleName || 'Groups' === $referenceModuleName) {
			return \App\Fields\Owner::getLabel($value);
		}
		if (!\App\Record::isExists($value)) {
			return '';
		}
		$label = \App\Record::getLabel($value, $rawText);
		if ($rawText || ($value && !\App\Privilege::isPermitted($referenceModuleName, 'DetailView', $value))) {
			return $label;
		}
		if (\is_int($length)) {
			$label = \App\TextParser::textTruncate($label, $length);
		} elseif (true !== $length) {
			$label = App\TextParser::textTruncate($label, \App\Config::main('href_max_length'));
		}
		if ('Active' !== \App\Record::getState($value)) {
			$label = '<s>' . $label . '</s>';
		}
		$url = "index.php?module={$referenceModuleName}&view={$referenceModule->getDetailViewName()}&record={$value}";
		if (!empty($this->fullUrl)) {
			$url = Config\Main::$site_URL . $url;
		}
		return "<a class='modCT_$referenceModuleName showReferenceTooltip js-popover-tooltip--record' href='$url'>$label</a>";
	}

	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		if (empty($value)) {
			return '';
		}
		if (($referenceModule = $this->getReferenceModule($value)) && ('Users' === $referenceModule->getName() || 'Groups' === $referenceModule->getName())) {
			return \App\Fields\Owner::getLabel($value);
		}
		return \App\Record::getLabel($value);
	}

	/** {@inheritdoc} */
	public function getEditViewValue($value, $recordModel = false)
	{
		return (int) $value;
	}

	/** {@inheritdoc} */
	public function getApiDisplayValue($value, Vtiger_Record_Model $recordModel)
	{
		if (empty($value) || !($referenceModule = $this->getReferenceModule($value))) {
			return '';
		}
		$referenceModuleName = $referenceModule->getName();
		if ('Users' === $referenceModuleName || 'Groups' === $referenceModuleName) {
			return \App\Fields\Owner::getLabel($value);
		}
		if (!\App\Record::isExists($value)) {
			return '';
		}
		return [
			'value' => \App\Record::getLabel($value, true),
			'raw' => $value,
			'referenceModule' => $referenceModuleName,
			'state' => \App\Record::getState($value),
			'isPermitted' => \App\Privilege::isPermitted($referenceModuleName, 'DetailView', $value),
		];
	}

	/** {@inheritdoc} */
	public function getApiEditValue($value)
	{
		if (empty($value) || !($referenceModule = $this->getReferenceModule($value))) {
			return ['value' => ''];
		}
		$referenceModuleName = $referenceModule->getName();
		if ('Users' === $referenceModuleName || 'Groups' === $referenceModuleName) {
			return [
				'value' => \App\Fields\Owner::getLabel($value),
				'raw' => $value,
				'referenceModule' => $referenceModuleName,
			];
		}
		return !\App\Record::isExists($value) ? ['value' => ''] : [
			'value' => \App\Record::getLabel($value, true),
			'raw' => $value,
			'referenceModule' => $referenceModuleName,
		];
	}

	/** {@inheritdoc} */
	public function getValueToExport($value, int $recordId)
	{
		$value = trim($value);
		if (!empty($value)) {
			$recordModule = \App\Record::getType($value);
			$displayValueArray = \App\Record::computeLabels($recordModule, $value);
			if (!empty($displayValueArray)) {
				foreach ($displayValueArray as $v) {
					$displayValue = $v;
				}
			}
			if (!empty($recordModule) && !empty($displayValue)) {
				$value = $recordModule . '::::' . $displayValue;
			} else {
				$value = '';
			}
		} else {
			$value = '';
		}
		return $value;
	}

	/** {@inheritdoc} */
	public function getValueFromImport($value, $defaultValue = null)
	{
		if (empty($value)) {
			return '';
		}
		$fieldValueDetails = [];
		$referenceModuleName = '';
		$entityId = false;
		if (false !== strpos($value, '::::')) {
			$fieldValueDetails = explode('::::', $value);
		} elseif (false !== strpos($value, ':::')) {
			$fieldValueDetails = explode(':::', $value);
		}
		if ($fieldValueDetails && \count($fieldValueDetails) > 1) {
			$referenceModuleName = trim($fieldValueDetails[0]);
			$entityLabel = trim($fieldValueDetails[1]);
			if (\App\Module::isModuleActive($referenceModuleName)) {
				$entityId = \App\Record::getCrmIdByLabel($referenceModuleName, App\Purifier::decodeHtml($entityLabel));
			} else {
				$referenceModuleName = $defaultValue;
				if (false !== strpos($referenceModuleName, '::')) {
					[$referenceModuleName, ] = explode('::', $referenceModuleName);
				}
				$referencedModules = $this->getFieldModel()->getReferenceList();
				if ($referenceModuleName && \in_array($referenceModuleName, $referencedModules)) {
					$entityId = \App\Record::getCrmIdByLabel($referenceModuleName, $entityLabel);
				}
			}
		} else {
			$entityLabel = $value;
			$referencedModules = $this->getFieldModel()->getReferenceList();
			if (!empty($defaultValue) && false !== strpos($defaultValue, '::')) {
				[$refModule, $refFieldName] = explode('::', $defaultValue);
				if (\in_array($refModule, $referencedModules)) {
					$referenceModuleName = $refModule;
					$queryGenerator = new \App\QueryGenerator($refModule);
					$queryGenerator->permissions = false;
					$queryGenerator->setFields(['id'])->addCondition($refFieldName, $value, 'e');
					$entityId = $queryGenerator->createQuery()->scalar();
				}
			}
			foreach ($referencedModules as $referenceModule) {
				$referenceModuleName = $referenceModule;
				if ('Users' === $referenceModule) {
					$referenceEntityId = \App\User::getUserIdByFullName(trim($entityLabel));
				} elseif ('Currency' === $referenceModule) {
					$referenceEntityId = \App\Fields\Currency::getCurrencyIdByName($entityLabel);
				} else {
					$referenceEntityId = \App\Record::getCrmIdByLabel($referenceModule, App\Purifier::decodeHtml($entityLabel));
				}
				if ($referenceEntityId) {
					$entityId = $referenceEntityId;
					break;
				}
			}
		}
		if (\App\Config::module('Import', 'CREATE_REFERENCE_RECORD') && empty($entityId) && !empty($referenceModuleName) && \App\Privilege::isPermitted($referenceModuleName, 'CreateView')) {
			try {
				$recordModel = Vtiger_Record_Model::getCleanInstance($referenceModuleName);
				$moduleModel = $recordModel->getModule();
				$mandatoryFields = array_keys($moduleModel->getMandatoryFieldModels());
				$entityNameFields = $moduleModel->getNameFields();
				$save = $entityId = false;
				foreach ($entityNameFields as $entityNameField) {
					if (\in_array($entityNameField, $mandatoryFields)) {
						$recordModel->set($entityNameField, $entityLabel);
						$save = true;
					}
				}
				if ($save) {
					if (!\App\Config::module('Import', 'SAVE_BY_HANDLERS')) {
						$recordModel->setHandlerExceptions(['disableHandlers' => true]);
					}
					$recordModel->save();
					$entityId = $recordModel->getId();
					if ($entityId) {
						\App\Record::updateLabel($referenceModuleName, $recordModel->getId());
					}
				}
			} catch (\Exception $e) {
				$entityId = false;
			}
		}
		return $entityId;
	}

	/** {@inheritdoc} */
	public function getListSearchTemplateName()
	{
		$fieldModel = $this->getFieldModel();
		$fieldName = $fieldModel->getName();
		if ('modifiedby' === $fieldName) {
			return 'List/Field/Owner.tpl';
		}
		if (App\Config::performance('SEARCH_REFERENCE_BY_AJAX')) {
			return 'List/Field/Reference.tpl';
		}
		return parent::getListSearchTemplateName();
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/Reference.tpl';
	}

	/** {@inheritdoc} */
	public function getAllowedColumnTypes()
	{
		return ['bigint', 'integer', 'smallint'];
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['e', 'n', 's', 'ew', 'c', 'k', 'y', 'ny'];
	}

	/** {@inheritdoc} */
	public function delete()
	{
		$db = \App\Db::getInstance();
		$fieldModel = $this->getFieldModel();
		$reference = $fieldModel->getReferenceList();

		$db->createCommand()->delete('vtiger_relatedlists', [
			'field_name' => $fieldModel->getName(),
			'related_tabid' => $fieldModel->getModuleId(),
			'tabid' => array_map('App\Module::getModuleId', $reference),
		])->execute();

		foreach ($reference as $module) {
			\App\Relation::clearCacheByModule($module);
		}
		\App\Cache::delete('HierarchyByRelation', '');

		parent::delete();
	}
}
