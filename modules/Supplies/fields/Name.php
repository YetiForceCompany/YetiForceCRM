<?php

/**
 * Supplies Name Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Supplies_Name_Field extends Supplies_Basic_Field
{

	protected $name = 'Name';
	protected $defaultLabel = 'LBL_ITEM_NAME';
	protected $columnName = 'name';
	protected $dbType = 'int(19) DEFAULT \'0\'';

	/**
	 * Geting value to display
	 * @param type $value
	 * @return type
	 */
	public function getDisplayValue($value)
	{
		if($value != 0)
			return Vtiger_Functions::getCRMRecordLabel($value);
		return '';
	}
}
