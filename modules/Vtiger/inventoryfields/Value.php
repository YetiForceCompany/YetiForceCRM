<?php

/**
 * Inventory Value Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Value_InventoryField extends Vtiger_Basic_InventoryField
{

	protected $name = 'Value';
	protected $defaultLabel = 'LBL_VALUE';
	protected $columnName = 'value';
	protected $dbType = 'varchar(255)';
	protected $onlyOne = false;

}
