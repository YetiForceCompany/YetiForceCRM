<?php

/**
 * Supplies Value Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Supplies_Value_Field extends Supplies_Basic_Field
{

	protected $name = 'Value';
	protected $defaultLabel = 'LBL_VALUE';
	protected $columnName = 'value';
	protected $dbType = 'varchar(255)';
}
