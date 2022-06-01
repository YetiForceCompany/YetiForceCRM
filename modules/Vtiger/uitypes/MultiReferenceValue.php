<?php

/**
 * UIType MultiReferenceValue Field Class.
 *
 * @package   UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_MultiReferenceValue_UIType extends Vtiger_Base_UIType
{
	const COMMA = '|#|';

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (null === $value) {
			return '';
		}
		$value = str_replace(self::COMMA, ', ', $value);
		$value = substr($value, 1);
		$value = substr($value, 0, -2);
		if (\is_int($length)) {
			$value = \App\TextUtils::textTruncate($value, $length);
		}
		return \App\Purifier::encodeHtml($value);
	}

	/** {@inheritdoc} */
	public function getListViewDisplayValue($value, $record = false, $recordModel = false, $rawText = false)
	{
		$field = $this->getFieldModel();
		$params = $field->getFieldParams();
		$fieldInfo = \App\Field::getFieldInfo($params['field']);
		if (\in_array($fieldInfo['uitype'], [15, 16, 33])) {
			$relModuleName = \App\Module::getModuleName($fieldInfo['tabid']);
			$values = array_filter(explode(self::COMMA, $value));
			foreach ($values as &$value) {
				$value = \App\Language::translate($value, $relModuleName);
			}
			$values = implode(', ', $values);
		} else {
			return $this->getDisplayValue($value, $record, $recordModel, $rawText, $field->get('maxlengthtext'));
		}
		return \App\Purifier::encodeHtml(\App\TextUtils::textTruncate($values, $field->get('maxlengthtext')));
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/MultiReferenceValue.tpl';
	}

	/** {@inheritdoc} */
	public function getListSearchTemplateName()
	{
		return 'List/Field/MultiReferenceValue.tpl';
	}

	/**
	 * Function to get all the available picklist values for the current field.
	 *
	 * @return array List of picklist values if the field is of type MultiReferenceValue
	 */
	public function getPicklistValues()
	{
		if ($picklistValues = $this->get('picklistValues')) {
			return $picklistValues;
		}
		$params = $this->getFieldModel()->getFieldParams();
		$fieldInfo = \App\Field::getFieldInfo($params['field']);
		$queryGenerator = new \App\QueryGenerator($params['module']);
		if ('-' !== $params['filterField']) {
			$queryGenerator->addCondition($params['filterField'], $params['filterValue'], 'e');
		}
		$queryGenerator->setFields([$fieldInfo['fieldname']]);
		$values = $queryGenerator->createQuery()->distinct()->indexBy($fieldInfo['column'] ?? null)->column();
		$this->set('picklistValues', $values);
		return $values;
	}

	/**
	 * Loading the list of multireference fields.
	 *
	 * @param string $sourceModule      Source module name
	 * @param string $destinationModule Destination module name
	 *
	 * @return int[]
	 */
	public static function getFieldsByModules(string $sourceModule, string $destinationModule)
	{
		$cacheKey = "$sourceModule,$destinationModule";
		if (App\Cache::has('mrvfbm', $cacheKey)) {
			return App\Cache::get('mrvfbm', $cacheKey);
		}
		$fields = (new \App\Db\Query())
			->select(['fieldid'])
			->from('vtiger_field')
			->where(['and',
				['<>', 'presence', 1], ['uitype' => 305],
				['like', 'fieldparams', '{"module":"' . $destinationModule . '"%', false], ['tabid' => App\Module::getModuleId($sourceModule)]
			])->column();
		App\Cache::get('mrvfbm', $cacheKey, $fields, App\Cache::LONG);

		return $fields;
	}

	/**
	 * Get MultiReference modules.
	 *
	 * @param string $moduleName
	 *
	 * @return array
	 */
	public static function getMultiReferenceModules(string $moduleName)
	{
		if (App\Cache::has('getMultiReferenceModules', $moduleName)) {
			return App\Cache::get('getMultiReferenceModules', $moduleName);
		}
		$data = (new \App\Db\Query())->select(['tabid'])->from('vtiger_field')->where(['uitype' => 305])->andWhere(['<>', 'presence', 1])
			->andWhere(['like', 'fieldparams', '{"module":"' . $moduleName . '"%', false])->distinct()->column();
		App\Cache::save('getMultiReferenceModules', $moduleName, $data, App\Cache::LONG);

		return $data;
	}

	/**
	 * Set record to cron.
	 *
	 * @param string $moduleName
	 * @param string $destModule
	 * @param int    $recordId
	 * @param int    $type
	 */
	public static function setRecordToCron($moduleName, $destModule, $recordId, $type = 1)
	{
		$data = ['source_module' => $moduleName, 'dest_module' => $destModule, 'lastid' => $recordId, 'type' => $type];
		if(!(new \App\Db\Query())->from('s_#__multireference')->where($data)->exists()){
			\App\Db::getInstance()->createCommand()->insert('s_#__multireference', $data)->execute();
		}
	}

	/**
	 * Update the value for relation.
	 *
	 * @param int    $sourceRecord Source record
	 */
	public function reloadValue($sourceRecord)
	{
		$field = $this->getFieldModel();
		$params = $field->getFieldParams();
		$sourceRecordModel = \Vtiger_Record_Model::getInstanceById($sourceRecord, $field->getModuleName());
		$targetModel = \Vtiger_RelationListView_Model::getInstance($sourceRecordModel, $params['module']);
		$fieldInfo = \App\Field::getFieldInfo($params['field']);
		$targetModel->getRelationQuery();
		$queryGenerator = $targetModel->getRelationModel()->getQueryGenerator();
		$queryGenerator->permissions = false;
		if ('-' !== $params['filterField']) {
			$queryGenerator->addCondition($params['filterField'], $params['filterValue'], 'e');
		}
		$values = $queryGenerator
			->setFields([$fieldInfo['fieldname']])
			->createQuery(true)
			->distinct()
			->column();
		\App\Db::getInstance()->createCommand()->update($field->get('table'), [
			$field->get('column') => $values ? self::COMMA . implode(self::COMMA, $values) . self::COMMA : '',
		], [$sourceRecordModel->getEntity()->tab_name_index[$field->get('table')] => $sourceRecord]
		)->execute();
	}

	/**
	 * Function to get all the available picklist values for the current field.
	 *
	 * @param mixed $module
	 * @param mixed $view
	 *
	 * @return array List of picklist values if the field is of type MultiReferenceValue
	 */
	public function getPicklistValuesForModuleList($module, $view): array
	{
		if ($picklistValues = $this->get('picklistValues')) {
			return $picklistValues;
		}
		$queryGenerator = new \App\QueryGenerator($module);
		$queryGenerator->initForCustomViewById($view);
		$queryGenerator->setFields([$this->getFieldModel()->get('name')]);
		$query = $queryGenerator->createQuery();
		$dataReader = $query->distinct()->createCommand()->query();
		$values = [];
		while (false !== ($value = $dataReader->readColumn(0))) {
			if (null === $value) {
				continue;
			}
			$value = explode(self::COMMA, trim($value, self::COMMA));
			$values = array_merge($values, $value);
		}
		$values = array_unique($values);
		$this->set('picklistValues', $values);
		return $values;
	}

	/** {@inheritdoc} */
	public function getAllowedColumnTypes()
	{
		return ['text'];
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['e', 'n', 'y', 'ny'];
	}

	/** {@inheritdoc} */
	public function delete()
	{
		$db = \App\Db::getInstance();
		$fieldModel = $this->getFieldModel();
		$moduleName = $fieldModel->getModuleName();
		$destModule = $fieldModel->getFieldParams()['module'] ?? '';

		$db->createCommand()->delete('s_#__multireference', ['source_module' => $moduleName, 'dest_module' => $destModule])->execute();

		\App\Cache::delete('mrvfbm', "{$moduleName},{$destModule}");
		\App\Cache::delete('getMultiReferenceModules', $destModule);

		$tabIds = (new \App\Db\Query())
			->select(['fieldid', 'tabid'])
			->from('vtiger_field')
			->where(['and',	['<>', 'presence', 1], ['uitype' => $fieldModel->getUIType()],	['and', ['like', 'fieldparams', '"field":"' . $fieldModel->getId() . '"']]
			])->createCommand()->queryAllByGroup();
		foreach ($tabIds as $fieldId => $tabId) {
			$sourceModule = \App\Module::getModuleName($tabId);
			$db->createCommand()->update('vtiger_field', ['presence' => 1], ['fieldid' => $fieldId])->execute();
			\App\Cache::delete('mrvfbm', "{$sourceModule},{$moduleName}");
			\App\Cache::delete('getMultiReferenceModules', $moduleName);
		}

		parent::delete();
	}
}
