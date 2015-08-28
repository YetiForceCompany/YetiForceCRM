<?php

/**
 * Inventory Value Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Value_InnventoryField extends Vtiger_Basic_InnventoryField
{

	protected $name = 'Value';
	protected $defaultLabel = 'LBL_VALUE';
	protected $columnName = 'value';
	protected $dbType = 'varchar(255)';
}
