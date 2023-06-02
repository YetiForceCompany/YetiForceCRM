<?php

/**
 * Calendar extra source model file.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Calendar extra source model class.
 */
class Vtiger_CalendarExtSource_Model extends App\Base
{
	/** @var string[] Extra source details */
	const EXTRA_SOURCE_TYPES = [
		1 => 'LBL_SOURCE_TYPE_1',
		2 => 'LBL_SOURCE_TYPE_2',
		3 => 'LBL_SOURCE_TYPE_3',
		4 => 'LBL_SOURCE_TYPE_4',
	];
	/** @var string Extra source table name */
	const EXTRA_SOURCE_TABLE = 's_#__calendar_sources';

	/** @var string Base module name */
	protected $baseModuleName;
	/** @var string Target module name */
	protected $targetModuleName;
	/** @var \Vtiger_Module_Model Target module model */
	protected $targetModuleModel;
	/** @var \App\QueryGenerator Query generator instance */
	protected $queryGenerator;
	/** @var string[] Name record fields */
	protected $nameFields;

	/**
	 * Get calendar extra sources list.
	 *
	 * @param int $moduleId
	 *
	 * @return array
	 */
	public static function getByModule(int $moduleId): array
	{
		if (\App\Cache::has('Calendar-GetExtraSourcesList', $moduleId)) {
			return \App\Cache::get('Calendar-GetExtraSourcesList', $moduleId);
		}
		$query = (new \App\Db\Query())->from('s_#__calendar_sources')
			->where(['base_module' => $moduleId]);
		if (!\App\User::getCurrentUserModel()->isAdmin()) {
			$query->andWhere(['public' => 1]);
		}
		$rows = $query->createCommand(\App\Db::getInstance('admin'))->queryAllByGroup(1);
		\App\Cache::save('Calendar-GetExtraSourcesList', $moduleId, $rows, \App\Cache::LONG);
		return $rows;
	}

	/**
	 * Get calendar extra source instance by id.
	 *
	 * @param int $id
	 *
	 * @return $this
	 */
	public static function getInstanceById(int $id)
	{
		$source = self::getById($id);
		if (!$source) {
			throw new \App\Exceptions\AppException('No calendar extra source found');
		}
		$moduleName = \App\Module::getModuleName($source['base_module']);
		$className = Vtiger_Loader::getComponentClassName('Model', 'CalendarExtSource', $moduleName);
		if ($source['color']) {
			$source['textColor'] = \App\Colors::getContrast($source['color']);
		}
		$instance = new $className($source);
		$instance->baseModuleName = $moduleName;
		$instance->targetModuleName = \App\Module::getModuleName($source['target_module']);
		return $instance;
	}

	/**
	 * Get calendar extra source clean instance by module name.
	 *
	 * @param string $moduleName
	 *
	 * @return $this
	 */
	public static function getCleanInstance(string $moduleName)
	{
		$className = Vtiger_Loader::getComponentClassName('Model', 'CalendarExtSource', $moduleName);
		$handler = new $className();
		$handler->baseModuleName = $moduleName;
		return $handler;
	}

	/**
	 * Get calendar extra sources data by id.
	 *
	 * @param int $id
	 *
	 * @return string[]
	 */
	public static function getById(int $id): array
	{
		if (\App\Cache::has('Calendar-GetExtraSourceById', $id)) {
			return \App\Cache::get('Calendar-GetExtraSourceById', $id);
		}
		$row = (new \App\Db\Query())->from(self::EXTRA_SOURCE_TABLE)
			->where(['id' => $id])
			->one(\App\Db::getInstance('admin'));
		\App\Cache::save('Calendar-GetExtraSourceById', $id, $row, \App\Cache::LONG);
		return $row;
	}

