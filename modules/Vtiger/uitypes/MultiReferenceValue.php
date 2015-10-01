<?php

/**
 * UIType MultiReferenceValue Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_MultiReferenceValue_UIType extends Vtiger_Base_UIType
{

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
	 * @return <Array> List of picklist values if the field is of type picklist or multipicklist, null otherwise.
	 */
	public function getPicklistValues()
	{
		$params = $this->get('field')->getFieldParams();
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$queryGenerator = new QueryGenerator($params['module'], $currentUser);
		$queryGenerator->setFields([$params['field']]);
		if($params['filterField'] != '-'){
			$queryGenerator->addCondition($params['filterField'], $params['filterValue'], 'e');
		}
		$query = $queryGenerator->getQuery();
		$result = $db->query($query);
		
		$values = [];
		while ($value = $db->getSingleValue($result)) {
			$values[$value] = vtranslate($value, $params['module']);
		}
		return $values;
	}
}
