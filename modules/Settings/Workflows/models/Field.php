<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_Workflows_Field_Model extends Vtiger_Field_Model
{

	/**
	 * Function to get all the supported advanced filter operations
	 * @return <Array>
	 */
	public static function getAdvancedFilterOptions()
	{
		return array(
			'is' => 'is',
			'is not' => 'is not',
			'contains' => 'contains',
			'does not contain' => 'does not contain',
			'starts with' => 'starts with',
			'ends with' => 'ends with',
			'has changed' => 'has changed',
			'has changed to' => 'has changed to',
			'is empty' => 'is empty',
			'is not empty' => 'is not empty',
			'less than' => 'less than',
			'greater than' => 'greater than',
			'does not equal' => 'does not equal',
			'less than or equal to' => 'less than or equal to',
			'greater than or equal to' => 'greater than or equal to',
			'before' => 'before',
			'after' => 'after',
			'between' => 'between',
			'is added' => 'is added',
			'is today' => 'is today',
			'less than hours before' => 'less than hours before',
			'less than hours later' => 'less than hours later',
			'more than hours before' => 'more than hours before',
			'more than hours later' => 'more than hours later',
			'less than days ago' => 'less than days ago',
			'more than days ago' => 'more than days ago',
			'in less than' => 'in less than',
			'in more than' => 'in more than',
			'days ago' => 'days ago',
			'days later' => 'days later',
			'equal to' => 'equal to',
			'None' => 'None',
		);
	}

	/**
	 * Function to get the advanced filter option names by Field type
	 * @return <Array>
	 */
	public static function getAdvancedFilterOpsByFieldType()
	{
		return array(
			'string' => array('is', 'contains', 'does not contain', 'starts with', 'ends with', 'has changed', 'is empty', 'is not empty'),
			'salutation' => array('is', 'contains', 'does not contain', 'starts with', 'ends with', 'has changed', 'is empty', 'is not empty'),
			'text' => array('is', 'contains', 'does not contain', 'starts with', 'ends with', 'has changed', 'is empty', 'is not empty'),
			'url' => array('is', 'contains', 'does not contain', 'starts with', 'ends with', 'has changed', 'is empty', 'is not empty'),
			'email' => array('is', 'contains', 'does not contain', 'starts with', 'ends with', 'has changed', 'is empty', 'is not empty'),
			'phone' => array('is', 'contains', 'does not contain', 'starts with', 'ends with', 'has changed', 'is empty', 'is not empty'),
			'integer' => array('equal to', 'less than', 'greater than', 'does not equal', 'less than or equal to', 'greater than or equal to', 'has changed'),
			'double' => array('equal to', 'less than', 'greater than', 'does not equal', 'less than or equal to', 'greater than or equal to', 'has changed'),
			'currency' => array('equal to', 'less than', 'greater than', 'does not equal', 'less than or equal to', 'greater than or equal to', 'has changed', 'is not empty'),
			'picklist' => array('is', 'is not', 'has changed', 'has changed to', 'starts with', 'ends with', 'contains', 'does not contain', 'is empty', 'is not empty'),
			'multipicklist' => array('is', 'is not', 'has changed', 'has changed to'),
			'datetime' => array('is', 'is not', 'has changed', 'less than hours before', 'less than hours later', 'more than hours before', 'more than hours later', 'is not empty'),
			'time' => array('is', 'is not', 'has changed', 'is not empty'),
			'date' => array('is', 'is not', 'has changed', 'between', 'before', 'after', 'is today', 'less than days ago', 'more than days ago', 'in less than', 'in more than',
				'days ago', 'days later', 'is not empty'),
			'boolean' => array('is', 'is not', 'has changed'),
			'reference' => array('has changed', 'is empty', 'is not empty'),
			'owner' => array('has changed', 'is', 'is not'),
			'recurrence' => array('is', 'is not', 'has changed'),
			'comment' => array('is added'),
			'image' => array('is', 'is not', 'contains', 'does not contain', 'starts with', 'ends with', 'is empty', 'is not empty'),
			'percentage' => array('equal to', 'less than', 'greater than', 'does not equal', 'less than or equal to', 'greater than or equal to', 'has changed', 'is not empty'),
			'multiReferenceValue' => array('contains', 'does not contain', 'has changed', 'is empty', 'is not empty'),
		);
	}

	/**
	 * Function to get comment field which will useful in creating conditions
	 * @param <Vtiger_Module_Model> $moduleModel
	 * @return <Vtiger_Field_Model>
	 */
	public static function getCommentFieldForFilterConditions($moduleModel)
	{
		$commentField = new Vtiger_Field_Model();
		$commentField->set('name', '_VT_add_comment');
		$commentField->set('label', 'Comment');
		$commentField->setModule($moduleModel);
		$commentField->fieldDataType = 'comment';

		return $commentField;
	}

	/**
	 * Function to get comment fields list which are useful in tasks
	 * @param <Vtiger_Module_Model> $moduleModel
	 * @return <Array> list of Field models <Vtiger_Field_Model>
	 */
	public static function getCommentFieldsListForTasks($moduleModel)
	{
		$commentsFieldsInfo = array('lastComment' => 'Last Comment', 'last5Comments' => 'Last 5 Comments', 'allComments' => 'All Comments');

		$commentFieldModelsList = array();
		foreach ($commentsFieldsInfo as $fieldName => $fieldLabel) {
			$commentField = new Vtiger_Field_Model();
			$commentField->setModule($moduleModel);
			$commentField->set('name', $fieldName);
			$commentField->set('label', $fieldLabel);
			$commentFieldModelsList[$fieldName] = $commentField;
		}
		return $commentFieldModelsList;
	}
}