	/**
	 * Save calendar extra sources.
	 *
	 * @return int
	 */
	public function save(): int
	{
		$dbCommand = \App\Db::getInstance('admin')->createCommand();
		if ($id = $this->get('id')) {
			$dbCommand->update(self::EXTRA_SOURCE_TABLE, $this->getData(), [
				'id' => $id
			])->execute();
			\App\Cache::save('Calendar-GetExtraSourceById', $id, $this->getData(), \App\Cache::LONG);
		} else {
			$params = $this->getData();
			$params['user_id'] = \App\User::getCurrentUserId();
			$dbCommand->insert(self::EXTRA_SOURCE_TABLE, $params)
				->execute();
			$id = \App\Db::getInstance('admin')->getLastInsertID(self::EXTRA_SOURCE_TABLE . '_id_seq');
		}
		\App\Cache::delete('Calendar-GetExtraSourcesList', $this->get('base_module'));
		return $id;
	}

	/**
	 * Delete calendar extra sources.
	 *
	 * @return bool
	 */
	public function delete(): bool
	{
		$dbCommand = \App\Db::getInstance('admin')->createCommand();
		$status = $dbCommand->delete(self::EXTRA_SOURCE_TABLE, ['id' => $this->get('id')])->execute();
		\App\Cache::delete('Calendar-GetExtraSourceById', $this->get('id'));
		\App\Cache::delete('Calendar-GetExtraSourcesList', $this->get('base_module'));
		return (bool) $status;
	}

	/**
	 * Get module model.
	 *
	 * @return Vtiger_Module_Model
	 */
	public function getModule(): Vtiger_Module_Model
	{
		if (!$this->targetModuleModel) {
			$this->targetModuleModel = \Vtiger_Module_Model::getInstance($this->targetModuleName);
		}
		return $this->targetModuleModel;
	}

	/**
	 * Get extra sources query.
	 *
	 * @return \App\Db\Query
	 */
	protected function getExtraSourcesQuery(): ?App\Db\Query
	{
		if (
			!\App\Privilege::isPermitted($this->targetModuleName)
			|| !\App\CustomView::getCustomViewById($this->get('custom_view'))
		) {
			return null;
		}
		$this->queryGenerator = new App\QueryGenerator($this->targetModuleName);
		$this->queryGenerator->initForCustomViewById($this->get('custom_view'));
		$this->targetModuleModel = $this->queryGenerator->getModuleModel();
		if ($this->get('include_filters')) {
			$this->loadExtraSourcesQueryFilter();
		}
		$this->queryGenerator->clearFields();
		$this->queryGenerator->setField('assigned_user_id');
		if ($this->get('field_label')) {
			$this->nameFields = [$this->getModule()->getField($this->get('field_label'))->getName()];
		} else {
			$this->nameFields = $this->getModule()->getNameFields();
		}
		foreach ($this->nameFields as $field) {
			$this->queryGenerator->setField($field);
		}
		$this->loadExtraSourcesQueryType();
		return $this->queryGenerator->createQuery();
	}

