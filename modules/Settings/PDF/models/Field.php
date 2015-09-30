<?php
/**
 * Field Class for PDF Settings
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */
class Settings_PDF_Field_Model extends Vtiger_Field_Model
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
		);
	}
}
