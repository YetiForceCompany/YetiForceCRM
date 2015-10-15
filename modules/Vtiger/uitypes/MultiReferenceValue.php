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
		if (!empty($this->get('picklistValues'))) {
			return $this->get('picklistValues');
		}
		$params = $this->get('field')->getFieldParams();
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$fieldInfo = Vtiger_Functions::getModuleFieldInfoWithId($params['field']);
		$queryGenerator = new QueryGenerator($params['module'], $currentUser);
		$queryGenerator->setFields([$fieldInfo['columnname']]);
		if ($params['filterField'] != '-') {
			$queryGenerator->addCondition($params['filterField'], $params['filterValue'], 'e');
		}
		$query = $queryGenerator->getQuery();
		$result = $db->query($query);

		$values = [];
		while ($value = $db->getSingleValue($result)) {
			$values[] = $value;
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
	public function getMultiReferenceValueFields($sourceModule, $destinationModule)
	{
		$return = Vtiger_Cache::get('mrvf-' . $sourceModule, $destinationModule);
		if (!$return) {
			$db = PearDatabase::getInstance();
			$query = 'SELECT * FROM vtiger_field WHERE tabid = ? AND presence <> ? AND fieldparams LIKE \'{"module":"' . $destinationModule . '"%\';';
			$result = $db->pquery($query, [Vtiger_Functions::getModuleId($sourceModule), 1]);
			$return = [];
			while ($field = $db->fetch_array($result)) {
				$return[] = $field;
			}
			Vtiger_Cache::set('mrvf-' . $sourceModule, $destinationModule, $return);
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
		$query = 'SELECT ' . $this->get('field')->get('column') . ' FROM ' . $this->get('field')->get('table') . ' WHERE ' . $entity->tab_name_index[$this->get('field')->get('table')] . ' = ?';
		$result = $db->pquery($query, [$sourceRecord]);
		$currentValue = $db->getSingleValue($result);

		// Get value to added
		$destInstance = CRMEntity::getInstance($params['module']);
		$fieldInfo = Vtiger_Functions::getModuleFieldInfoWithId($params['field']);
		$query = 'SELECT ' . $fieldInfo['columnname'] . ' FROM ' . $fieldInfo['tablename'] . ' WHERE ' . $destInstance->tab_name_index[$fieldInfo['tablename']] . ' = ?';
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
		$query = 'UPDATE ' . $this->get('field')->get('table') . ' SET ' . $this->get('field')->get('column') . ' = ? WHERE ' . $entity->tab_name_index[$this->get('field')->get('table')] . ' = ?';
		$db->pquery($query, [$currentValue, $sourceRecord]);
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
		$query = 'UPDATE ' . $this->get('field')->get('table') . ' SET ' . $this->get('field')->get('column') . ' = ? WHERE ' . $entity->tab_name_index[$this->get('field')->get('table')] . ' = ?';
		$db->pquery($query, [$currentValue, $sourceRecord]);
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

		$db = PearDatabase::getInstance();
		$params = $this->get('field')->getFieldParams();
		$sourceRecordModel = Vtiger_Record_Model::getInstanceById($sourceRecord, $sourceModule);

		$targetModel = Vtiger_RelationListView_Model::getInstance($sourceRecordModel, $params['module']);
		$fieldInfo = Vtiger_Functions::getModuleFieldInfoWithId($params['field']);

		$query = $targetModel->getRelationQuery();
		$explodedQuery = explode('FROM', $query, 2);
		$relationQuery = 'SELECT ' . $fieldInfo['columnname'] . ' FROM' . $explodedQuery[1];

		vglobal('current_user', $currentUser);
		$result = $db->query($relationQuery);
		$currentValue = self::COMMA;
		while ($value = $db->getSingleValue($result)) {
			$currentValue .= $value . self::COMMA;
		}
		$query = 'UPDATE ' . $this->get('field')->get('table') . ' SET ' . $this->get('field')->get('column') . ' = ? WHERE ' . $sourceRecordModel->getEntity()->tab_name_index[$this->get('field')->get('table')] . ' = ?';
		$db->pquery($query, [$currentValue, $sourceRecord]);
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
		while ($value = $db->getSingleValue($result)) {
			$value = explode(self::COMMA, trim($value,self::COMMA));
			$values = array_merge($values, $value);
		}
		return array_unique($values);
	}
}