	/**
	 * Load extra sources query condition by type.
	 *
	 * @return void
	 */
	protected function loadExtraSourcesQueryType(): void
	{
		$fieldA = $this->getModule()->getField($this->get('fieldid_a_date'));
		$startDateInstance = DateTimeField::convertToDBTimeZone($this->get('start'));
		$startDateTime = $startDateInstance->format('Y-m-d H:i:s');
		$startDate = $startDateInstance->format('Y-m-d');
		$endDateInstance = DateTimeField::convertToDBTimeZone($this->get('end'));
		$endDateTime = $endDateInstance->format('Y-m-d H:i:s');
		$endDate = $endDateInstance->format('Y-m-d');
		if ('datetime' === $fieldA->getFieldDataType()) {
			$startFormatted = $startDateTime;
			$endFormatted = $endDateTime;
		} else {
			$startFormatted = $startDate;
			$endFormatted = $endDate;
		}
		$columnA = $fieldA->getTableName() . '.' . $fieldA->getColumnName();
		$this->queryGenerator->setField($fieldA->getName());
		switch ($this->get('type')) {
			case 1:
				$this->queryGenerator->addNativeCondition([
					'and', ['>=', $columnA, $startFormatted], ['<=', $columnA,  $endFormatted]
				]);
				break;
			case 3:
				$fieldB = $this->getModule()->getField($this->get('fieldid_b_date'));
				$columnB = $fieldB->getTableName() . '.' . $fieldB->getColumnName();
				$this->queryGenerator->setField($fieldB->getName());
				$this->queryGenerator->addNativeCondition([
					'or',
					['and', ['>=', $columnA, $startFormatted], ['<=', $columnA,  $endFormatted]],
					['and', ['>=', $columnB, $startFormatted], ['<=', $columnB,  $endFormatted]],
					['and', ['<', $columnA, $startFormatted], ['>', $columnB,  $endFormatted]],
				]);
				break;
			case 2:
				$fieldTimeA = $this->getModule()->getField($this->get('fieldid_a_time'));
				$this->queryGenerator->setField($fieldTimeA->getName());
				$columnATime = $fieldTimeA->getTableName() . '.' . $fieldTimeA->getColumnName();
				$this->queryGenerator->addNativeCondition([
					'or',
					[
						'and',
						['>=', new \yii\db\Expression("CONCAT($columnA, ' ', $columnATime)"),  $startDateTime],
						['<=', new \yii\db\Expression("CONCAT($columnA, ' ', $columnATime)"),  $endDateTime],
					],
				]);
				break;
			case 4:
				$fieldTimeA = $this->getModule()->getField($this->get('fieldid_a_time'));
				$fieldB = $this->getModule()->getField($this->get('fieldid_b_date'));
				$fieldTimeB = $this->getModule()->getField($this->get('fieldid_b_time'));
				$columnATime = $fieldTimeA->getTableName() . '.' . $fieldTimeA->getColumnName();
				$columnB = $fieldB->getTableName() . '.' . $fieldB->getColumnName();
				$columnBTime = $fieldTimeB->getTableName() . '.' . $fieldTimeB->getColumnName();
				$this->queryGenerator->setField($fieldTimeA->getName());
				$this->queryGenerator->setField($fieldB->getName());
				$this->queryGenerator->setField($fieldTimeB->getName());
				$this->queryGenerator->addNativeCondition([
					'or',
					[
						'and',
						['>=', new \yii\db\Expression("CONCAT($columnA, ' ', $columnATime)"),  $startDateTime],
						['<=', new \yii\db\Expression("CONCAT($columnA, ' ', $columnATime)"),  $endDateTime],
					],
					[
						'and',
						['>=', new \yii\db\Expression("CONCAT($columnB, ' ', $columnBTime)"),  $startDateTime],
						['<=', new \yii\db\Expression("CONCAT($columnB, ' ', $columnBTime)"),  $endDateTime],
					],
					[
						'and', ['<', $columnA, $startDate], ['>', $columnB,  $endDate],
					],
				]);
				break;
			default:
				break;
		}
	}

	/**
	 * Load extra sources query condition by type.
	 *
	 * @return void
	 */
	protected function loadExtraSourcesQueryFilter(): void
	{
		$conditions = [];
		if (!empty($this->get('user')) && isset($this->get('user')['selectedIds'][0])) {
			$selectedUsers = $this->get('user');
			$selectedIds = $selectedUsers['selectedIds'];
			if ('all' !== $selectedIds[0]) {
				$conditions[] = ['vtiger_crmentity.smownerid' => $selectedIds];
				$subQuery = (new \App\Db\Query())->select(['crmid'])
					->from('u_#__crmentity_showners')
					->where(['userid' => $selectedIds]);
				$conditions[] = ['vtiger_crmentity.crmid' => $subQuery];
			}
			if (isset($selectedUsers['excludedIds']) && 'all' === $selectedIds[0]) {
				$conditions[] = ['not in', 'vtiger_crmentity.smownerid', $selectedUsers['excludedIds']];
			}
		}
		if ($conditions) {
			$this->queryGenerator->addNativeCondition(array_merge(['or'], $conditions));
		}
	}

