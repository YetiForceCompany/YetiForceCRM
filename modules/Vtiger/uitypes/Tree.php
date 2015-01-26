<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
class Vtiger_Tree_UIType extends Vtiger_Base_UIType {
	public function isAjaxEditable() {
		return false;
	}
	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/Tree.tpl';
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDisplayValue($value) {
		$template = $this->get('field')->getFieldParams();
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery('SELECT name FROM vtiger_trees_templates_data WHERE templateid = ? AND tree = ?', array($template,$value));
		if($adb->num_rows($result)) {
			return $adb->query_result($result, 0, 'name');
		}
		return false;
	}
	
	/**
	 * Function to get the display value in edit view
	 * @param reference record id
	 * @return link
	 */
	public function getEditViewDisplayValue($value) {
		return $this->getDisplayValue($value);
	}
	
    public function getListSearchTemplateName() {
        return 'uitypes/TreeFieldSearchView.tpl';
    }
	
	/**
	 * Function to get the all Values
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getAllValue() {
		$template = $this->get('field')->getFieldParams();
		$adb = PearDatabase::getInstance();
		$values = array();
		$result = $adb->pquery('SELECT tree,name FROM vtiger_trees_templates_data WHERE templateid = ?', array($template));
		for($i = 0; $i < $adb->num_rows($result); $i++){
			$values[$adb->query_result_raw($result, $i, 'tree')] = vtranslate($adb->query_result_raw($result, $i, 'name'), $this->get('field')->getModuleName());
		}
		return $values;
	}
}