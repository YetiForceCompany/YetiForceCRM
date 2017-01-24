<?php

/**
 * UIType MultiReferenceValue Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_MultiReferenceValue_UIType extends Vtiger_Base_UIType
{

	const COMMA = '|#|';

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return string - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/MultiReferenceValue.tpl';
	}

	public function getListSearchTemplateName()
	{
		return 'uitypes/MultiReferenceValueFieldSearchView.tpl';
	}

	/**
	 * Function to get all the available picklist values for the current field
	 * @return <Array> List of picklist values if the field is of type MultiReferenceValue.
	 */
	public function getPicklistValues()
	{
		$picklistValues = $this->get('picklistValues');
		if (!empty($picklistValues)) {
			return $picklistValues;
		}
		$params = $this->get('field')->getFieldParams();
		$fieldInfo = vtlib\Functions::getModuleFieldInfoWithId($params['field']);
		$queryGenerator = new \App\QueryGenerator($params['module']);
		if ($params['filterField'] != '-') {
			$queryGenerator->addCondition($params['filterField'], $params['filterValue'], 'e');
		}
		$queryGenerator->setFields([$fieldInfo['fieldname']]);
		$values = $queryGenerator->createQuery()->indexBy($fieldInfo['column'])->column();
		$this->set('picklistValues', $values);
		return $values;
	}

	/**
	 * Loading the list of multireference fields
	 * @param string $sourceModule Source module name
	 * @param string $destinationModule Destination module name
	 * @return array
	 */
	public static function getFieldsByModules($sourceModule, $destinationModule)
	{
		$cacheKey = "$sourceModule,$destinationModule";
		if (App\Cache::has('mrvfbm', $cacheKey)) {
			return App\Cache::get('mrvfbm', $cacheKey);
		}
		$fields = (new \App\Db\Query())
				->from('vtiger_field')
				->where(['tabid' => App\Module::getModuleId($sourceModule), 'uitype' => 305])
				->andWhere(['<>', 'presence', 1])
				->andWhere(['like', 'fieldparams', '{"module":"' . $destinationModule . '"%', false])->all();
		App\Cache::get('mrvfbm', $cacheKey, $fields, App\Cache::LONG);
		return $fields;
	}

	/**
	 * Getting the value for multireference
	 * @param CRMEntity $entity CRMEntity instance
	 * @param int $sourceRecord
	 * @param int $destRecord
	 * @return array
	 */
	public function getRecordValues(CRMEntity $entity, $sourceRecord, $destRecord)
	{
		$db = PearDatabase::getInstance();
		$params = $this->get('field')->getFieldParams();

		// Get current value
		$query = sprintf('SELECT %s FROM %s WHERE %s = ?', $this->get('field')->get('column'), $this->get('field')->get('table'), $entity->tab_name_index[$this->get('field')->get('table')]);
		$result = $db->pquery($query, [$sourceRecord]);
		$currentValue = $db->getSingleValue($result);

		// Get value to added
		$destInstance = CRMEntity::getInstance($params['module']);
		$fieldInfo = vtlib\Functions::getModuleFieldInfoWithId($params['field']);
		$query = sprintf('SELECT %s FROM %s WHERE %s = ?', $fieldInfo['columnname'], $fieldInfo['tablename'], $destInstance->tab_name_index[$fieldInfo['tablename']]);
		$result = $db->pquery($query, [$destRecord]);
		$relatedValue = $db->getSingleValue($result);
		return ['currentValue' => $currentValue, 'relatedValue' => $relatedValue];
	}

	/**
	 * Add value to multireference
	 * @param CRMEntity $entity CRMEntity instance
	 * @param int $sourceRecord 
	 * @param int $destRecord
	 */
	public function addValue(CRMEntity $entity, $sourceRecord, $destRecord)
	{
		$values = $this->getRecordValues($entity, $sourceRecord, $destRecord);
		$currentValue = $values['currentValue'];
		if (strpos($currentValue, self::COMMA . $values['relatedValue'] . self::COMMA) !== false) {
			return;
		}
		if (empty($currentValue)) {
			$currentValue = self::COMMA;
		}
		$currentValue .= $values['relatedValue'] . self::COMMA;
		App\Db::getInstance()->createCommand()->update($this->get('field')->get('table'), [
			$this->get('field')->get('column') => $currentValue
			], [$entity->tab_name_index[$this->get('field')->get('table')] => $sourceRecord]
		)->execute();
	}

	/**
	 * Remove value to multireference
	 * @param CRMEntity $entity CRMEntity instance
	 * @param int $sourceRecord 
	 * @param int $destRecord
	 */
	public function removeValue(CRMEntity $entity, $sourceRecord, $destRecord)
	{
		$db = PearDatabase::getInstance();
		$values = $this->getRecordValues($entity, $sourceRecord, $destRecord);
		$currentValue = $values['currentValue'];
		if (empty($currentValue)) {
			$currentValue = self::COMMA;
		}
		$currentValue = str_replace(self::COMMA . $values['relatedValue'] . self::COMMA, self::COMMA, $currentValue);
		App\Db::getInstance()->createCommand()->update($this->get('field')->get('table'), [
			$this->get('field')->get('column') => $currentValue
			], [$entity->tab_name_index[$this->get('field')->get('table')] => $sourceRecord]
		)->execute();
	}

	/**
	 * Update the value for relation
	 * @param string $sourceModule Source module name
	 * @param int $sourceRecord Source record
	 */
	public function reloadValue($sourceModule, $sourceRecord)
	{
		$orgUserId = App\User::getCurrentUserId();
		App\User::setCurrentUserId(Users::getActiveAdminId());
		$currentUser = vglobal('current_user');
		$user = new Users();
		vglobal('current_user', $user->retrieveCurrentUserInfoFromFile(Users::getActiveAdminId()));
		vglobal('currentModule', $sourceModule);
		$db = PearDatabase::getInstance();
		$params = $this->get('field')->getFieldParams();
		$sourceRecordModel = Vtiger_Record_Model::getInstanceById($sourceRecord, $sourceModule);

		$targetModel = Vtiger_RelationListView_Model::getInstance($sourceRecordModel, $params['module']);
		$fieldInfo = vtlib\Functions::getModuleFieldInfoWithId($params['field']);
		$query = $targetModel->getRelationQuery();
		$dataReader = $query->select([$fieldInfo['columnname']])
				->andWhere(['<>', $fieldInfo['columnname'], ''])
				->createCommand()->query();
		vglobal('current_user', $currentUser);
		App\User::setCurrentUserId($orgUserId);
		$currentValue = self::COMMA;
		while ($value = $dataReader->readColumn(0)) {
			$currentValue .= $value . self::COMMA;
		}
		$db->update($this->get('field')->get('table'), [
			$this->get('field')->get('column') => $currentValue
			], $sourceRecordModel->getEntity()->tab_name_index[$this->get('field')->get('table')] . ' = ?', [$sourceRecord]
		);
	}

	/**
	 * Function to get all the available picklist values for the current field
	 * @return <Array> List of picklist values if the field is of type MultiReferenceValue.
	 */
	public function getPicklistValuesForModuleList($module, $view)
	{
		$queryGenerator = new \App\QueryGenerator($module);
		$queryGenerator->initForCustomViewById($view);
		$queryGenerator->setFields([$this->get('field')->get('name')]);
		$query = $queryGenerator->createQuery();
		$dataReader = $query->distinct()->createCommand()->query();
		$values = [];
		while (($value = $dataReader->readColumn(0)) !== false) {
			$value = explode(self::COMMA, trim($value, self::COMMA));
			$values = array_merge($values, $value);
		}

		return array_unique($values);
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param string $value
	 * @param integer $record
	 * @param Vtiger_Record_Model $recordInstance
	 * @param string $rawText
	 * @return string
	 */
	public function getDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		$value = str_replace(self::COMMA, ', ', $value);
		$value = substr($value, 1);
		$value = substr($value, 0, -2);

		return $value;
	}
}
