<?php

/**
 * Supplies Basic Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Supplies_Basic_Field extends Vtiger_Base_Model
{

	protected $name = '';
	protected $defaultLabel = 'LBL_CURRENCY';
	protected $defaultValue = '';
	protected $columnname = '';
	protected $dbType = 'varchar(100)';
	protected $customColumn = [];
	protected $summationValue = false;

	/**
	 * Geting database-type of field
	 * @return string dbType
	 */
	public function getDBType()
	{
		return $this->dbType;
	}

	/**
	 * Geting template name
	 * @return string templateName
	 */
	public function getTemplateName($view)
	{
		return $view . $this->name . '.tpl';
	}

	/**
	 * Geting default label
	 * @return string defaultLabel
	 */
	public function getDefaultLabel()
	{
		return $this->defaultLabel;
	}

	/**
	 * Geting field name
	 * @return string name
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Geting column name
	 * @return string columnname
	 */
	public function getColumnName()
	{
		return $this->columnname;
	}

	/**
	 * Geting column name
	 * @return string columnname
	 */
	public function getCustomColumn()
	{
		return $this->customColumn;
	}

	public function isSummary()
	{
		return $this->summationValue;
	}
	
	/**
	 * Data field instance initialization 
	 * @param array $valueArray Array for initialization
	 */
	public function initialize($valueArray)
	{
		$this->setData($valueArray);
	}

	/**
	 * Geting value to display
	 * @param type $value
	 * @return string
	 */
	public function getDisplayValue($value)
	{
		return $value;
	}

	/**
	 * Geting value to display
	 * @param type $value
	 * @return string
	 */
	public function getEditValue($value)
	{
		return $this->getDisplayValue($value);
	}

	/**
	 * Geting value
	 * @param type $value
	 * @return string
	 */
	public function getValue($value)
	{
		if ($value == '') {
			return $this->get('defaultvalue');
		}
		return $value;
	}

	public function isMandatory()
	{
		return true;
	}
}