	/**
	 * Get calendar extra source counter.
	 *
	 * @return int
	 */
	public function getExtraSourcesCount(): int
	{
		$privileges = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if ($privileges->hasModuleActionPermission($this->baseModuleName, 'CalendarExtraSources')) {
			if ($query = $this->getExtraSourcesQuery()) {
				return $query->count();
			}
		}
		return 0;
	}

	/**
	 * Get calendar extra source rows.
	 *
	 * @return array
	 */
	public function getRows(): array
	{
		$query = $this->getExtraSourcesQuery();
		$privileges = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$query || !$privileges->hasModuleActionPermission($this->baseModuleName, 'CalendarExtraSources')) {
			return [];
		}
		$dataReader = $query->createCommand()->query();
		$result = [];
		while ($row = $dataReader->read()) {
			$result[] = $this->formatRow($row);
		}
		$dataReader->close();
		return $result;
	}

	/**
	 * Format record data.
	 *
	 * @param array $row
	 *
	 * @return array
	 */
	protected function formatRow(array $row): array
	{
		$item = [
			'id' => $row['id'],
			'editable' => false,
		];
		$this->formatDate($row, $item);
		$title = '';
		foreach ($this->nameFields as $field) {
			$title .= ' ' . \App\Purifier::encodeHtml($row[$field]);
		}
		$item['title'] = trim($title);
		$item['backgroundColor'] = $this->get('color');
		$item['textColor'] = $this->get('textColor');
		$item['className'] = 'js-show-modal js-quick-detail-modal js-popover-tooltip--record ownerCBr_' . $row['assigned_user_id'];
		$item['url'] = 'index.php?module=' . $this->targetModuleName . '&view=Detail&record=' . $row['id'];
		return $item;
	}

	/**
	 * Format dates.
	 *
	 * @param array $row
	 * @param array $item
	 *
	 * @return void
	 */
	protected function formatDate(array $row, array &$item): void
	{
		$fieldA = $this->getModule()->getField($this->get('fieldid_a_date'));
		$valueA = $row[$fieldA->getColumnName()];
		switch ($this->get('type')) {
			case 3:
				$fieldB = $this->getModule()->getField($this->get('fieldid_b_date'));
				$valueB = $row[$fieldB->getColumnName()];
				if ($valueB) {
					if ('datetime' === $fieldB->getFieldDataType()) {
						$date = new DateTimeField($valueB);
						$item['end'] = $date->getFullcalenderValue();
						$item['end_display'] = $date->getDisplayDateTimeValue();
					} else {
						$item['end'] = $valueB;
						$item['end_display'] = \App\Fields\Date::formatToDisplay($valueB);
					}
				}
				// no break
			case 1:
				if ($valueA) {
					if ('datetime' === $fieldA->getFieldDataType()) {
						$date = new DateTimeField($valueA);
						$item['start'] = $date->getFullcalenderValue();
						$item['start_display'] = $date->getDisplayDateTimeValue();
					} else {
						$item['start'] = $valueA;
						$item['start_display'] = \App\Fields\Date::formatToDisplay($valueA);
					}
				} else {
					$item['start'] = $item['end'];
					$item['start_display'] = $item['end_display'];
				}
				break;
			case 4:
				$valueB = $row[$this->getModule()->getField($this->get('fieldid_b_date'))->getColumnName()];
				$valueTimeB = $row[$this->getModule()->getField($this->get('fieldid_b_time'))->getColumnName()];
				if ($valueTimeB) {
					$date = new DateTimeField($valueB . ' ' . $valueTimeB);
					$item['end'] = $date->getFullcalenderValue();
					$item['end_display'] = $date->getDisplayDateTimeValue();
				}
				// no break
			case 2:
				$valueTimeA = $row[$this->getModule()->getField($this->get('fieldid_a_time'))->getColumnName()];
				if ($valueA) {
					$date = new DateTimeField($valueA . ' ' . $valueTimeA);
					$item['start'] = $date->getFullcalenderValue();
					$item['start_display'] = $date->getDisplayDateTimeValue();
				} else {
					$item['start'] = $item['end'];
					$item['start_display'] = $item['end_display'];
				}
				break;
			default:
				break;
		}
	}
}
