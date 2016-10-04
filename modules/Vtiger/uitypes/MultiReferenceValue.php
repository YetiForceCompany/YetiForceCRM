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
	 * @return <String> - Template Name
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
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$fieldInfo = vtlib\Functions::getModuleFieldInfoWithId($params['field']);
		$queryGenerator = new QueryGenerator($params['module'], $currentUser);
		$queryGenerator->setFields([$fieldInfo['columnname']]);
		if ($params['filterField'] != '-') {
			$queryGenerator->addCondition($params['filterField'], $params['filterValue'], 'e');
		}
		$query = $queryGenerator->getQuery();
		$result = $db->query($query);

		$values = [];
		while ($value = $db->getSingleValue($result)) {
			$values[$value] = $value;
		}
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
		$return = Vtiger_Cache::get('mrvfm-' . $sourceModule, $destinationModule);
		if (!$return) {
			$db = PearDatabase::getInstance();
			$query = sprintf('SELECT * FROM vtiger_field WHERE tabid = ? && presence <> ? && vtiger_field.uitype = ? && fieldparams LIKE \'%s\';', '{"module":"' . $destinationModule . '"%');
			$result = $db->pquery($query, [vtlib\Functions::getModuleId($sourceModule), 1, 305]);
			$return = [];
			while ($field = $db->fetch_array($result)) {
				$return[] = $field;
			}
			Vtiger_Cache::set('mrvfm-' . $sourceModule, $destinationModule, $return);
		}
		return $return;
	}

	/**
	 * Loading the list of multireference fields
	 * @param string $sourceModule Source module name
	 * @param string $destinationModule Destination module name
	 * @return array
	 */
	public static function getRelatedModules($moduleName)
	{
		$return = Vtiger_Cache::get('mrvf', $moduleName);
		if (!$return) {
			$db = PearDatabase::getInstance();
			$moduleId = vtlib\Functions::getModuleId($moduleName);
			$query = 'SELECT DISTINCT vtiger_tab.name FROM vtiger_field INNER JOIN vtiger_relatedlists ON vtiger_relatedlists.related_tabid = vtiger_field.tabid'
				. ' LEFT JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_field.tabid'
				. ' WHERE vtiger_relatedlists.tabid = ? && vtiger_field.presence <> ? && vtiger_field.uitype = ? && fieldparams LIKE \'{"module":"' . $moduleName . '"%\';';
			$result = $db->pquery($query, [$moduleId, 1, 305]);
			$return = [];
			while ($module = $db->getSingleValue($result)) {
				$return[] = $module;
			}
			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			$fieldsModel = $moduleModel->getFields();
			$relatedModules = [];
			foreach ($fieldsModel as $fieldName => $fieldModel) {
				if ($fieldModel->isReferenceField()) {
					$referenceList = $fieldModel->getReferenceList();
					$relatedModules = array_merge($relatedModules, $referenceList);
				}
			}
			$relatedModules = array_unique($relatedModules);
			foreach ($relatedModules as $key => $relatedModule) {
				if ($relatedModule != 'Users') {
					$relatedModules[$key] = vtlib\Functions::getModuleId($relatedModule);
				} else {
					unset($relatedModules[$key]);
				}
			}
			if (count($relatedModules) > 0) {
				$query = 'SELECT DISTINCT vtiger_tab.name FROM vtiger_field LEFT JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_field.tabid'
					. ' WHERE vtiger_field.uitype = ? && vtiger_field.tabid IN (\'' . implode("','", $relatedModules) . '\') && vtiger_field.presence <> ? '
					. 'AND fieldparams LIKE \'{"module":"' . $moduleName . '"%\' ;';
				$result = $db->pquery($query, [1, 305]);
				while ($module = $db->getSingleValue($result)) {
					$return[] = $module;
				}
			}
			$return = array_unique($return);
			Vtiger_Cache::set('mrvf-', $moduleName, $return);
		}
		return $return;
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
		$db = PearDatabase::getInstance();
		$values = $this->getRecordValues($entity, $sourceRecord, $destRecord);
		$currentValue = $values['currentValue'];
		if (strpos($currentValue, self::COMMA . $values['relatedValue'] . self::COMMA) !== false) {
			return;
		}
		if (empty($currentValue)) {
			$currentValue = self::COMMA;
		}
		$currentValue .= $values['relatedValue'] . self::COMMA;
		$db->update($this->get('field')->get('table'), [
			$this->get('field')->get('column') => $currentValue
			], $entity->tab_name_index[$this->get('field')->get('table')] . ' = ?', [$sourceRecord]
		);
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
		$db->update($this->get('field')->get('table'), [
			$this->get('field')->get('column') => $currentValue
			], $entity->tab_name_index[$this->get('field')->get('table')] . ' = ?', [$sourceRecord]
		);
	}

	/**
	 * Update the value for relation
	 * @param string $sourceModule Source module name
	 * @param int $sourceRecord Source record
	 */
	public function reloadValue($sourceModule, $sourceRecord)
	{
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
		$explodedQuery = explode('FROM', $query, 2);
		$relationQuery = sprintf("SELECT DISTINCT %s FROM %s && %s <> ''", $fieldInfo['columnname'], $explodedQuery[1], $fieldInfo['columnname']);

		vglobal('current_user', $currentUser);
		$result = $db->query($relationQuery);
		$currentValue = self::COMMA;
		while ($value = $db->getSingleValue($result)) {
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
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		$queryGenerator = new QueryGenerator($module, $currentUser);
		$queryGenerator->initForCustomViewById($view);
		$queryGenerator->setFields([$this->get('field')->get('name')]);
		$listQuery = $queryGenerator->getQuery('SELECT DISTINCT');
		$result = $db->query($listQuery);

		$values = [];
		while (($value = $db->getSingleValue($result)) !== false) {
			$value = explode(self::COMMA, trim($value, self::COMMA));
			$values = array_merge($values, $value);
		}

		return array_unique($values);
	}
}
