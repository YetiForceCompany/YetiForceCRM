<?php

/**
 * Supplies Reference Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Supplies_Reference_Field extends Supplies_Basic_Field
{

	protected $name = 'Reference';
	protected $defaultLabel = 'LBL_REFERENCE';
	protected $columnName = 'ref';
	protected $dbType = 'int(19)';

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
